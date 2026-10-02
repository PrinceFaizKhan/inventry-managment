<?php

namespace Modules\Catalog\Grid;

use App\Models\User;
use App\Support\Grid\GridDataProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Modules\Catalog\Attributes\AttributeTypeRegistry;
use Modules\Catalog\Models\Attribute;

class AttributesGridProvider implements GridDataProvider
{
    public function __construct(private readonly AttributeTypeRegistry $types) {}

    /** @param array<string, mixed> $parameters @param array<string, mixed> $definition */
    public function query(array $parameters, array $definition): Builder
    {
        $query = Attribute::query()->select(['id', 'name', 'code', 'type', 'required', 'created_at']);
        if ($parameters['search'] !== '') {
            $query->where(function (Builder $query) use ($parameters): void {
                $query->where('name', 'like', '%'.$parameters['search'].'%')
                    ->orWhere('code', 'like', '%'.$parameters['search'].'%');
            });
        }
        if (isset($parameters['filters']['type'])) {
            $query->where('type', $parameters['filters']['type']);
        }
        if (isset($parameters['filters']['required'])) {
            $query->where('required', filter_var($parameters['filters']['required'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query
            ->orderBy($definition['sorts'][$parameters['sort']], $parameters['direction'])
            ->orderBy('id');
    }

    public function row(Model $model): array
    {
        /** @var Attribute $model */
        return [
            'id' => $model->id,
            'name' => $model->name,
            'code' => $model->code,
            'type' => $this->types->definitions()[$model->type]['label'],
            'type_key' => $model->type,
            'required' => $model->required,
        ];
    }

    public function filterOptions(): array
    {
        $types = [];
        foreach ($this->types->definitions() as $key => $definition) {
            $types[] = ['value' => $key, 'label' => $definition['label']];
        }

        return [
            'type' => $types,
            'required' => [
                ['value' => '1', 'label' => 'Yes'],
                ['value' => '0', 'label' => 'No'],
            ],
        ];
    }

    public function acceptsFilterValue(string $filter, mixed $value): bool
    {
        return match ($filter) {
            'type' => is_string($value) && array_key_exists($value, $this->types->definitions()),
            'required' => in_array((string) $value, ['0', '1'], true),
            default => false,
        };
    }

    public function isSelectable(User $actor, Model $record): bool
    {
        return true;
    }
}
