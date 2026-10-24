<?php

use Modules\ProductTypes\Grid\ProductTypesGridProvider;

return [
    [
        'key' => 'product-types',
        'endpoint' => '/api/grids/product-types',
        'row_identifier' => 'id',
        'provider' => ProductTypesGridProvider::class,
        'searchable' => ['name', 'code'],
        'sorts' => [
            'name' => 'name',
            'code' => 'code',
            'created_at' => 'created_at',
        ],
        'columns' => [
            ['index' => 'code', 'label' => 'Code', 'sortable' => true, 'searchable' => true],
            ['index' => 'name', 'label' => 'Name', 'sortable' => true, 'searchable' => true],
            ['index' => 'groups_count', 'label' => 'Groups', 'sortable' => false, 'align' => 'center'],
            ['index' => 'attributes_count', 'label' => 'Attributes', 'sortable' => false, 'align' => 'center'],
            ['index' => 'variants_count', 'label' => 'Variant Types', 'sortable' => false, 'align' => 'center'],
            ['index' => 'created_at', 'label' => 'Created At', 'sortable' => true],
        ],
        'filters' => [],
        'page_sizes' => [10, 25, 50],
        'default_sort' => 'name',
        'default_direction' => 'asc',
        'view_permission' => 'catalog.product-types.view',
        'selectable' => false,
        'bulk_actions' => [],
        'bulk_edit_fields' => [],
        'actions' => [
            'create' => 'catalog.product-types.create',
            'edit' => 'catalog.product-types.edit',
            'delete' => 'catalog.product-types.delete',
        ],
    ],
];
