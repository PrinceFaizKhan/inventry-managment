<?php

namespace Modules\Product\DataGrids;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Modules\Attribute\Models\Attribute;
use Modules\Core\DataGrid\Action;
use Modules\Core\DataGrid\Column;
use Modules\Core\DataGrid\DataGrid;
use Modules\Core\DataGrid\Filter;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductAttributeValue;
use Modules\ProductTypes\Models\ProductType;

class ProductDataGrid extends DataGrid
{
    public function build(): void
    {
        $this->source(
            Product::query()
                ->select(['products.id', 'products.sku', 'products.type', 'products.product_type_id', 'products.status', 'products.created_at'])
                ->with(['productType:id,name,code'])
                ->parentsOnly()
        )
            ->primaryKey('id')
            ->searchable(['sku'])
            ->defaultSort('created_at', 'desc')
            ->searchPlaceholder('Search by SKU or Family...')
            ->columns([
                Column::make('sku')->label('SKU')->sortable()->searchable(),
                Column::make('type')->label('Type')->sortable(),
                Column::make('family')->label('Family')->sortable(false),
                Column::make('status')->label('Status')->sortable(),
                Column::make('created_at')->label('Created At')->sortable(),
            ])
            ->filters($this->buildFilters())
            ->actions([
                Action::edit('catalog.products.edit', fn ($row) => url('catalog/products/'.$row->id.'/edit')),
                Action::delete('catalog.products.delete', fn ($row) => url('api/catalog/products/'.$row->id)),
            ]);
    }

    /**
     * @return array<int, Filter>
     */
    protected function buildFilters(): array
    {
        $filters = [
            Filter::select('family')
                ->label('Family')
                ->options(fn () => ProductType::query()->orderBy('name')->pluck('name', 'id')->all())
                ->query(fn ($query, $value) => $query->where('products.product_type_id', $value)),
        ];

        if (Schema::hasTable('attributes')) {
            $filterableAttributes = Attribute::query()
                ->where('is_filterable', true)
                ->with(['options' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])
                ->orderBy('name')
                ->get();

            foreach ($filterableAttributes as $attribute) {
                $filter = Filter::select($attribute->code)->label($attribute->name);

                if ($attribute->type === 'boolean') {
                    $filter->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ]);
                } elseif ($attribute->options->isNotEmpty()) {
                    $filter->options(
                        fn () => $attribute->options()->pluck('label', 'id')->all()
                    );
                } else {
                    $filter->options(
                        fn () => ProductAttributeValue::query()
                            ->where('attribute_id', $attribute->id)
                            ->whereNotNull('text_value')
                            ->where('text_value', '!=', '')
                            ->distinct()
                            ->limit(50)
                            ->pluck('text_value', 'text_value')
                            ->all()
                    );
                }

                $filter->query(function ($query, $value) use ($attribute): void {
                    $query->where(function (Builder $productQuery) use ($attribute, $value): void {
                        $productQuery->whereHas('attributeValues', function (Builder $sub) use ($attribute, $value): void {
                            $this->applyAttributeCondition($sub, $attribute, $value);
                        })->orWhereHas('variants.attributeValues', function (Builder $sub) use ($attribute, $value): void {
                            $this->applyAttributeCondition($sub, $attribute, $value);
                        });
                    });
                });

                $filters[] = $filter;
            }
        }

        return $filters;
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
}
