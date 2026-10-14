<?php

use Modules\Settings\Actions\UsersBulkActions;
use Modules\Settings\Grid\UsersGridProvider;

return [
    'key' => 'users',
    'endpoint' => 'api/grids/users',
    'row_identifier' => 'id',
    'provider' => UsersGridProvider::class,
    'bulk_handler' => UsersBulkActions::class,
    'searchable' => ['users.name', 'users.email'],
    'sorts' => [
        'name' => 'users.name',
        'email' => 'users.email',
        'role' => 'roles.name',
        'status' => 'users.status',
    ],
    'filters' => [
        'role_id' => ['type' => 'select'],
        'status' => ['type' => 'select'],
    ],
    'page_sizes' => [10, 25, 50],
    'default_sort' => 'name',
    'default_direction' => 'asc',
    'view_permission' => 'authenticated',
    'selectable' => false,
    'bulk_actions' => [
        'activate_selected' => 'settings.users.activate',
        'deactivate_selected' => 'settings.users.deactivate',
        'edit_selected' => 'settings.users.edit',
        'delete_selected' => 'settings.users.delete',
    ],
    'bulk_edit_fields' => [
        'status' => ['label' => 'Status', 'type' => 'select', 'permission' => 'settings.users.edit', 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
        'role_id' => ['label' => 'Role', 'type' => 'select', 'permission' => 'settings.users.edit', 'nullable' => true, 'options_source' => 'roles'],
    ],
    'actions' => [
        'create' => 'settings.users.create',
        'edit' => 'settings.users.edit',
        'delete' => 'settings.users.delete',
    ],
];
