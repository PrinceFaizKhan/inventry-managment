<?php

namespace Tests\Support;

use Modules\Core\Support\Grid\GridRegistry;
use Modules\Settings\Actions\UsersBulkActions;
use Modules\Settings\Grid\UsersGridProvider;

class SelectableUsersGridRegistry extends GridRegistry
{
    public function __construct(
        private readonly string $editPermission = 'authenticated',
        private readonly string $deletePermission = 'authenticated',
        private readonly string $fieldPermission = 'authenticated',
    ) {}

    public function find(string $key): array
    {
        return [
            'key' => 'users',
            'endpoint' => 'api/grids/users',
            'row_identifier' => 'id',
            'provider' => UsersGridProvider::class,
            'searchable' => ['users.name', 'users.email'],
            'sorts' => ['name' => 'users.name', 'email' => 'users.email', 'role' => 'roles.name', 'status' => 'users.status'],
            'filters' => ['role_id' => ['type' => 'select'], 'status' => ['type' => 'select']],
            'page_sizes' => [10, 25, 50],
            'default_sort' => 'name',
            'default_direction' => 'asc',
            'view_permission' => 'authenticated',
            'selectable' => true,
            'bulk_handler' => UsersBulkActions::class,
            'bulk_actions' => [
                'activate_selected' => 'authenticated',
                'deactivate_selected' => 'authenticated',
                'edit_selected' => $this->editPermission,
                'delete_selected' => $this->deletePermission,
            ],
            'bulk_edit_fields' => [
                'status' => ['label' => 'Status', 'type' => 'select', 'permission' => $this->editPermission, 'options' => ['active' => 'Active', 'inactive' => 'Inactive']],
                'role_id' => ['label' => 'Role', 'type' => 'select', 'permission' => $this->fieldPermission, 'nullable' => true, 'options_source' => 'roles'],
            ],
        ];
    }
}
