import type { GridConfig } from '../../types/grid'

export const usersGrid: GridConfig = {
  key: 'users',
  rowKey: 'id',
  endpoint: '/api/grids/users',
  searchableFields: ['name', 'email'],
  searchPlaceholder: 'Search by name or email…',
  columns: [
    { key: 'user', label: 'User', sortable: true, sortKey: 'name', cell: 'user' },
    { key: 'role', label: 'Role', sortable: true, cell: 'role' },
    { key: 'status', label: 'Status', sortable: true, cell: 'status' },
  ],
  filters: [
    { key: 'role_id', label: 'Role', type: 'select' },
    { key: 'status', label: 'Status', type: 'select' },
  ],
  defaultSort: 'name',
  defaultDirection: 'asc',
  defaultPageSize: 10,
  pageSizes: [10, 25, 50],
  actions: [
    { key: 'create', label: 'Create user', icon: 'plus', permission: 'settings.users.create' },
    { key: 'edit', label: 'Edit user', icon: 'pencil', permission: 'settings.users.edit' },
    { key: 'delete', label: 'Delete user', icon: 'trash', permission: 'settings.users.delete' },
  ],
  selectable: false,
  bulkActions: [
    { key: 'activate_selected', label: 'Activate selected', permission: 'settings.users.activate' },
    { key: 'deactivate_selected', label: 'Deactivate selected', permission: 'settings.users.deactivate' },
    { key: 'edit_selected', label: 'Edit selected', permission: 'settings.users.edit' },
    { key: 'delete_selected', label: 'Delete selected', permission: 'settings.users.delete' },
  ],
  bulkEditFields: {},
}
