@props([
    'src' => null,
    'config' => null,
    'columns' => null,
    'actions' => null,
    'massActions' => null,
    'selectable' => null,
    'pageSizes' => [10, 25, 50, 100],
    'defaultPerPage' => 10,
    'searchPlaceholder' => 'Search records...',
    'rowKey' => 'id',
])

@php
    $initialConfig = array_filter([
        'src' => $src ?? ($config['endpoint'] ?? null),
        'columns' => $columns ?? ($config['columns'] ?? null),
        'actions' => $actions ?? ($config['actions'] ?? null),
        'massActions' => $massActions ?? ($config['bulkActions'] ?? null),
        'selectable' => $selectable ?? ($config['selectable'] ?? null),
        'pageSizes' => $pageSizes ?? ($config['pageSizes'] ?? [10, 25, 50, 100]),
        'defaultPerPage' => $defaultPerPage ?? ($config['defaultPageSize'] ?? 10),
        'searchPlaceholder' => $searchPlaceholder ?? ($config['searchPlaceholder'] ?? 'Search records...'),
        'rowKey' => $rowKey ?? ($config['rowKey'] ?? 'id'),
    ], fn ($val) => !is_null($val));
@endphp

<!-- Ensure custom single-select, multi-select, badge, and action components are registered -->
<div style="display:none">
    <x-core::dropdown.select />
    <x-core::dropdown.multiselect />
    <x-core::badge />
    <x-core::button.action />
    <x-core::datagrid.row-actions />
</div>

<v-datagrid
    :initial-config='@json($initialConfig, JSON_HEX_APOS)'
    {{ $attributes }}
>
    {{ $slot }}
</v-datagrid>

