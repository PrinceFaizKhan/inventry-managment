<script setup lang="ts">
import { computed, nextTick, ref, watch, watchEffect } from 'vue'
import { ArrowDown, ArrowUp, Plus } from 'lucide-vue-next'
import FilterDrawer from './FilterDrawer.vue'
import GridPagination from './GridPagination.vue'
import GridStates from './GridStates.vue'
import GridToolbar from './GridToolbar.vue'
import RowActions from './RowActions.vue'
import ConfirmDialog from './ConfirmDialog.vue'
import BulkActionsMenu from './BulkActionsMenu.vue'
import BulkEditDialog from './BulkEditDialog.vue'
import { useDataGrid } from '../../composables/useDataGrid'
import { request } from '../../lib/http'
import type { GridAction, GridBulkAction, GridConfig, GridRow } from '../../types/grid'

const props = defineProps<{ config: GridConfig }>()
const emit = defineEmits<{ create: []; edit: [row: GridRow]; delete: [row: GridRow]; notify: [message: string] }>()
const { rows, meta, search, filters, sort, direction, loading, error, activeFilters, selectedIds, clearSelection, updateSearch, applyFilters: commitFilters, clearFilter, updateSort, updatePage, updatePageSize, load } = useDataGrid(props.config)
const filtersOpen = ref(false)
const toolbar = ref<{ focusFilterButton: () => void } | null>(null)
const pendingDelete = ref<GridRow | null>(null)
const pendingBulkDelete = ref(false)
const bulkEditOpen = ref(false)
const bulkSubmitting = ref(false)
const bulkErrors = ref<Record<string, string>>({})
const headerCheckbox = ref<HTMLInputElement | null>(null)
const bulkMenu = ref<{ focusTrigger: () => void } | null>(null)
const createAction = computed(() => props.config.actions.find((action) => action.key === 'create'))
defineExpose({ refresh: load })
const selectableRows = computed(() => rows.value.filter((row) => row.selectable === true))
const selectedOnPage = computed(() => selectableRows.value.filter((row) => selectedIds.value.includes(row[props.config.rowKey] as string | number)))
const allSelectedOnPage = computed(() => selectableRows.value.length > 0 && selectedOnPage.value.length === selectableRows.value.length)
const someSelectedOnPage = computed(() => selectedOnPage.value.length > 0 && !allSelectedOnPage.value)
const selectable = computed(() => props.config.selectable && meta.value.selectable)
const configuredBulkActions = computed(() => props.config.bulkActions)
const deleteDescription = computed(() => `Delete ${selectedIds.value.length} selected ${selectedIds.value.length === 1 ? 'record' : 'records'}? This action cannot be undone.`)
watchEffect(() => { if (headerCheckbox.value) headerCheckbox.value.indeterminate = someSelectedOnPage.value })
watch(() => [search.value, meta.value.current_page, meta.value.per_page, sort.value, direction.value, JSON.stringify(filters.value)], clearSelection)
watch(filtersOpen, async (open, wasOpen) => { if (!open && wasOpen) { await nextTick(); toolbar.value?.focusFilterButton() } })
watch(bulkEditOpen, async (open, wasOpen) => { if (!open && wasOpen) { await nextTick(); bulkMenu.value?.focusTrigger() } })
function actionSelected(action: GridAction, row: GridRow): void { if (action.key === 'delete') { pendingDelete.value = row; return } if (action.key === 'edit') emit('edit', row) }
function confirmDelete(): void { if (pendingDelete.value) emit('delete', pendingDelete.value); pendingDelete.value = null }
function toggleRow(row: GridRow): void { const id = row[props.config.rowKey] as string | number; selectedIds.value = selectedIds.value.includes(id) ? selectedIds.value.filter((selectedId) => selectedId !== id) : [...selectedIds.value, id] }
function togglePage(): void { const pageIds = selectableRows.value.map((row) => row[props.config.rowKey] as string | number); selectedIds.value = allSelectedOnPage.value ? selectedIds.value.filter((id) => !pageIds.includes(id)) : [...new Set([...selectedIds.value, ...pageIds])] }
function applyFilterDraft(values: Record<string, string | number>): void { commitFilters(values); filtersOpen.value = false }
function bulkAction(key: GridBulkAction['key']): void { if (key === 'edit_selected') { bulkErrors.value = {}; bulkEditOpen.value = true } else if (key === 'delete_selected') { pendingBulkDelete.value = true } else { void submitBulk(key, {}) } }
async function submitBulk(action: string, changes: Record<string, string | number | null>): Promise<void> {
  if (!selectedIds.value.length || bulkSubmitting.value) return
  bulkSubmitting.value = true
  bulkErrors.value = {}
  try {
    const result = await request(`/api/grids/${props.config.key}/bulk/${action}`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ids: selectedIds.value, ...(action === 'edit_selected' ? { changes } : {}) }) })
    clearSelection()
    bulkEditOpen.value = false
    pendingBulkDelete.value = false
    emit('notify', result.message)
    await load()
  } catch (caught) {
    const error = caught as Error & { errors?: Record<string, string[]> }
    bulkErrors.value = Object.fromEntries(Object.entries(error.errors || {}).map(([key, messages]) => [key, messages[0]]))
    emit('notify', error.message)
  } finally { bulkSubmitting.value = false }
}
</script>
<template>
  <section class="data-grid">
    <div class="data-grid__top">
      <div class="grid-page-actions"><slot name="title"/><button v-if="createAction && meta.actions[createAction.key]" class="button button--compact" type="button" @click="emit('create')"><Plus :size="16"/>{{ createAction.label }}</button></div>
      <div v-if="$slots.title" class="grid-toolbar-separator" aria-hidden="true"></div>
      <GridToolbar ref="toolbar" :search="search" :placeholder="config.searchPlaceholder" :filter-count="activeFilters.length" :show-filters="filtersOpen" :total="meta.total" @search="updateSearch" @toggle-filters="filtersOpen=!filtersOpen"/>
      <div v-if="activeFilters.length" class="grid-chips"><button v-for="filter in activeFilters" :key="filter.key" type="button" @click="clearFilter(filter.key)">{{ filter.label }}: {{ filter.option }} <span>×</span></button></div>
      <BulkActionsMenu v-if="selectable" ref="bulkMenu" :count="selectedIds.length" :actions="configuredBulkActions" :allowed="meta.bulk_actions" @action="bulkAction" @clear="clearSelection"/>
    </div>
    <FilterDrawer v-if="filtersOpen" :filters="config.filters" :values="filters" :options="meta.filter_options" @close="filtersOpen=false" @apply="applyFilterDraft"/>
    <div class="grid-table-wrap">
      <div v-if="loading && !rows.length" class="grid-state-wrap"><GridStates type="loading"/></div>
      <GridStates v-else-if="error" type="error" :message="error"/>
      <GridStates v-else-if="!rows.length" type="empty"/>
      <table v-else>
        <thead><tr>
          <th v-if="selectable" class="grid-select-cell"><input ref="headerCheckbox" type="checkbox" aria-label="Select all selectable rows on this page" :checked="allSelectedOnPage" :disabled="!selectableRows.length" @change="togglePage"/></th>
          <th v-for="column in config.columns" :key="column.key"><button v-if="column.sortable" type="button" @click="updateSort(column.sortKey || column.key)">{{ column.label }}<ArrowUp v-if="sort === (column.sortKey || column.key) && direction === 'asc'" :size="13"/><ArrowDown v-else-if="sort === (column.sortKey || column.key)" :size="13"/></button><span v-else>{{ column.label }}</span></th>
          <th>Actions</th>
        </tr></thead>
        <tbody><tr v-for="row in rows" :key="String(row[config.rowKey])">
          <td v-if="selectable" class="grid-select-cell"><input type="checkbox" :aria-label="`Select ${String(row.name || row[config.rowKey])}`" :checked="selectedIds.includes(row[config.rowKey] as string | number)" :disabled="row.selectable !== true" @change="toggleRow(row)"/></td>
          <td v-for="column in config.columns" :key="column.key"><slot :name="`cell-${column.cell || column.key}`" :row="row">{{ row[column.key] ?? '—' }}</slot></td>
          <td><RowActions :row="row" :actions="config.actions" :allowed="meta.actions" @action="actionSelected"/></td>
        </tr></tbody>
      </table>
      <div v-if="loading && rows.length" class="grid-refreshing">Refreshing…</div>
    </div>
    <GridPagination :current-page="meta.current_page" :last-page="meta.last_page" :per-page="meta.per_page" :page-sizes="config.pageSizes" :total="meta.total" :from="meta.from" :to="meta.to" @page="updatePage" @page-size="updatePageSize" @invalid-page="emit('notify',$event)"/>
    <ConfirmDialog :open="!!pendingDelete" title="Delete this record?" description="This action cannot be undone." @cancel="pendingDelete=null" @confirm="confirmDelete"/>
    <ConfirmDialog :open="pendingBulkDelete" :title="`Delete ${selectedIds.length} selected ${selectedIds.length === 1 ? 'record' : 'records'}?`" :description="deleteDescription" confirm-text="Delete selected" :submitting="bulkSubmitting" @cancel="pendingBulkDelete=false" @confirm="submitBulk('delete_selected',{})"/>
    <BulkEditDialog v-if="bulkEditOpen" :key="selectedIds.join(',')" :count="selectedIds.length" :fields="meta.bulk_edit_fields" :submitting="bulkSubmitting" :errors="bulkErrors" @cancel="bulkEditOpen=false" @submit="submitBulk('edit_selected',$event)"/>
  </section>
</template>
