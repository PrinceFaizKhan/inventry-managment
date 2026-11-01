<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Attribute\Models\Attribute;
use Modules\Product\Grid\ProductsGridProvider;

$productFilters = [
    'family' => ['type' => 'select', 'label' => 'Product Family'],
    'type' => ['type' => 'select', 'label' => 'Product Type'],
    'status' => ['type' => 'select', 'label' => 'Status'],
];

$productColumns = [
    ['index' => 'sku', 'label' => 'SKU', 'sortable' => true, 'searchable' => true, 'group' => 'Core'],
    ['index' => 'status', 'label' => 'Status', 'sortable' => true, 'align' => 'center', 'group' => 'Core'],
    ['index' => 'type', 'label' => 'Type', 'sortable' => true, 'group' => 'Core'],
    ['index' => 'family', 'label' => 'Family', 'sortable' => false, 'group' => 'Core'],
    ['index' => 'created_at', 'label' => 'Created At', 'sortable' => true, 'group' => 'Core'],
];

try {
    if (Schema::hasTable('attributes')) {
        $filterableAttributes = Attribute::query()
            ->where('is_filterable', true)
            ->get();

        foreach ($filterableAttributes as $attr) {
            if (in_array($attr->code, ['family', 'type', 'status'], true)) {
                continue;
            }

            $productFilters[$attr->code] = [
                'type' => 'select',
                'label' => $attr->name,
            ];
        }

        // Fetch all attributes that are attached to product types/families
        $familyAttrIds = collect();
        if (Schema::hasTable('product_type_group_attributes')) {
            $groupAttrIds = DB::table('product_type_group_attributes')->distinct()->pluck('attribute_id');
            $familyAttrIds = $familyAttrIds->merge($groupAttrIds);
        }
        if (Schema::hasTable('product_type_variant_axes')) {
            $axesAttrIds = DB::table('product_type_variant_axes')->distinct()->pluck('attribute_id');
            $familyAttrIds = $familyAttrIds->merge($axesAttrIds);
        }

        $familyAttrIds = $familyAttrIds->unique()->filter()->values();

        if ($familyAttrIds->isNotEmpty()) {
            $familyAttributes = Attribute::query()
                ->whereIn('id', $familyAttrIds)
                ->orderBy('name')
                ->get();

            foreach ($familyAttributes as $attr) {
                if (in_array($attr->code, ['sku', 'status', 'type', 'family', 'created_at'])) {
                    continue;
                }

                $productColumns[] = [
                    'index' => $attr->code,
                    'label' => $attr->name,
                    'sortable' => false,
                    'is_attribute' => true,
                    'type' => $attr->type,
                    'group' => 'Family Attributes',
                ];
            }
        }
    }
} catch (Throwable) {
    // Fallback if table not ready
}

return [
    [
        'key' => 'products',
        'endpoint' => '/api/grids/products',
        'row_identifier' => 'id',
        'provider' => ProductsGridProvider::class,
        'searchable' => ['sku'],
        'sorts' => [
            'sku' => 'products.sku',
            'type' => 'products.type',
            'status' => 'products.status',
            'created_at' => 'products.created_at',
        ],
        'default_columns' => ['sku', 'status'],
        'columns' => $productColumns,
        'filters' => $productFilters,
        'page_sizes' => [10, 25, 50],
        'default_sort' => 'created_at',
        'default_direction' => 'desc',
        'view_permission' => 'catalog.products.view',
        'selectable' => false,
        'bulk_actions' => [],
        'bulk_edit_fields' => [],
        'actions' => [
            'create' => 'catalog.products.create',
            'edit' => 'catalog.products.edit',
            'delete' => 'catalog.products.delete',
        ],
    ],
];
