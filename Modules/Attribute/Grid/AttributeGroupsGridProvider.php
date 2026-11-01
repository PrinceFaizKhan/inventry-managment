<?php

namespace Modules\Attribute\Grid;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Attribute\Models\AttributeGroup;
use Modules\Core\Support\Grid\GridDataProvider;

class AttributeGroupsGridProvider implements GridDataProvider
{
    /** @param array<string, mixed> $parameters @param array<string, mixed> $definition */
    public function query(array $parameters, array $definition): Builder
    {
        $query = AttributeGroup::query()->select(['id', 'name', 'code', 'created_at']);

        if ($parameters['search'] !== '') {
            $query->where(function (Builder $query) use ($parameters): void {
                $query->where('name', 'like', '%'.$parameters['search'].'%')
                    ->orWhere('code', 'like', '%'.$parameters['search'].'%');
            });
        }

        if (isset($parameters['filters']['has_families'])) {
            $val = filter_var($parameters['filters']['has_families'], FILTER_VALIDATE_BOOLEAN);
            $val ? $query->has('productTypeGroups') : $query->doesntHave('productTypeGroups');
        }

        return $query
            ->orderBy($definition['sorts'][$parameters['sort']], $parameters['direction'])
            ->orderBy('id');
    }

    public function row(Model $model): array
    {
        /** @var AttributeGroup $model */
        return [
            'id' => $model->id,
            'name' => $model->name,
            'code' => $model->code,
            'created_at' => $model->created_at?->format('M d, Y'),
        ];
    }

    public function filterOptions(): array
    {
        return [
            'has_families' => [
                ['value' => '1', 'label' => 'Yes (Assigned to Families)'],
                ['value' => '0', 'label' => 'No (Unassigned)'],
            ],
        ];
    }

    public function acceptsFilterValue(string $filter, mixed $value): bool
    {
        return match ($filter) {
            'has_families' => in_array((string) $value, ['0', '1'], true),
            default => false,
        };
    }

    public function isSelectable(User $actor, Model $record): bool
    {
        return true;
    }
}
