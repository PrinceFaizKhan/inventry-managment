<?php

use Modules\Catalog\Grid\AttributesGridProvider;

return [[
    'key' => 'attributes',
    'endpoint' => '/api/grids/attributes',
    'row_identifier' => 'id',
    'provider' => AttributesGridProvider::class,
    'searchable' => ['name', 'code'],
    'sorts' => [
        'name' => 'name',
        'code' => 'code',
        'type' => 'type',
        'required' => 'required',
    ],
    'filters' => [
        'type' => ['type' => 'select'],
        'required' => ['type' => 'select'],
    ],
    'page_sizes' => [10, 25, 50],
    'default_sort' => 'name',
    'default_direction' => 'asc',
    'view_permission' => 'catalog.attributes.view',
    'selectable' => false,
    'bulk_actions' => [],
    'bulk_edit_fields' => [],
    'actions' => [
        'create' => 'catalog.attributes.create',
        'edit' => 'catalog.attributes.edit',
        'delete' => 'catalog.attributes.delete',
    ],
]];
