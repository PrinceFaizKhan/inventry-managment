<?php

use Modules\Attribute\Grid\AttributeGroupsGridProvider;
use Modules\Attribute\Grid\AttributesGridProvider;

return [
    [
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
            'is_filterable' => 'is_filterable',
        ],
        'columns' => [
            ['index' => 'code', 'label' => 'Code', 'sortable' => true, 'searchable' => true],
            ['index' => 'name', 'label' => 'Name', 'sortable' => true, 'searchable' => true],
            ['index' => 'type', 'label' => 'Type', 'sortable' => true],
            ['index' => 'required', 'label' => 'Required', 'sortable' => true, 'align' => 'center'],
            ['index' => 'is_filterable', 'label' => 'Filterable', 'sortable' => true, 'align' => 'center'],
        ],
        'filters' => [
            'type' => ['type' => 'select'],
            'required' => ['type' => 'select'],
            'is_filterable' => ['type' => 'select'],
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
    ],
    [
        'key' => 'attribute-groups',
        'endpoint' => '/api/grids/attribute-groups',
        'row_identifier' => 'id',
        'provider' => AttributeGroupsGridProvider::class,
        'searchable' => ['name', 'code'],
        'sorts' => [
            'name' => 'name',
            'code' => 'code',
            'created_at' => 'created_at',
        ],
        'columns' => [
            ['index' => 'code', 'label' => 'Code', 'sortable' => true, 'searchable' => true],
            ['index' => 'name', 'label' => 'Name', 'sortable' => true, 'searchable' => true],
            ['index' => 'created_at', 'label' => 'Created At', 'sortable' => true],
        ],
        'filters' => [],
        'page_sizes' => [10, 25, 50],
        'default_sort' => 'name',
        'default_direction' => 'asc',
        'view_permission' => 'catalog.attribute-groups.view',
        'selectable' => false,
        'bulk_actions' => [],
        'bulk_edit_fields' => [],
        'actions' => [
            'create' => 'catalog.attribute-groups.create',
            'edit' => 'catalog.attribute-groups.edit',
            'delete' => 'catalog.attribute-groups.delete',
        ],
    ],
];
