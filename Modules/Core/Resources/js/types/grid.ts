export type SortDirection = 'asc' | 'desc'

export interface GridColumn {
  key: string
  label: string
  sortable?: boolean
  cell?: string
  sortKey?: string
}

export interface GridFilter {
  key: string
  label: string
  type: 'select'
}

export interface GridAction {
  key: 'create' | 'edit' | 'delete'
  label: string
  icon: 'plus' | 'pencil' | 'trash'
  permission: string
}

export interface GridBulkAction {
  key: 'activate_selected' | 'deactivate_selected' | 'edit_selected' | 'delete_selected'
  label: string
  permission: string
}

export interface BulkEditField {
  label: string
  type: 'text' | 'select'
  nullable?: boolean
  options?: Record<string, string>
}

export interface GridConfig {
  key: string
  rowKey: string
  endpoint: string
  searchableFields: string[]
  searchPlaceholder: string
  columns: GridColumn[]
  filters: GridFilter[]
  defaultSort: string
  defaultDirection: SortDirection
  defaultPageSize: number
  pageSizes: number[]
  actions: GridAction[]
  selectable: boolean
  bulkActions: { key: 'activate_selected' | 'deactivate_selected' | 'edit_selected' | 'delete_selected'; label: string; permission: string }[]
  bulkEditFields: Record<string, BulkEditField>
}

export interface GridRow { id: number | string; [key: string]: unknown }

export interface GridMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
  filter_options: Record<string, { value: string | number; label: string }[]>
  actions: Record<string, boolean>
  selectable: boolean
  bulk_actions: Record<string, boolean>
  bulk_edit_fields: Record<string, BulkEditField>
}

export interface GridResponse { data: GridRow[]; meta: GridMeta }
