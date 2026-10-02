import type { GridConfig } from '../../types/grid'

export const attributesGrid: GridConfig = {
  key: 'attributes',
  rowKey: 'id',
  endpoint: '/api/grids/attributes',
  searchableFields: ['name', 'code'],
  searchPlaceholder: 'Search by name or code…',
  columns: [
    { key: 'name', label: 'Name', sortable: true },
    { key: 'code', label: 'Code', sortable: true },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'required', label: 'Required', sortable: true, cell: 'required' },
  ],
  filters: [
    { key: 'type', label: 'Type', type: 'select' },
    { key: 'required', label: 'Required', type: 'select' },
  ],
  defaultSort: 'name',
  defaultDirection: 'asc',
  defaultPageSize: 10,
  pageSizes: [10, 25, 50],
  actions: [
    { key: 'create', label: 'Create attribute', icon: 'plus', permission: 'catalog.attributes.create' },
    { key: 'edit', label: 'Edit attribute', icon: 'pencil', permission: 'catalog.attributes.edit' },
    { key: 'delete', label: 'Delete attribute', icon: 'trash', permission: 'catalog.attributes.delete' },
  ],
  selectable: false,
  bulkActions: [],
  bulkEditFields: {},
}
