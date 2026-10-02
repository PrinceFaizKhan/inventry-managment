<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexGridRequest;
use App\Support\Grid\GridDataProvider;
use App\Support\Grid\GridRegistry;
use App\Support\Menu\MenuAuthorizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class GridController extends Controller
{
    public function index(IndexGridRequest $request, string $grid, GridRegistry $grids, MenuAuthorizer $authorizer): JsonResponse
    {
        $definition = $grids->find($grid);
        abort_unless($authorizer->allows($request->user(), $definition['view_permission']), 403);

        /** @var GridDataProvider $provider */
        $provider = app($definition['provider']);
        $parameters = [
            'search' => $request->string('search')->toString(),
            'filters' => $request->array('filter'),
            'sort' => $request->input('sort', $definition['default_sort']),
            'direction' => $request->input('direction', $definition['default_direction']),
            'per_page' => $request->integer('per_page', $definition['page_sizes'][0]),
        ];
        $paginator = $provider->query($parameters, $definition)->paginate($parameters['per_page'])->withQueryString();

        return response()->json([
            'data' => $paginator->getCollection()->map(function ($row) use ($provider, $request, $definition): array {
                return [
                    ...$provider->row($row),
                    'selectable' => $definition['selectable'] && $provider->isSelectable($request->user(), $row),
                ];
            })->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'filter_options' => $provider->filterOptions(),
                'actions' => collect($definition['actions'])->map(fn (string $permission): bool => $authorizer->allows($request->user(), $permission)),
                'selectable' => $definition['selectable'],
                'bulk_actions' => collect($definition['bulk_actions'])->map(fn (string $permission): bool => $authorizer->allows($request->user(), $permission)),
                'bulk_edit_fields' => collect($definition['bulk_edit_fields'])
                    ->filter(fn (array $field): bool => $authorizer->allows($request->user(), $field['permission']))
                    ->map(function (array $field, string $key) use ($provider): array {
                        if (($field['options_source'] ?? null) === 'roles') {
                            $field['options'] = collect($provider->filterOptions()['role_id'] ?? [])->mapWithKeys(fn (array $option): array => [$option['value'] => $option['label']])->all();
                        }

                        return [$key => Arr::only($field, ['label', 'type', 'nullable', 'options'])];
                    })->collapse(),
            ],
        ]);
    }
}
