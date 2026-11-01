<?php

namespace Modules\Core\DataGrid;

use Closure;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class DataGrid implements Responsable
{
    /** @var EloquentBuilder|QueryBuilder|Closure|null */
    protected mixed $query = null;

    protected string $primaryColumn = 'id';

    /** @var array<int, string> */
    protected array $searchableColumns = [];

    /** @var array<int, Column> */
    protected array $columns = [];

    /** @var array<int, Filter> */
    protected array $filters = [];

    /** @var array<int, Action> */
    protected array $actions = [];

    /** @var array<int, BulkAction> */
    protected array $bulkActions = [];

    protected string $defaultSort = 'id';

    protected string $defaultDirection = 'desc';

    /** @var array<int, int> */
    protected array $pageSizes = [10, 25, 50, 100];

    protected int $defaultPerPage = 10;

    protected bool $selectable = true;

    protected string $searchPlaceholder = 'Search records...';

    protected bool $isBuilt = false;

    public function __construct() {}

    public static function make(mixed ...$args): static
    {
        return new static(...$args);
    }

    /**
     * Define columns, query, filters, actions, etc.
     */
    abstract public function build(): void;

    /** @param EloquentBuilder|QueryBuilder|Closure $query */
    public function source(mixed $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function primaryKey(string $key): static
    {
        $this->primaryColumn = $key;

        return $this;
    }

    /** @param array<int, string> $columns */
    public function searchable(array $columns): static
    {
        $this->searchableColumns = $columns;

        return $this;
    }

    /** @param array<int, Column> $columns */
    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    public function addColumn(Column $column): static
    {
        $this->columns[] = $column;

        return $this;
    }

    /** @param array<int, Filter> $filters */
    public function filters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function addFilter(Filter $filter): static
    {
        $this->filters[] = $filter;

        return $this;
    }

    /** @param array<int, Action> $actions */
    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function addAction(Action $action): static
    {
        $this->actions[] = $action;

        return $this;
    }

    /** @param array<int, BulkAction> $bulkActions */
    public function bulkActions(array $bulkActions): static
    {
        $this->bulkActions = $bulkActions;

        return $this;
    }

    public function addBulkAction(BulkAction $bulkAction): static
    {
        $this->bulkActions[] = $bulkAction;

        return $this;
    }

    public function defaultSort(string $column, string $direction = 'asc'): static
    {
        $this->defaultSort = $column;
        $this->defaultDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        return $this;
    }

    /** @param array<int, int> $sizes */
    public function pageSizes(array $sizes): static
    {
        $this->pageSizes = $sizes;

        return $this;
    }

    public function defaultPerPage(int $perPage): static
    {
        $this->defaultPerPage = $perPage;

        return $this;
    }

    public function selectable(bool $selectable = true): static
    {
        $this->selectable = $selectable;

        return $this;
    }

    public function searchPlaceholder(string $placeholder): static
    {
        $this->searchPlaceholder = $placeholder;

        return $this;
    }

    /**
     * Render either Blade view or JSON based on request type.
     *
     * @param  array<string, mixed>  $data
     */
    public function render(?string $view = null, array $data = []): View|JsonResponse
    {
        $request = request();

        if ($request->ajax() || $request->wantsJson() || $request->has('_grid') || is_null($view)) {
            return $this->toJson();
        }

        return view($view, array_merge(['datagrid' => $this], $data));
    }

    public function toResponse($request): JsonResponse|View
    {
        return $this->render();
    }

    /**
     * Process query, filters, sorting, pagination, and output JSON response.
     */
    public function toJson(): JsonResponse
    {
        $this->ensureBuilt();

        $request = request();
        $user = $request->user();

        // 1. Resolve query builder
        $query = is_callable($this->query) ? ($this->query)() : $this->query;
        if (! $query) {
            throw new \RuntimeException('DataGrid source query has not been specified.');
        }

        // 2. Global search
        $search = trim($request->string('search')->toString());
        if ($search !== '') {
            $searchCols = ! empty($this->searchableColumns)
                ? $this->searchableColumns
                : collect($this->columns)->filter(fn (Column $c) => $c->isSearchable())->map(fn (Column $c) => $c->getIndex())->all();

            if (! empty($searchCols)) {
                $query->where(function ($sub) use ($searchCols, $search): void {
                    foreach ($searchCols as $idx => $col) {
                        if ($idx === 0) {
                            $sub->where($col, 'like', '%'.$search.'%');
                        } else {
                            $sub->orWhere($col, 'like', '%'.$search.'%');
                        }
                    }
                });
            }
        }

        // 3. Filters
        $requestedFilters = $request->input('filter', []);
        if (is_array($requestedFilters)) {
            $filtersMap = collect($this->filters)->keyBy(fn (Filter $f) => $f->getIndex());

            foreach ($requestedFilters as $key => $value) {
                if ($value === '' || is_null($value)) {
                    continue;
                }

                if ($filtersMap->has($key)) {
                    /** @var Filter $filter */
                    $filter = $filtersMap->get($key);
                    if ($filter->getQueryCallback()) {
                        ($filter->getQueryCallback())($query, $value);

                        continue;
                    }
                }

                // Default filter behavior: boolean or direct match
                if ($value === '1' || $value === '0') {
                    $query->where($key, filter_var($value, FILTER_VALIDATE_BOOLEAN));
                } else {
                    $query->where($key, $value);
                }
            }
        }

        // 4. Sorting
        $sortColumn = $request->input('sort', $this->defaultSort);
        $sortDirection = strtolower($request->input('direction', $this->defaultDirection)) === 'desc' ? 'desc' : 'asc';

        $allowedSorts = collect($this->columns)
            ->filter(fn (Column $c) => $c->isSortable())
            ->map(fn (Column $c) => $c->getIndex())
            ->push($this->primaryColumn)
            ->unique()
            ->all();

        if (in_array($sortColumn, $allowedSorts, true)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy($this->defaultSort, $this->defaultDirection);
        }

        // 5. Pagination
        $perPage = $request->integer('per_page', $this->defaultPerPage);
        if (! in_array($perPage, $this->pageSizes, true)) {
            $perPage = $this->defaultPerPage;
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage)->withQueryString();

        // 6. Format Rows
        $rows = $paginator->getCollection()->map(function ($record) use ($user): array {
            $row = is_array($record) ? $record : (array) $record;
            if (is_object($record) && method_exists($record, 'toArray')) {
                $row = $record->toArray();
            }

            // Apply column formatting closures if any
            foreach ($this->columns as $col) {
                if ($closure = $col->getClosure()) {
                    $row[$col->getIndex()] = $closure($record);
                } elseif (in_array($col->getIndex(), ['created_at', 'updated_at'], true)) {
                    $rawDate = $record->{$col->getIndex()} ?? ($row[$col->getIndex()] ?? null);
                    if ($rawDate instanceof \DateTimeInterface) {
                        $row[$col->getIndex()] = $rawDate->format('M d, Y');
                    } elseif (is_string($rawDate) && ! empty($rawDate) && ($ts = strtotime($rawDate)) !== false) {
                        $row[$col->getIndex()] = date('M d, Y', $ts);
                    }
                } elseif (isset($row[$col->getIndex()]) && is_bool($row[$col->getIndex()])) {
                    $row[$col->getIndex()] = (bool) $row[$col->getIndex()];
                }
            }

            // Row actions
            $rowActions = [];
            foreach ($this->actions as $action) {
                if ($action->isAllowed($user, $record)) {
                    $rowActions[$action->getKey()] = [
                        'key' => $action->getKey(),
                        'label' => $action->getLabel(),
                        'icon' => $action->getIcon(),
                        'method' => $action->getMethod(),
                        'url' => $action->getUrl($record),
                    ];
                }
            }
            $row['_actions'] = $rowActions;

            // Auto urls if actions exist
            if (isset($rowActions['edit']['url'])) {
                $row['edit_url'] = $rowActions['edit']['url'];
            }
            if (isset($rowActions['delete']['url'])) {
                $row['delete_url'] = $rowActions['delete']['url'];
            }
            if (isset($rowActions['toggle_status']['url'])) {
                $row['toggle_status_url'] = $rowActions['toggle_status']['url'];
            }

            $row['selectable'] = $this->selectable;

            return $row;
        })->values();

        // 7. Format Filter Options for Frontend
        $filterOptions = [];
        foreach ($this->filters as $filter) {
            $filterOptions[$filter->getIndex()] = $filter->getOptions();
        }

        // 8. Actions allowed map
        $allowedActions = [];
        foreach ($this->actions as $action) {
            $allowedActions[$action->getKey()] = $action->isAllowed($user);
        }

        // 9. Bulk actions allowed map & definition
        $allowedBulkActions = [];
        $bulkActionDefinitions = [];
        foreach ($this->bulkActions as $bulk) {
            $allowed = $bulk->isAllowed($user);
            $allowedBulkActions[$bulk->getKey()] = $allowed;
            if ($allowed) {
                $bulkActionDefinitions[] = [
                    'key' => $bulk->getKey(),
                    'label' => $bulk->getLabel(),
                    'method' => $bulk->getMethod(),
                    'url' => $bulk->getUrl(),
                ];
            }
        }

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'primary_column' => $this->primaryColumn,
                'columns' => collect($this->columns)->map(fn (Column $c) => $c->toArray())->values()->all(),
                'filter_options' => $filterOptions,
                'page_sizes' => $this->pageSizes,
                'default_sort' => $this->defaultSort,
                'default_direction' => $this->defaultDirection,
                'actions' => $allowedActions,
                'selectable' => $this->selectable,
                'bulk_actions' => $allowedBulkActions,
                'bulk_action_definitions' => $bulkActionDefinitions,
                'search_placeholder' => $this->searchPlaceholder,
            ],
        ]);
    }

    protected function ensureBuilt(): void
    {
        if (! $this->isBuilt) {
            $this->build();
            $this->isBuilt = true;
        }
    }
}
