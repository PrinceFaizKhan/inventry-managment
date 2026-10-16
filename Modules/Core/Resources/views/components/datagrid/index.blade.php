@props([
    'config' => null,
])

<v-datagrid :config="{{ json_encode($config ?? new \stdClass()) }}"></v-datagrid>

@pushOnce('scripts')
<script type="text/x-template" id="v-datagrid-template">
    <section class="data-grid">
        <div class="data-grid__top">
            <div class="grid-page-actions">
                <slot name="title"></slot>
                <button
                    v-if="createAction && meta.actions && meta.actions[createAction.key]"
                    class="button button--compact"
                    type="button"
                    @click="$emit('create')"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>@{{ createAction.label }}</span>
                </button>
            </div>
            <div v-if="$slots.title" class="grid-toolbar-separator" aria-hidden="true"></div>
            <v-grid-toolbar
                :search="search"
                :placeholder="config.searchPlaceholder || 'Search records...'"
                :filter-count="activeFilters.length"
                :show-filters="filtersOpen"
                :total="meta.total"
                @search="updateSearch"
                @toggle-filters="filtersOpen = !filtersOpen"
            ></v-grid-toolbar>
            <div v-if="activeFilters.length" class="grid-chips">
                <button
                    v-for="filter in activeFilters"
                    :key="filter.key"
                    type="button"
                    @click="clearFilter(filter.key)"
                >
                    @{{ filter.label }}: @{{ filter.option }} <span>×</span>
                </button>
            </div>
            <v-bulk-actions-menu
                v-if="isSelectable"
                :count="selectedIds.length"
                :actions="config.bulkActions || []"
                :allowed="meta.bulk_actions || {}"
                @action="bulkAction"
                @clear="clearSelection"
            ></v-bulk-actions-menu>
        </div>

        <v-filter-drawer
            v-if="filtersOpen"
            :filters="config.filters || []"
            :values="filters"
            :options="meta.filter_options || {}"
            @close="filtersOpen = false"
            @apply="applyFilterDraft"
        ></v-filter-drawer>

        <div class="grid-table-wrap">
            <div v-if="loading && !rows.length" class="grid-state-wrap">
                <v-grid-states type="loading"></v-grid-states>
            </div>
            <v-grid-states v-else-if="error" type="error" :message="error"></v-grid-states>
            <v-grid-states v-else-if="!rows.length" type="empty"></v-grid-states>
            <table v-else>
                <thead>
                    <tr>
                        <th v-if="isSelectable" class="grid-select-cell">
                            <input
                                ref="headerCheckbox"
                                type="checkbox"
                                aria-label="Select all selectable rows on this page"
                                :checked="allSelectedOnPage"
                                :disabled="!selectableRows.length"
                                @change="togglePage"
                            />
                        </th>
                        <th v-for="column in (config.columns || [])" :key="column.key">
                            <button
                                v-if="column.sortable"
                                type="button"
                                @click="updateSort(column.sortKey || column.key)"
                            >
                                @{{ column.label }}
                                <svg v-if="sort === (column.sortKey || column.key) && direction === 'asc'" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m18 15-6-6-6 6"/></svg>
                                <svg v-else-if="sort === (column.sortKey || column.key)" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <span v-else>@{{ column.label }}</span>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="String(row[config.rowKey || 'id'])">
                        <td v-if="isSelectable" class="grid-select-cell">
                            <input
                                type="checkbox"
                                :aria-label="`Select ${String(row.name || row[config.rowKey || 'id'])}`"
                                :checked="selectedIds.includes(row[config.rowKey || 'id'])"
                                :disabled="row.selectable !== true"
                                @change="toggleRow(row)"
                            />
                        </td>
                        <td v-for="column in (config.columns || [])" :key="column.key">
                            <slot :name="`cell-${column.cell || column.key}`" :row="row">
                                @{{ row[column.key] ?? '—' }}
                            </slot>
                        </td>
                        <td>
                            <v-row-actions
                                :row="row"
                                :actions="config.actions || []"
                                :allowed="meta.actions || {}"
                                @action="actionSelected"
                            ></v-row-actions>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div v-if="loading && rows.length" class="grid-refreshing">Refreshing…</div>
        </div>

        <v-grid-pagination
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :per-page="meta.per_page"
            :page-sizes="config.pageSizes || [10, 25, 50, 100]"
            :total="meta.total"
            :from="meta.from"
            :to="meta.to"
            @page="updatePage"
            @page-size="updatePageSize"
            @invalid-page="$emit('notify', $event)"
        ></v-grid-pagination>

        <v-confirm-dialog
            :open="!!pendingDelete"
            title="Delete this record?"
            description="This action cannot be undone."
            @cancel="pendingDelete = null"
            @confirm="confirmDelete"
        ></v-confirm-dialog>

        <v-confirm-dialog
            :open="pendingBulkDelete"
            :title="`Delete ${selectedIds.length} selected ${selectedIds.length === 1 ? 'record' : 'records'}?`"
            :description="deleteDescription"
            confirm-text="Delete selected"
            :submitting="bulkSubmitting"
            @cancel="pendingBulkDelete = false"
            @confirm="submitBulk('delete_selected', {})"
        ></v-confirm-dialog>

        <v-bulk-edit-dialog
            v-if="bulkEditOpen"
            :key="selectedIds.join(',')"
            :count="selectedIds.length"
            :fields="meta.bulk_edit_fields || {}"
            :submitting="bulkSubmitting"
            :errors="bulkErrors"
            @cancel="bulkEditOpen = false"
            @submit="submitBulk('edit_selected', $event)"
        ></v-bulk-edit-dialog>
    </section>
