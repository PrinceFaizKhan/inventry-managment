<?php

namespace Modules\ProductTypes\Grid;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Support\Grid\GridDataProvider;
use Modules\ProductTypes\Models\ProductType;

class ProductTypesGridProvider implements GridDataProvider
{
    /** @param array<string, mixed> $parameters @param array<string, mixed> $definition */
    public function query(array $parameters, array $definition): Builder
    {
        $query = ProductType::query()
            ->select(['id', 'name', 'code', 'created_at'])
            ->withCount([
                'groups',
                'allGroupAttributes as attributes_count',
                'variants as variants_count',
            ]);

        if ($parameters['search'] !== '') {
            $query->where(function (Builder $query) use ($parameters): void {
                $query->where('name', 'like', '%'.$parameters['search'].'%')
                    ->orWhere('code', 'like', '%'.$parameters['search'].'%');
            });
        }

        if (isset($parameters['filters']['has_variants'])) {
            $val = filter_var($parameters['filters']['has_variants'], FILTER_VALIDATE_BOOLEAN);
            $val ? $query->has('variants') : $query->doesntHave('variants');
        }

        if (isset($parameters['filters']['has_groups'])) {
            $val = filter_var($parameters['filters']['has_groups'], FILTER_VALIDATE_BOOLEAN);
            $val ? $query->has('groups') : $query->doesntHave('groups');
        }

        return $query
            ->orderBy($definition['sorts'][$parameters['sort']], $parameters['direction'])
            ->orderBy('id');
    }

    public function row(Model $model): array
    {
        /** @var ProductType $model */
        return [
            'id' => $model->id,
            'name' => $model->name,
            'code' => $model->code,
            'groups_count' => (int) ($model->groups_count ?? 0),
            'attributes_count' => (int) ($model->attributes_count ?? 0),
            'variants_count' => (int) ($model->variants_count ?? 0),
            'created_at' => $model->created_at?->format('M d, Y H:i'),
        ];
    }

    public function filterOptions(): array
    {
        return [
            'has_variants' => [
                ['value' => '1', 'label' => 'Yes (Configurable with Variants)'],
                ['value' => '0', 'label' => 'No (Simple Only)'],
            ],
            'has_groups' => [
                ['value' => '1', 'label' => 'Yes (Has Groups)'],
                ['value' => '0', 'label' => 'No (Empty)'],
            ],
        ];
    }

    public function acceptsFilterValue(string $filter, mixed $value): bool
    {
        return match ($filter) {
            'has_variants', 'has_groups' => in_array((string) $value, ['0', '1'], true),
            default => false,
        };
    }

    public function isSelectable(User $actor, Model $record): bool
    {
        return true;
    }
}
