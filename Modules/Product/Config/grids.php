<?php

use Illuminate\Support\Facades\Schema;
use Modules\Attribute\Models\Attribute;
use Modules\Product\Grid\ProductsGridProvider;

$productFilters = [
    'family' => ['type' => 'select'],
];

try {
    if (Schema::hasTable('attributes')) {
        $filterableCodes = Attribute::query()
            ->where('is_filterable', true)
            ->pluck('code')
            ->all();

        foreach ($filterableCodes as $code) {
            $productFilters[$code] = ['type' => 'select'];
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
        'columns' => [
            ['index' => 'sku', 'label' => 'SKU', 'sortable' => true, 'searchable' => true],
            ['index' => 'type', 'label' => 'Type', 'sortable' => true],
            ['index' => 'family', 'label' => 'Family', 'sortable' => false],
            ['index' => 'status', 'label' => 'Status', 'sortable' => true, 'align' => 'center'],
            ['index' => 'created_at', 'label' => 'Created At', 'sortable' => true],
        ],
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
