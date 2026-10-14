import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { request } from '../lib/http'
import type { GridConfig, GridMeta, GridResponse, GridRow, SortDirection } from '../types/grid'

const emptyMeta = (): GridMeta => ({ current_page: 1, last_page: 1, per_page: 10, total: 0, from: null, to: null, filter_options: {}, actions: {}, selectable: false, bulk_actions: {}, bulk_edit_fields: {} })

export function useDataGrid(config: GridConfig) {
  const route = useRoute()
  const router = useRouter()
  const rows = ref<GridRow[]>([])
  const meta = ref<GridMeta>({ ...emptyMeta(), per_page: config.defaultPageSize })
  const search = ref('')
  const filters = ref<Record<string, string | number>>({})
  const sort = ref(config.defaultSort)
  const direction = ref<SortDirection>(config.defaultDirection)
  const loading = ref(false)
  const error = ref('')
  const selectedIds = ref<Array<string | number>>([])
  let controller: AbortController | null = null
  let searchTimer: number | undefined
  let requestVersion = 0

  const activeFilters = computed(() => Object.entries(filters.value).map(([key, value]) => ({
    key,
    value,
    label: config.filters.find((filter) => filter.key === key)?.label ?? key,
    option: meta.value.filter_options[key]?.find((item) => String(item.value) === String(value))?.label ?? String(value),
  })))

  function readRoute(): void {
    const query = route.query
    const querySearch = query[`${config.key}_search`]
    const querySort = query[`${config.key}_sort`]
    search.value = typeof querySearch === 'string' ? querySearch : ''
    sort.value = typeof querySort === 'string' ? querySort : config.defaultSort
    direction.value = query[`${config.key}_direction`] === 'desc' ? 'desc' : config.defaultDirection
    meta.value.current_page = Number(query[`${config.key}_page`]) || 1
    meta.value.per_page = config.pageSizes.includes(Number(query[`${config.key}_per_page`])) ? Number(query[`${config.key}_per_page`]) : config.defaultPageSize
    filters.value = Object.fromEntries(config.filters.flatMap((filter) => {
      const value = query[`${config.key}_filter_${filter.key}`]
      return typeof value === 'string' && value !== '' ? [[filter.key, value]] : []
    }))
  }

  async function load(): Promise<void> {
    controller?.abort()
    controller = new AbortController()
    const version = ++requestVersion
    loading.value = true
    error.value = ''
    const params = new URLSearchParams({ search: search.value, sort: sort.value, direction: direction.value, page: String(meta.value.current_page), per_page: String(meta.value.per_page) })
    Object.entries(filters.value).forEach(([key, value]) => params.set(`filter[${key}]`, String(value)))
    try {
      const data = await request(`${config.endpoint}?${params}`, { signal: controller.signal }) as GridResponse
      if (version === requestVersion) {
        if (meta.value.current_page > data.meta.last_page) {
          selectedIds.value = []
          writeRoute({ page: data.meta.last_page })
          return
        }
        rows.value = data.data
        meta.value = data.meta
      }
    } catch (caught) {
      if ((caught as Error).name !== 'AbortError' && version === requestVersion) error.value = (caught as Error).message
    } finally {
      if (version === requestVersion) loading.value = false
    }
  }

  function writeRoute(patch: Record<string, string | number | undefined>): void {
    const query = { ...route.query } as Record<string, string | undefined>
    Object.entries(patch).forEach(([key, value]) => {
      const queryKey = `${config.key}_${key}`
      if (value === undefined || value === '' || value === config.defaultSort || (key === 'direction' && value === config.defaultDirection)) delete query[queryKey]
      else query[queryKey] = String(value)
    })
    router.push({ query })
  }

  function updateSearch(value: string): void {
    search.value = value
    selectedIds.value = []
    window.clearTimeout(searchTimer)
    searchTimer = window.setTimeout(() => writeRoute({ search: value, page: 1 }), 300)
  }

  function applyFilters(nextFilters: Record<string, string | number>): void {
    selectedIds.value = []
    window.clearTimeout(searchTimer)
    const patch: Record<string, string | number | undefined> = { search: search.value, page: 1 }
    config.filters.forEach((filter) => {
      patch[`filter_${filter.key}`] = nextFilters[filter.key] || undefined
    })
    writeRoute(patch)
  }
  function clearFilter(key: string): void {
    const nextFilters = { ...filters.value }
    delete nextFilters[key]
    applyFilters(nextFilters)
  }
  function updateSort(column: string): void { selectedIds.value = []; writeRoute({ sort: column, direction: sort.value === column && direction.value === 'asc' ? 'desc' : 'asc', page: 1 }) }
  function updatePage(page: number): void { selectedIds.value = []; writeRoute({ page }) }
  function updatePageSize(size: number): void { selectedIds.value = []; writeRoute({ per_page: size, page: 1 }) }

  function clearSelection(): void { selectedIds.value = [] }

  watch(() => route.fullPath, () => { selectedIds.value = []; readRoute(); load() }, { immediate: true })
  onBeforeUnmount(() => { controller?.abort(); window.clearTimeout(searchTimer) })

  return { rows, meta, search, filters, sort, direction, loading, error, activeFilters, selectedIds, clearSelection, load, updateSearch, applyFilters, clearFilter, updateSort, updatePage, updatePageSize }
}