@pushOnce('scripts')
<script type="text/x-template" id="v-datagrid-template">
    <div class="datagrid-wrapper space-y-3">
        <!-- Notification Flash Banner -->
        <transition name="fade">
            <div
                v-if="flashMessage"
                class="p-2.5 px-3 rounded-lg border flex items-center justify-between text-xs font-semibold shadow-xs transition-all"
                :class="flashType === 'error' ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-950/40 dark:text-red-300 dark:border-red-900' : 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-900'"
            >
                <div class="flex items-center gap-2">
                    <svg v-if="flashType === 'error'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>@{{ flashMessage }}</span>
                </div>
                <button type="button" @click="flashMessage = null" class="opacity-70 hover:opacity-100 text-sm font-bold">&times;</button>
            </div>
        </transition>

        <!-- Top Header & Actions Slot -->
        <div v-if="$slots.title || $slots.action" class="flex flex-wrap items-center justify-between gap-3 pb-0.5">
            <div>
                <slot name="title"></slot>
            </div>
            <div class="flex items-center gap-2">
                <slot name="action"></slot>
            </div>
        </div>

        <!-- Main Card Wrapper (Compact Enterprise Styling) -->
        <div class="rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs overflow-hidden transition-colors">
            
            <!-- Toolbar Area: Compact Density -->
            <div class="px-3 py-2 border-b border-[var(--border)] bg-[var(--page)] flex flex-wrap items-center justify-between gap-2.5">
                
                <!-- Left: Compact Search Bar -->
                <div class="flex items-center gap-2 flex-1 min-w-[220px] max-w-sm">
                    <div class="relative w-full">
                        <input
                            type="text"
                            :value="search"
                            @input="onSearchInput"
                            :placeholder="searchPlaceholder"
                            class="w-full h-8 pl-8 pr-7 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                        />
                        <svg class="absolute left-2.5 top-2.5 text-[var(--muted)] pointer-events-none" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <button
                            v-if="search"
                            type="button"
                            @click="clearSearch"
                            class="absolute right-2 top-2 text-[var(--muted)] hover:text-[var(--text)] text-xs font-bold"
                            title="Clear search"
                        >&times;</button>
                    </div>
                </div>

                <!-- Right Toolbar Actions -->
                <div class="flex items-center gap-2 flex-wrap">
                    
                    <!-- Filters Button (Opens Right-Side Slide-Over Drawer) -->
                    <div v-if="hasAvailableFilters">
                        <button
                            type="button"
                            @click="openFilterDrawer"
                            class="h-8 px-2.5 rounded-lg border text-xs font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer select-none"
                            :class="activeFiltersCount > 0 ? 'border-[var(--teal)] bg-[var(--soft)] text-[var(--teal)] font-semibold' : 'border-[var(--border)] bg-[var(--card)] text-[var(--text)] hover:border-[var(--teal)]'"
                        >
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            <span>Filter</span>
                            <span v-if="activeFiltersCount > 0" class="px-1.5 py-0.2 rounded-full bg-[var(--teal)] text-white text-[10px] grid place-items-center font-bold">
                                @{{ activeFiltersCount }}
                            </span>
                        </button>
                    </div>

                    <!-- Per Page Custom Dropdown Single-Select -->
                    <div class="flex items-center gap-1.5 text-xs text-[var(--muted)]">
                        <span class="text-[11px] font-medium">Show</span>
                        <div class="w-18">
                            <v-dropdown-select
                                :options="pageSizesDropdownOptions"
                                :model-value="meta.per_page"
                                @change="changePerPage"
                                size="sm"
                                :searchable="false"
                            ></v-dropdown-select>
                        </div>
                        <span class="text-[11px] font-medium">rows</span>
                    </div>

                    <!-- Refresh Button -->
                    <button
                        type="button"
                        @click="refresh"
                        :disabled="isLoading"
                        class="h-8 w-8 rounded-lg border border-[var(--border)] bg-[var(--card)] text-[var(--muted)] hover:text-[var(--text)] hover:border-[var(--teal)] grid place-items-center transition-colors disabled:opacity-50 cursor-pointer"
                        title="Refresh data"
                    >
                        <svg :class="{ 'animate-spin': isLoading }" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    </button>
                </div>
            </div>

            <!-- Active Filter Chips Bar (Compact) -->
            <div v-if="activeFiltersList.length > 0" class="px-3 py-1.5 bg-[var(--page)] border-b border-[var(--border)] flex flex-wrap items-center gap-1.5">
                <span class="text-[10px] font-semibold text-[var(--muted)] uppercase tracking-wider">Active:</span>
                <span
                    v-for="chip in activeFiltersList"
                    :key="chip.key"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-[var(--soft)] text-[var(--teal)] border border-[var(--teal)]/20 shadow-xs"
                >
                    <span>@{{ chip.label }}: <strong>@{{ chip.valueLabel }}</strong></span>
                    <button type="button" @click="removeFilter(chip.key)" class="hover:opacity-75 font-bold text-xs leading-none cursor-pointer">&times;</button>
                </span>
                <button
                    type="button"
                    @click="clearAllFilters"
                    class="text-[10px] text-[var(--muted)] hover:text-red-600 underline ml-1 cursor-pointer font-semibold"
                >
                    Clear all
                </button>
            </div>

            <!-- Multi-Select Bulk Actions Banner (ONLY appears when multi-select rows are checked) -->
            <transition name="slide-down">
                <div
                    v-if="isSelectable && selectedIds.length > 0"
                    class="px-3 py-1.5 bg-sky-50 dark:bg-sky-950/40 border-b border-sky-200 dark:border-sky-900 text-sky-900 dark:text-sky-200 flex flex-wrap items-center justify-between gap-2 text-xs"
                >
                    <div class="flex items-center gap-2.5">
                        <span class="font-bold text-xs">
                            @{{ selectedIds.length }} @{{ selectedIds.length === 1 ? 'record' : 'records' }} selected
                        </span>
                        <button
                            v-if="!isEntireQuerySelected && meta.total > records.length"
                            type="button"
                            @click="isEntireQuerySelected = true"
                            class="text-sky-700 dark:text-sky-300 underline font-semibold text-xs cursor-pointer"
                        >
                            Select all @{{ meta.total }} records
                        </button>
                        <span v-if="isEntireQuerySelected" class="italic text-[10px]">
                            (All @{{ meta.total }} records selected)
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Custom Bulk Actions Dropdown -->
                        <div class="w-40" v-if="availableBulkActions.length > 0">
                            <v-dropdown-select
                                :options="bulkActionDropdownOptions"
                                model-value=""
                                placeholder="Bulk Actions ▾"
                                @change="onBulkActionSelect"
                                size="sm"
                                :searchable="false"
                            ></v-dropdown-select>
                        </div>

                        <!-- Clear Selection Button -->
                        <button
                            type="button"
                            @click="clearSelection"
                            class="px-2 py-1 h-8 rounded-lg border border-sky-300 dark:border-sky-800 hover:bg-sky-100 dark:hover:bg-sky-900/50 text-xs font-medium cursor-pointer"
                        >
                            Clear Selection
                        </button>
                    </div>
                </div>
            </transition>

            <!-- Table Area (Compact Enterprise Density) -->
            <div class="overflow-x-auto relative">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[var(--border)] bg-[var(--page)] text-[var(--muted)] font-semibold text-[11px] uppercase tracking-wider select-none">
                            
                            <!-- Multi-select Master Checkbox: ONLY visible if isSelectable is true -->
                            <th v-if="isSelectable" class="w-9 py-2 px-2.5 text-center">
                                <input
                                    ref="masterCheckbox"
                                    type="checkbox"
                                    :checked="isAllOnPageSelected"
                                    @change="toggleSelectAllPage"
                                    class="w-3.5 h-3.5 rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)] cursor-pointer"
                                    aria-label="Select all on this page"
                                />
                            </th>

                            <!-- Data Columns -->
                            <th
                                v-for="col in resolvedColumns"
                                :key="col.index || col.key"
                                class="py-2 px-3 text-[11px]"
                                :class="[
                                    col.align === 'center' ? 'text-center' : (col.align === 'right' ? 'text-right' : 'text-left'),
                                    col.sortable ? 'cursor-pointer hover:text-[var(--text)]' : ''
                                ]"
                                @click="col.sortable && toggleSort(col.index || col.key)"
                            >
                                <div
                                    class="inline-flex items-center gap-1"
                                    :class="col.align === 'center' ? 'justify-center' : (col.align === 'right' ? 'justify-end' : 'justify-start')"
                                >
                                    <span>@{{ col.label }}</span>
                                    <span v-if="col.sortable" class="text-[10px]">
                                        <svg v-if="currentSortColumn === (col.index || col.key) && currentSortDirection === 'asc'" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m18 15-6-6-6 6"/></svg>
                                        <svg v-else-if="currentSortColumn === (col.index || col.key) && currentSortDirection === 'desc'" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                                        <svg v-else class="opacity-25" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                                    </span>
                                </div>
                            </th>

                            <!-- Actions Column -->
                            <th class="py-2 px-3 text-right text-[11px]">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-[var(--border)]">
                        <!-- Loading Shimmer State -->
                        <template v-if="isLoading">
                            <tr v-for="n in (meta.per_page || 5)" :key="'shimmer-' + n" class="animate-pulse">
                                <td v-if="isSelectable" class="w-9 py-2 px-2.5 text-center">
                                    <div class="w-3.5 h-3.5 rounded bg-[var(--soft)] mx-auto"></div>
                                </td>
                                <td v-for="col in resolvedColumns" :key="'shimmer-col-' + (col.index || col.key)" class="py-2 px-3">
                                    <div class="h-3 rounded bg-[var(--soft)] w-3/4"></div>
                                </td>
                                <td class="py-2 px-3 text-right">
                                    <div class="h-3 rounded bg-[var(--soft)] w-10 ml-auto"></div>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <tr v-else-if="records.length === 0">
                            <td :colspan="resolvedColumns.length + (isSelectable ? 2 : 1)" class="py-10 text-center text-[var(--muted)]">
                                <slot name="empty">
                                    <div class="flex flex-col items-center justify-center gap-1.5 max-w-sm mx-auto">
                                        <svg class="text-[var(--muted)] opacity-40" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                        <p class="text-xs font-semibold text-[var(--text)] mt-1">No matching records found</p>
                                        <p class="text-[11px] text-[var(--muted)]">Try adjusting search criteria or clearing filters.</p>
                                        <button
                                            v-if="search || activeFiltersCount > 0"
                                            type="button"
                                            @click="resetAll"
                                            class="mt-1 px-2.5 py-1 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] hover:border-[var(--teal)] transition-colors cursor-pointer"
                                        >
                                            Reset search & filters
                                        </button>
                                    </div>
                                </slot>
                            </td>
                        </tr>

                        <!-- Records Rows (Compact py-2 px-3) -->
                        <tr
                            v-else
                            v-for="row in records"
                            :key="String(row[rowKeyProperty])"
                            class="hover:bg-[var(--soft)]/50 transition-colors"
                            :class="{ 'bg-sky-50/50 dark:bg-sky-950/20': isSelectable && isRowSelected(row) }"
                        >
                            <!-- Row Checkbox: ONLY visible if isSelectable is true -->
                            <td v-if="isSelectable" class="w-9 py-2 px-2.5 text-center" @click.stop>
                                <input
                                    type="checkbox"
                                    :checked="isRowSelected(row)"
                                    :disabled="row.selectable === false"
                                    @change="toggleRowSelection(row)"
                                    class="w-3.5 h-3.5 rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)] cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
                                />
                            </td>

                            <!-- Column Cells -->
                            <td
                                v-for="col in resolvedColumns"
                                :key="col.index || col.key"
                                class="py-2 px-3 text-xs text-[var(--text)]"
                                :class="col.align === 'center' ? 'text-center' : (col.align === 'right' ? 'text-right' : 'text-left')"
                            >
                                <slot
                                    :name="'cell-' + (col.index || col.key)"
                                    :row="row"
                                    :value="row[col.index || col.key]"
                                >
                                    <!-- Smart Default Fallback Formatters via modular v-status-badge -->
                                    <template v-if="typeof row[col.index || col.key] === 'boolean'">
                                        <v-status-badge :value="row[col.index || col.key]" type="boolean" size="sm"></v-status-badge>
                                    </template>
                                    <template v-else-if="(col.index || col.key) === 'status' || (col.index || col.key) === 'is_active'">
                                        <v-status-badge :value="row[col.index || col.key]" type="status" size="sm"></v-status-badge>
                                    </template>
                                    <template v-else>
                                        @{{ row[col.index || col.key] ?? '—' }}
                                    </template>
                                </slot>
                            </td>

                            <!-- Row Actions: Modular v-row-actions with Unified Action Buttons -->
                            <td class="py-2 px-3 text-right" @click.stop>
                                <slot name="row-actions" :row="row">
                                    <v-row-actions
                                        :row="row"
                                        :actions="rowActionsConfig"
                                        :edit-url="row.edit_url || getEditUrl(row)"
                                        @delete="confirmDeleteRow"
                                        @toggle-status="confirmToggleRowStatus"
                                    ></v-row-actions>
                                </slot>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Footer: Compact -->
            <div class="px-3 py-2 border-t border-[var(--border)] bg-[var(--page)] flex flex-wrap items-center justify-between gap-3 text-xs text-[var(--muted)]">
                <!-- Showing X to Y of Total -->
                <div>
                    <template v-if="meta.total > 0">
                        Showing <strong class="text-[var(--text)]">@{{ meta.from }}</strong> to <strong class="text-[var(--text)]">@{{ meta.to }}</strong> of <strong class="text-[var(--text)]">@{{ meta.total }}</strong>
                    </template>
                    <template v-else>
                        0 records
                    </template>
                </div>

                <!-- Navigation Controls -->
                <div class="flex items-center gap-1" v-if="meta.last_page > 1">
                    <!-- First Page -->
                    <button
                        type="button"
                        :disabled="meta.current_page <= 1 || isLoading"
                        @click="changePage(1)"
                        class="px-2 py-0.5 rounded border border-[var(--border)] bg-[var(--card)] hover:bg-[var(--soft)] disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer text-xs"
                        title="First page"
                    >&laquo;</button>

                    <!-- Prev Page -->
                    <button
                        type="button"
                        :disabled="meta.current_page <= 1 || isLoading"
                        @click="changePage(meta.current_page - 1)"
                        class="px-2 py-0.5 rounded border border-[var(--border)] bg-[var(--card)] hover:bg-[var(--soft)] disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer text-xs"
                        title="Previous page"
                    >Prev</button>

                    <!-- Page Indicator -->
                    <span class="px-1.5 font-medium text-[var(--text)] text-xs">
                        @{{ meta.current_page }} / @{{ meta.last_page }}
                    </span>

                    <!-- Next Page -->
                    <button
                        type="button"
                        :disabled="meta.current_page >= meta.last_page || isLoading"
                        @click="changePage(meta.current_page + 1)"
                        class="px-2 py-0.5 rounded border border-[var(--border)] bg-[var(--card)] hover:bg-[var(--soft)] disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer text-xs"
                        title="Next page"
                    >Next</button>

                    <!-- Last Page -->
                    <button
                        type="button"
                        :disabled="meta.current_page >= meta.last_page || isLoading"
                        @click="changePage(meta.last_page)"
                        class="px-2 py-0.5 rounded border border-[var(--border)] bg-[var(--card)] hover:bg-[var(--soft)] disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer text-xs"
                        title="Last page"
                    >&raquo;</button>
                </div>
            </div>
        </div>

        <!-- Right-Side Slide-Over Filter Drawer (Sleek Compact Enterprise Design) -->
        <teleport to="body">
            <div v-if="isFilterDrawerOpen" class="fixed inset-0 z-[1000] overflow-hidden">
                <!-- Backdrop Overlay -->
                <div
                    class="fixed inset-0 bg-black/40 backdrop-blur-xs transition-opacity"
                    @click="closeFilterDrawer"
                ></div>

                <div class="fixed inset-y-0 right-0 max-w-full flex">
                    <!-- Drawer Panel: Compact Width w-80 -->
                    <div class="w-80 max-w-[calc(100vw-1rem)] bg-[var(--card)] border-l border-[var(--border)] shadow-2xl flex flex-col z-10 text-[var(--text)] transform transition-transform duration-300 ease-in-out">
                        
                        <!-- Drawer Header -->
                        <div class="p-3 px-4 border-b border-[var(--border)] flex items-center justify-between bg-[var(--page)]">
                            <div class="flex items-center gap-2">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                                <h2 class="text-xs font-bold uppercase tracking-wider text-[var(--text)]">Filters</h2>
                                <span v-if="activeFiltersCount > 0" class="px-1.5 py-0.2 rounded-full bg-[var(--teal)] text-white text-[10px] font-bold">
                                    @{{ activeFiltersCount }}
                                </span>
                            </div>
                            <button
                                type="button"
                                @click="closeFilterDrawer"
                                class="w-7 h-7 rounded-lg hover:bg-[var(--soft)] grid place-items-center text-base font-bold text-[var(--muted)] hover:text-[var(--text)] cursor-pointer"
                                title="Close filters"
                            >&times;</button>
                        </div>

                        <!-- Drawer Content / Fields List: Compact Cards -->
                        <div class="p-3.5 flex-1 overflow-y-auto space-y-3">
                            <div
                                v-for="(options, key) in filterOptions"
                                :key="key"
                                class="p-2.5 rounded-lg border border-[var(--border)] bg-[var(--page)] space-y-1.5"
                            >
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-bold text-[var(--text)] capitalize">
                                        @{{ formatFilterLabel(key) }}
                                    </label>
                                    <span v-if="filterDraft[key]" class="w-2 h-2 rounded-full bg-[var(--teal)]" title="Filter active"></span>
                                </div>

                                <!-- Custom Dropdown Select for Filter field -->
                                <v-dropdown-select
                                    :options="formatFilterOptions(options)"
                                    :model-value="filterDraft[key] ?? ''"
                                    @change="val => filterDraft[key] = val"
                                    placeholder="All (No filter)"
                                    size="sm"
                                    :clearable="true"
                                ></v-dropdown-select>
                            </div>
                        </div>

                        <!-- Drawer Footer -->
                        <div class="p-2.5 px-3.5 border-t border-[var(--border)] bg-[var(--page)] flex items-center justify-between gap-2">
                            <button
                                type="button"
                                @click="resetFilterDraft"
                                class="px-3 py-1.5 rounded-lg border border-[var(--border)] bg-[var(--card)] hover:bg-[var(--soft)] text-xs font-semibold text-[var(--muted)] hover:text-[var(--text)] cursor-pointer"
                            >
                                Reset All
                            </button>
                            <button
                                type="button"
                                @click="applyFilters"
                                class="px-4 py-1.5 rounded-lg bg-[var(--teal)] text-white text-xs font-bold shadow hover:opacity-95 cursor-pointer"
                            >
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </teleport>

        <!-- Confirmation Dialog Modal -->
        <teleport to="body">
            <div
                v-if="confirmDialog.open"
                class="fixed inset-0 z-[1001] flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
                @click.self="!confirmDialog.isWorking && (confirmDialog.open = false)"
            >
                <div class="w-full max-w-sm rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-2xl p-5 text-[var(--text)] animate-scale-in">
                    <h3 class="text-sm font-bold text-[var(--text)]">@{{ confirmDialog.title }}</h3>
                    <p class="text-xs text-[var(--muted)] mt-2 leading-relaxed">@{{ confirmDialog.message }}</p>

                    <div class="mt-5 flex items-center justify-end gap-2.5">
                        <button
                            type="button"
                            :disabled="confirmDialog.isWorking"
                            @click="confirmDialog.open = false"
                            class="px-3 py-1.5 rounded-lg border border-[var(--border)] text-xs font-semibold hover:bg-[var(--soft)] cursor-pointer"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            :disabled="confirmDialog.isWorking"
                            @click="executeConfirmDialog"
                            class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white shadow cursor-pointer"
                            :class="confirmDialog.type === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-[var(--teal)] hover:opacity-90'"
                        >
                            @{{ confirmDialog.isWorking ? 'Processing…' : (confirmDialog.confirmText || 'Confirm') }}
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</script>

