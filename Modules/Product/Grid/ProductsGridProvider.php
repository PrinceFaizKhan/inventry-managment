<?php

namespace Modules\Product\Grid;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Modules\Attribute\Models\Attribute;
use Modules\Core\Support\Grid\GridDataProvider;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttributeValue;
use Modules\ProductTypes\Models\ProductType;

class ProductsGridProvider implements GridDataProvider
{
    /** @param array<string, mixed> $parameters @param array<string, mixed> $definition */
    public function query(array $parameters, array $definition): Builder
    {
        $query = Product::query()
            ->select(['products.id', 'products.sku', 'products.type', 'products.product_type_id', 'products.status', 'products.created_at'])
            ->with(['productType:id,name,code'])
            ->parentsOnly();

        if ($parameters['search'] !== '') {
            $query->where(function (Builder $query) use ($parameters): void {
                $query->where('products.sku', 'like', '%'.$parameters['search'].'%')
                    ->orWhereHas('productType', function (Builder $ptQuery) use ($parameters): void {
                        $ptQuery->where('name', 'like', '%'.$parameters['search'].'%')
                            ->orWhere('code', 'like', '%'.$parameters['search'].'%');
                    });
            });
        }

        // Apply filters
        $filters = $parameters['filters'] ?? [];

        // 1. Family filter
        if (! empty($filters['family'])) {
            $query->where('products.product_type_id', $filters['family']);
        }

        // 2. Dynamic filterable attributes filters
        if (Schema::hasTable('attributes')) {
            $filterableAttributes = Attribute::query()
                ->where('is_filterable', true)
                ->get()
                ->keyBy('code');

            foreach ($filters as $code => $value) {
                if ($value === '' || $value === null || $code === 'family') {
                    continue;
                }

                $attribute = $filterableAttributes->get($code);
                if (! $attribute) {
                    continue;
                }

                $query->where(function (Builder $productQuery) use ($attribute, $value): void {
                    $productQuery->whereHas('attributeValues', function (Builder $sub) use ($attribute, $value): void {
                        $this->applyAttributeCondition($sub, $attribute, $value);
                    })->orWhereHas('variants.attributeValues', function (Builder $sub) use ($attribute, $value): void {
                        $this->applyAttributeCondition($sub, $attribute, $value);
                    });
                });
            }
        }

        return $query
            ->orderBy($definition['sorts'][$parameters['sort']], $parameters['direction'])
            ->orderBy('products.id');
    }

    protected function applyAttributeCondition(Builder $sub, Attribute $attribute, mixed $value): void
    {
        $sub->where('attribute_id', $attribute->id);

        if ($attribute->type === 'boolean') {
            $sub->where('boolean_value', filter_var($value, FILTER_VALIDATE_BOOLEAN));
        } elseif (is_numeric($value)) {
            $sub->where(function (Builder $valQ) use ($value): void {
                $valQ->where('integer_value', (int) $value)
                    ->orWhere('text_value', (string) $value)
                    ->orWhereJsonContains('json_value', (int) $value)
                    ->orWhereJsonContains('json_value', (string) $value);
            });
        } else {
            $sub->where(function (Builder $valQ) use ($value): void {
                $valQ->where('text_value', (string) $value)
                    ->orWhere('text_value', 'like', '%'.$value.'%')
                    ->orWhereJsonContains('json_value', (string) $value);
            });
        }
    }

    public function row(Model $model): array
    {
        /** @var Product $model */
        return [
            'id' => $model->id,
            'sku' => $model->sku,
            'type' => $model->type,
            'family' => $model->productType?->name ?? '—',
            'status' => (bool) $model->status,
            'created_at' => $model->created_at?->format('M d, Y H:i'),
        ];
    }

    public function filterOptions(): array
    {
        $options = [];

        // 1. Product Family filter options
        $families = ProductType::query()->select(['id', 'name'])->orderBy('name')->get();
        $options['family'] = $families->map(fn (ProductType $pt): array => [
            'value' => (string) $pt->id,
            'label' => $pt->name,
        ])->values()->all();

        // 2. Filterable Attributes options
        if (Schema::hasTable('attributes')) {
            $filterableAttributes = Attribute::query()
                ->where('is_filterable', true)
                ->with(['options' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
                ->orderBy('name')
                ->get();

            foreach ($filterableAttributes as $attribute) {
                if ($attribute->type === 'boolean') {
                    $options[$attribute->code] = [
                        ['value' => '1', 'label' => 'Yes'],
                        ['value' => '0', 'label' => 'No'],
                    ];
                } elseif ($attribute->options->isNotEmpty()) {
                    $options[$attribute->code] = $attribute->options->map(fn ($opt): array => [
                        'value' => (string) $opt->id,
                        'label' => (string) ($opt->label ?? $opt->code),
                    ])->values()->all();
                } else {
                    $distinctValues = ProductAttributeValue::query()
                        ->where('attribute_id', $attribute->id)
                        ->whereNotNull('text_value')
                        ->where('text_value', '!=', '')
                        ->distinct()
                        ->limit(50)
                        ->pluck('text_value');

                    $options[$attribute->code] = $distinctValues->map(fn ($val): array => [
                        'value' => (string) $val,
                        'label' => (string) $val,
                    ])->values()->all();
                }
            }
        }

        return $options;
    }

    public function acceptsFilterValue(string $filter, mixed $value): bool
    {
        if ($filter === 'family') {
            return is_numeric($value) && ProductType::query()->where('id', $value)->exists();
        }

        if (Schema::hasTable('attributes')) {
            $exists = Attribute::query()
                ->where('is_filterable', true)
                ->where('code', $filter)
                ->exists();

            if ($exists) {
                return $value !== '' && $value !== null;
            }
        }

        return false;
    }

    public function isSelectable(User $actor, Model $record): bool
    {
        return true;
    }
}