</script>
<script type="module">
    const dataGridComponent = {
        template: '#v-datagrid-template',
        props: {
            config: {
                type: Object,
                required: true,
                default: () => ({
                    key: 'grid',
                    endpoint: '',
                    rowKey: 'id',
                    columns: [],
                    actions: [],
                    bulkActions: [],
                    filters: [],
                    pageSizes: [10, 25, 50, 100],
                    defaultPageSize: 10,
                    defaultSort: 'id',
                    defaultDirection: 'asc',
                    selectable: false,
                    searchPlaceholder: 'Search records...'
                })
            }
        },
        emits: ['create', 'edit', 'delete', 'notify'],
        data() {
            return {
                rows: [],
                meta: {
                    current_page: 1,
                    last_page: 1,
                    per_page: this.config.defaultPageSize || 10,
                    total: 0,
                    from: null,
                    to: null,
                    filter_options: {},
                    actions: {},
                    selectable: false,
                    bulk_actions: {},
                    bulk_edit_fields: {}
                },
                search: '',
                filters: {},
                sort: this.config.defaultSort || 'id',
                direction: this.config.defaultDirection || 'asc',
                loading: false,
                error: '',
                selectedIds: [],
                filtersOpen: false,
                pendingDelete: null,
                pendingBulkDelete: false,
                bulkEditOpen: false,
                bulkSubmitting: false,
                bulkErrors: {},
                searchTimer: null
            };
        },
        computed: {
            createAction() {
                return (this.config.actions || []).find((a) => a.key === 'create');
            },
            selectableRows() {
                return this.rows.filter((r) => r.selectable === true);
            },
            selectedOnPage() {
                const key = this.config.rowKey || 'id';
                return this.selectableRows.filter((r) => this.selectedIds.includes(r[key]));
            },
            allSelectedOnPage() {
                return this.selectableRows.length > 0 && this.selectedOnPage.length === this.selectableRows.length;
            },
            someSelectedOnPage() {
                return this.selectedOnPage.length > 0 && !this.allSelectedOnPage;
            },
            isSelectable() {
                return !!(this.config.selectable && this.meta.selectable);
            },
            deleteDescription() {
                const count = this.selectedIds.length;
                return `Delete ${count} selected ${count === 1 ? 'record' : 'records'}? This action cannot be undone.`;
            },
            activeFilters() {
                return Object.entries(this.filters).map(([k, val]) => {
                    const cfg = (this.config.filters || []).find((f) => f.key === k);
                    const opt = this.meta.filter_options && this.meta.filter_options[k]
                        ? this.meta.filter_options[k].find((o) => String(o.value) === String(val))
                        : null;
                    return {
                        key: k,
                        value: val,
                        label: cfg ? cfg.label : k,
                        option: opt ? opt.label : String(val)
                    };
                });
            }
        },
        watch: {
            someSelectedOnPage(val) {
                if (this.$refs.headerCheckbox) {
                    this.$refs.headerCheckbox.indeterminate = val;
                }
            }
        },
        mounted() {
            this.load();
        },
        methods: {
            async load() {
                this.loading = true;
                this.error = '';
                const params = new URLSearchParams({
                    search: this.search,
                    sort: this.sort,
                    direction: this.direction,
                    page: String(this.meta.current_page),
                    per_page: String(this.meta.per_page)
                });
                Object.entries(this.filters).forEach(([k, v]) => {
                    if (v !== '' && v !== null && v !== undefined) {
                        params.set(`filter[${k}]`, String(v));
                    }
                });

                try {
                    const endpoint = this.config.endpoint || `/api/grids/${this.config.key}`;
                    const res = await (this.$axios ? this.$axios.get(`${endpoint}?${params}`) : fetch(`${endpoint}?${params}`).then((r) => r.json()));
                    const data = res.data ? res : { data: res, meta: {} };
                    this.rows = data.data || [];
                    this.meta = {
                        ...this.meta,
                        ...(data.meta || {})
                    };
                } catch (err) {
                    this.error = err.message || 'Failed to load records.';
                } finally {
                    this.loading = false;
                }
            },
            updateSearch(query) {
                this.search = query;
                clearTimeout(this.searchTimer);
                this.searchTimer = setTimeout(() => {
                    this.meta.current_page = 1;
                    this.clearSelection();
                    this.load();
                }, 300);
            },
            clearFilter(key) {
                const next = { ...this.filters };
                delete next[key];
                this.filters = next;
                this.meta.current_page = 1;
                this.clearSelection();
                this.load();
            },
            applyFilterDraft(draft) {
                this.filters = { ...draft };
                this.filtersOpen = false;
                this.meta.current_page = 1;
                this.clearSelection();
                this.load();
            },
            updateSort(sortKey) {
                if (this.sort === sortKey) {
                    this.direction = this.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    this.sort = sortKey;
                    this.direction = 'asc';
                }
                this.clearSelection();
                this.load();
            },
            updatePage(page) {
                this.meta.current_page = page;
                this.clearSelection();
                this.load();
            },
            updatePageSize(size) {
                this.meta.per_page = size;
                this.meta.current_page = 1;
                this.clearSelection();
                this.load();
            },
            clearSelection() {
                this.selectedIds = [];
            },
            toggleRow(row) {
                const key = this.config.rowKey || 'id';
                const id = row[key];
                if (this.selectedIds.includes(id)) {
                    this.selectedIds = this.selectedIds.filter((item) => item !== id);
                } else {
                    this.selectedIds = [...this.selectedIds, id];
                }
            },
            togglePage() {
                const key = this.config.rowKey || 'id';
                const pageIds = this.selectableRows.map((r) => r[key]);
                if (this.allSelectedOnPage) {
                    this.selectedIds = this.selectedIds.filter((id) => !pageIds.includes(id));
                } else {
                    this.selectedIds = [...new Set([...this.selectedIds, ...pageIds])];
                }
            },
            actionSelected(action, row) {
                if (action.key === 'delete') {
                    this.pendingDelete = row;
                } else if (action.key === 'edit') {
                    this.$emit('edit', row);
                } else {
                    this.$emit('action', action, row);
                }
            },
            confirmDelete() {
                if (this.pendingDelete) {
                    this.$emit('delete', this.pendingDelete);
                }
                this.pendingDelete = null;
            },
            bulkAction(key) {
                if (key === 'edit_selected') {
                    this.bulkErrors = {};
                    this.bulkEditOpen = true;
                } else if (key === 'delete_selected') {
                    this.pendingBulkDelete = true;
                } else {
                    this.submitBulk(key, {});
                }
            },
            async submitBulk(action, changes) {
                if (!this.selectedIds.length || this.bulkSubmitting) return;
                this.bulkSubmitting = true;
                this.bulkErrors = {};
                try {
                    const endpoint = `/api/grids/${this.config.key}/bulk/${action}`;
                    const payload = {
                        ids: this.selectedIds,
                        ...(action === 'edit_selected' ? { changes } : {})
                    };
                    const res = await (this.$axios
                        ? this.$axios.post(endpoint, payload)
                        : fetch(endpoint, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        }).then((r) => r.json()));

                    this.clearSelection();
                    this.bulkEditOpen = false;
                    this.pendingBulkDelete = false;
                    this.$emit('notify', res.message || 'Action executed successfully');
                    await this.load();
                } catch (caught) {
                    this.bulkErrors = Object.fromEntries(
                        Object.entries(caught.errors || {}).map(([k, msgs]) => [k, msgs[0]])
                    );
                    this.$emit('notify', caught.message || 'Bulk action failed');
                } finally {
                    this.bulkSubmitting = false;
                }
            }
        }
    };

    app.component('v-datagrid', dataGridComponent);
    app.component('data-grid', dataGridComponent);
</script>
@endpushOnce