<script>
(function() {
    const DataGridComponent = {
        template: '#v-datagrid-template',
        props: {
            initialConfig: {
                type: Object,
                default: () => ({})
            }
        },
        emits: ['record-deleted', 'bulk-action-completed', 'row-click', 'selection-change'],
        data() {
            return {
                endpoint: this.initialConfig.src || '',
                rowKeyProperty: this.initialConfig.rowKey || 'id',
                searchPlaceholder: this.initialConfig.searchPlaceholder || 'Search records...',
                availablePageSizes: this.initialConfig.pageSizes || [10, 25, 50, 100],
                records: [],
                meta: {
                    current_page: 1,
                    last_page: 1,
                    per_page: this.initialConfig.defaultPerPage || 10,
                    total: 0,
                    from: null,
                    to: null,
                    filter_options: {},
                    columns: null,
                    actions: {},
                    selectable: false,
                    bulk_actions: {},
                    bulk_action_definitions: []
                },
                search: '',
                searchDebounceTimer: null,
                currentSortColumn: '',
                currentSortDirection: 'asc',
                appliedFilters: {},
                filterDraft: {},
                isFilterDrawerOpen: false,
                selectedIds: [],
                isEntireQuerySelected: false,
                isLoading: false,
                flashMessage: null,
                flashType: 'success',
                confirmDialog: {
                    open: false,
                    title: '',
                    message: '',
                    confirmText: 'Confirm',
                    type: 'danger',
                    isWorking: false,
                    handler: null
                }
            };
        },
        computed: {
            resolvedColumns() {
                if (this.initialConfig.columns && this.initialConfig.columns.length > 0) {
                    return this.initialConfig.columns;
                }
                if (this.meta.columns && this.meta.columns.length > 0) {
                    return this.meta.columns;
                }
                if (this.records.length > 0) {
                    return Object.keys(this.records[0])
                        .filter(key => !['id', 'selectable', 'edit_url', 'delete_url', '_actions'].includes(key))
                        .map(key => ({
                            index: key,
                            label: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                            sortable: true
                        }));
                }
                return [];
            },
            availableBulkActions() {
                if (Array.isArray(this.initialConfig.massActions) && this.initialConfig.massActions.length > 0) {
                    return this.initialConfig.massActions;
                }
                if (Array.isArray(this.meta.bulk_action_definitions) && this.meta.bulk_action_definitions.length > 0) {
                    return this.meta.bulk_action_definitions;
                }
                const bulk = this.meta.bulk_actions || {};
                const actions = [];
                for (const [actKey, allowed] of Object.entries(bulk)) {
                    if (allowed) {
                        actions.push({
                            key: actKey,
                            label: actKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
                        });
                    }
                }
                return actions;
            },
            pageSizesDropdownOptions() {
                return (this.availablePageSizes || [10, 25, 50, 100]).map(s => ({
                    value: s,
                    label: String(s)
                }));
            },
            bulkActionDropdownOptions() {
                return this.availableBulkActions.map(a => ({
                    value: a.key,
                    label: a.label
                }));
            },
            rowActionsConfig() {
                return {
                    edit: this.hasAction('edit'),
                    delete: this.hasAction('delete'),
                    activate: this.hasAction('activate'),
                    deactivate: this.hasAction('deactivate'),
                    toggle_status: this.hasAction('toggle_status')
                };
            },
            /**
             * Multi-select Checkbox Visibility:
             * ONLY true if bulk actions actually exist AND are permitted for this user,
             * or if selectable is explicitly enabled as a prop.
             */
            isSelectable() {
                if (this.initialConfig.selectable === true) {
                    return true;
                }
                if (this.initialConfig.selectable === false) {
                    return false;
                }
                return Boolean(this.meta.selectable) && this.availableBulkActions.length > 0;
            },
            filterOptions() {
                return this.meta.filter_options || {};
            },
            hasAvailableFilters() {
                return Object.keys(this.filterOptions).length > 0;
            },
            activeFiltersCount() {
                return Object.values(this.appliedFilters).filter(v => v !== '' && v !== null && v !== undefined).length;
            },
            activeFiltersList() {
                const list = [];
                for (const [key, val] of Object.entries(this.appliedFilters)) {
                    if (val === '' || val === null || val === undefined) continue;
                    let valLabel = String(val);
                    const opts = this.filterOptions[key];
                    if (Array.isArray(opts)) {
                        const match = opts.find(o => String(o.value) === String(val));
                        if (match) valLabel = match.label;
                    }
                    list.push({
                        key,
                        label: this.formatFilterLabel(key),
                        valueLabel: valLabel
                    });
                }
                return list;
            },
            isAllOnPageSelected() {
                if (this.records.length === 0) return false;
                const selectableOnPage = this.records.filter(r => r.selectable !== false);
                if (selectableOnPage.length === 0) return false;
                return selectableOnPage.every(r => this.selectedIds.includes(r[this.rowKeyProperty]));
            }
        },
        watch: {
            selectedIds(newVal) {
                this.updateMasterCheckbox();
                this.$emit('selection-change', newVal);
            },
            records() {
                this.updateMasterCheckbox();
            }
        },
        mounted() {
            this.fetchData(1);
        },
        methods: {
            openFilterDrawer() {
                this.filterDraft = { ...this.appliedFilters };
                this.isFilterDrawerOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeFilterDrawer() {
                this.isFilterDrawerOpen = false;
                document.body.style.overflow = '';
            },
            formatFilterOptions(options) {
                if (!options) return [];
                if (Array.isArray(options)) {
                    return options.map(opt => ({
                        value: String(opt.value),
                        label: opt.label || String(opt.value)
                    }));
                }
                if (typeof options === 'object') {
                    return Object.entries(options).map(([val, lbl]) => ({
                        value: String(val),
                        label: String(lbl)
                    }));
                }
                return [];
            },
            updateMasterCheckbox() {
                this.$nextTick(() => {
                    const el = this.$refs.masterCheckbox;
                    if (!el) return;
                    const count = this.selectedIds.length;
                    const totalOnPage = this.records.filter(r => r.selectable !== false).length;
                    el.indeterminate = count > 0 && count < totalOnPage;
                });
            },
            formatFilterLabel(key) {
                return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            },
            hasAction(actionKey) {
                if (this.initialConfig.actions && this.initialConfig.actions[actionKey] !== undefined) {
                    return Boolean(this.initialConfig.actions[actionKey]);
                }
                if (this.meta.actions && this.meta.actions[actionKey] !== undefined) {
                    return Boolean(this.meta.actions[actionKey]);
                }
                return true;
            },
            getEditUrl(row) {
                const path = window.location.pathname.replace(/\/+$/, '');
                return `${path}/${row[this.rowKeyProperty]}/edit`;
            },
            onSearchInput(e) {
                this.search = e.target.value;
                clearTimeout(this.searchDebounceTimer);
                this.searchDebounceTimer = setTimeout(() => {
                    this.fetchData(1);
                }, 300);
            },
            clearSearch() {
                this.search = '';
                this.fetchData(1);
            },
            toggleSort(columnKey) {
                if (this.currentSortColumn === columnKey) {
                    this.currentSortDirection = this.currentSortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    this.currentSortColumn = columnKey;
                    this.currentSortDirection = 'asc';
                }
                this.fetchData(1);
            },
            changePerPage(size) {
                this.meta.per_page = Number(size);
                this.fetchData(1);
            },
            changePage(page) {
                if (page < 1 || page > this.meta.last_page) return;
                this.fetchData(page);
            },
            applyFilters() {
                this.appliedFilters = { ...this.filterDraft };
                this.closeFilterDrawer();
                this.fetchData(1);
            },
            removeFilter(key) {
                delete this.appliedFilters[key];
                delete this.filterDraft[key];
                this.fetchData(1);
            },
            resetFilterDraft() {
                this.filterDraft = {};
                this.appliedFilters = {};
                this.closeFilterDrawer();
                this.fetchData(1);
            },
            clearAllFilters() {
                this.appliedFilters = {};
                this.filterDraft = {};
                this.fetchData(1);
            },
            resetAll() {
                this.search = '';
                this.appliedFilters = {};
                this.filterDraft = {};
                this.fetchData(1);
            },
            refresh() {
                this.fetchData(this.meta.current_page);
            },
            isRowSelected(row) {
                return this.selectedIds.includes(row[this.rowKeyProperty]);
            },
            toggleRowSelection(row) {
                const id = row[this.rowKeyProperty];
                if (this.selectedIds.includes(id)) {
                    this.selectedIds = this.selectedIds.filter(item => item !== id);
                } else {
                    this.selectedIds = [...this.selectedIds, id];
                }
                this.isEntireQuerySelected = false;
            },
            toggleSelectAllPage() {
                const pageSelectable = this.records.filter(r => r.selectable !== false).map(r => r[this.rowKeyProperty]);
                if (this.isAllOnPageSelected) {
                    this.selectedIds = this.selectedIds.filter(id => !pageSelectable.includes(id));
                } else {
                    this.selectedIds = [...new Set([...this.selectedIds, ...pageSelectable])];
                }
                this.isEntireQuerySelected = false;
            },
            clearSelection() {
                this.selectedIds = [];
                this.isEntireQuerySelected = false;
            },
            async fetchData(page = 1) {
                if (!this.endpoint) return;
                this.isLoading = true;
                try {
                    const params = new URLSearchParams();
                    params.append('page', String(page));
                    params.append('per_page', String(this.meta.per_page));

                    if (this.search && this.search.trim()) {
                        params.append('search', this.search.trim());
                    }

                    if (this.currentSortColumn) {
                        params.append('sort', this.currentSortColumn);
                        params.append('direction', this.currentSortDirection);
                    }

                    for (const [key, val] of Object.entries(this.appliedFilters)) {
                        if (val !== '' && val !== null && val !== undefined) {
                            params.append(`filter[${key}]`, String(val));
                        }
                    }

                    const glue = this.endpoint.includes('?') ? '&' : '?';
                    const fullUrl = `${this.endpoint}${glue}${params.toString()}`;

                    const res = await (window.axios ? window.axios.get(fullUrl) : fetch(fullUrl).then(r => r.json()));

                    const payload = res.data ? res : { data: res, meta: {} };
                    this.records = payload.data || [];
                    this.meta = {
                        ...this.meta,
                        ...(payload.meta || {})
                    };

                    for (const key of Object.keys(this.meta.filter_options || {})) {
                        if (this.filterDraft[key] === undefined) {
                            this.filterDraft[key] = this.appliedFilters[key] || '';
                        }
                    }
                } catch (e) {
                    console.error('DataGrid fetch error:', e);
                    this.flashMessage = e.message || 'Failed to load records.';
                    this.flashType = 'error';
                } finally {
                    this.isLoading = false;
                }
            },
            isStatusActiveValue(val) {
                if (val === true || val === 1 || val === '1' || val === 'active' || val === 'enabled') {
                    return true;
                }
                return false;
            },
            isRowActive(row) {
                if (!row) return false;
                const val = row.status !== undefined ? row.status : row.is_active;
                return this.isStatusActiveValue(val);
            },
            isStatusToggleVisible(row) {
                if (!row) return false;
                if (this.hasAction('toggle_status') || this.hasAction('activate') || this.hasAction('deactivate')) {
                    return true;
                }
                return row.status !== undefined || row.is_active !== undefined;
            },
            confirmToggleRowStatus(row) {
                const currentlyActive = this.isRowActive(row);
                const targetAction = currentlyActive ? 'deactivate' : 'activate';
                const targetLabel = currentlyActive ? 'Deactivate' : 'Activate';
                const label = row.name || row.code || `ID #${row[this.rowKeyProperty]}`;

                this.confirmDialog = {
                    open: true,
                    title: `${targetLabel} Record`,
                    message: `Are you sure you want to ${targetLabel.toLowerCase()} "${label}"?`,
                    confirmText: targetLabel,
                    type: currentlyActive ? 'danger' : 'primary',
                    isWorking: false,
                    handler: async () => {
                        const path = window.location.pathname.replace(/\/+$/, '');
                        const toggleUrl = row.toggle_status_url
                            || row._actions?.toggle_status?.url
                            || `/api${path}/${row[this.rowKeyProperty]}/toggle-status`;

                        try {
                            const res = await this.sendRequest('POST', toggleUrl, {
                                status: currentlyActive ? 'inactive' : 'active'
                            });
                            this.flashMessage = res.message || `Record "${label}" ${currentlyActive ? 'deactivated' : 'activated'} successfully.`;
                            this.flashType = 'success';
                        } catch (e) {
                            try {
                                const bulkActionKey = currentlyActive ? 'deactivate_selected' : 'activate_selected';
                                const bulkUrl = `${this.endpoint}/bulk/${bulkActionKey}`;
                                await this.sendRequest('POST', bulkUrl, {
                                    ids: [row[this.rowKeyProperty]],
                                    all_matching: false
                                });
                                this.flashMessage = `Record "${label}" ${currentlyActive ? 'deactivated' : 'activated'} successfully.`;
                                this.flashType = 'success';
                            } catch (e2) {
                                const singleUrl = `/api${path}/${row[this.rowKeyProperty]}/${targetAction}`;
                                const res = await this.sendRequest('POST', singleUrl);
                                this.flashMessage = res.message || `Record "${label}" ${currentlyActive ? 'deactivated' : 'activated'} successfully.`;
                                this.flashType = 'success';
                            }
                        }
                        this.fetchData(this.meta.current_page);
                        this.$emit('status-toggled', { row, active: !currentlyActive });
                    }
                };
            },
            confirmDeleteRow(row) {
                const label = row.name || row.code || `ID #${row[this.rowKeyProperty]}`;
                this.confirmDialog = {
                    open: true,
                    title: 'Delete Record',
                    message: `Are you sure you want to permanently delete "${label}"? This action cannot be undone.`,
                    confirmText: 'Delete Record',
                    type: 'danger',
                    isWorking: false,
                    handler: async () => {
                        const path = window.location.pathname.replace(/\/+$/, '');
                        const deleteUrl = row.delete_url
                            || row._actions?.delete?.url
                            || `/api${path}/${row[this.rowKeyProperty]}`;
                        await this.sendRequest('DELETE', deleteUrl);
                        this.flashMessage = `Record "${label}" deleted successfully.`;
                        this.flashType = 'success';
                        this.fetchData(this.meta.current_page);
                        this.$emit('record-deleted', row);
                    }
                };
            },
            onBulkActionSelect(actionKey) {
                if (!actionKey || this.selectedIds.length === 0) return;
                const actionObj = this.availableBulkActions.find(a => a.key === actionKey);
                const actionLabel = actionObj ? actionObj.label : actionKey.replace(/_/g, ' ');
                const isDestructive = actionKey.includes('delete') || actionKey.includes('deactivate');

                this.confirmDialog = {
                    open: true,
                    title: `Execute ${actionLabel}`,
                    message: `Are you sure you want to execute "${actionLabel}" on ${this.selectedIds.length} selected record(s)?`,
                    confirmText: actionLabel,
                    type: isDestructive ? 'danger' : 'primary',
                    isWorking: false,
                    handler: async () => {
                        const url = actionObj?.url || `${this.endpoint}/bulk/${actionKey}`;
                        const payload = {
                            ids: this.selectedIds,
                            all_matching: this.isEntireQuerySelected
                        };
                        const res = await this.sendRequest('POST', url, payload);
                        this.flashMessage = res.message || `Bulk action "${actionLabel}" completed successfully.`;
                        this.flashType = 'success';
                        this.clearSelection();
                        this.fetchData(this.meta.current_page);
                        this.$emit('bulk-action-completed', { action: actionKey });
                    }
                };
            },
            async executeConfirmDialog() {
                if (!this.confirmDialog.handler) return;
                this.confirmDialog.isWorking = true;
                try {
                    await this.confirmDialog.handler();
                    this.confirmDialog.open = false;
                } catch (e) {
                    this.flashMessage = e.message || 'Operation failed.';
                    this.flashType = 'error';
                } finally {
                    this.confirmDialog.isWorking = false;
                }
            },
            async sendRequest(method, url, data = null) {
                if (window.axios) {
                    if (method === 'DELETE') return (await window.axios.delete(url)).data;
                    if (method === 'POST') return (await window.axios.post(url, data)).data;
                    if (method === 'PUT') return (await window.axios.put(url, data)).data;
                }
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: data ? JSON.stringify(data) : undefined
                });
                if (!res.ok) {
                    const err = await res.json().catch(() => ({ message: 'Request failed.' }));
                    throw new Error(err.message || 'Request failed.');
                }
                if (res.status === 204) {
                    return {};
                }
                return await res.json().catch(() => ({}));
            }
        }
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-datagrid', DataGridComponent);
        window.registerVueComponent('data-grid', DataGridComponent);
    } else if (window.app && typeof window.app.component === 'function') {
        window.app.component('v-datagrid', DataGridComponent);
        window.app.component('data-grid', DataGridComponent);
    }
})();
</script>
@endpushOnce
