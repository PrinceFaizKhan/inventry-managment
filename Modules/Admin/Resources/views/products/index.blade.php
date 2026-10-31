@extends('admin::layouts.admin')

@section('title', 'Products')
@section('crumb', 'Products')

@section('content')
<div class="products-page space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-[var(--border)]">
        <div>
            <p class="eyebrow">Catalog / Products</p>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">Products</h1>
            <p class="page-copy text-sm text-[var(--muted)] mt-1">Manage simple and configurable catalog products, variant hierarchies, and attributes.</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <button
                type="button"
                onclick="window.dispatchEvent(new CustomEvent('ht-open-create-product'))"
                class="button button--primary inline-flex items-center justify-center gap-2 cursor-pointer shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-transform text-xs w-full sm:w-auto"
                title="Create New Product"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Create Product</span>
            </button>
        </div>
    </div>

    <!-- Products DataGrid -->
    <x-admin::datagrid
        src="/api/grids/products"
        search-placeholder="Search by SKU or Family..."
    >
        <template #cell-sku="{ row }">
            <a
                :href="'/catalog/products/' + row.id + '/edit'"
                class="font-mono font-semibold text-[var(--teal)] hover:underline inline-flex items-center gap-1.5"
                title="Click to edit product"
            >
                <span>@{{ row.sku }}</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-60">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                </svg>
            </a>
        </template>

        <template #cell-type="{ row }">
            <span
                v-if="row.type === 'configurable'"
                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-teal-500/10 text-[var(--teal)] border border-[var(--teal)]/30"
            >
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="18" cy="18" r="3"/>
                    <circle cx="6" cy="6" r="3"/>
                    <path d="M6 21V9a9 9 0 0 0 9 9"/>
                </svg>
                <span>Configurable</span>
            </span>
            <span
                v-else
                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-500 border border-blue-500/30"
            >
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect width="16" height="16" x="4" y="4" rx="2"/>
                </svg>
                <span>Simple</span>
            </span>
        </template>

        <template #cell-family="{ row }">
            <span class="inline-flex items-center gap-1.5 text-xs text-[var(--text)] font-medium">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--muted)]">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
                <span>@{{ row.family }}</span>
            </span>
        </template>

        <template #cell-status="{ row }">
            <span
                v-if="row.status"
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-500 border border-emerald-500/30"
            >
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Active</span>
            </span>
            <span
                v-else
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-500/10 text-zinc-400 border border-zinc-500/30"
            >
                <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span>
                <span>Inactive</span>
            </span>
        </template>

        <!-- Custom Mobile Bento Box Card Slot for Products -->
        <template #mobile-card="{ row, index, columns, moveUp, moveDown, toggleStatus, deleteRow }">
            <div class="space-y-3">
                <!-- Card Top: SKU, Level badge & Status pulse -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                        <span class="font-mono text-[10px] text-[var(--muted)] font-bold">#@{{ index + 1 }}</span>
                        <a
                            :href="'/catalog/products/' + row.id + '/edit'"
                            class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-[var(--soft)] text-[var(--teal)] border border-[var(--teal)]/20 hover:underline truncate"
                        >
                            @{{ row.sku }}
                        </a>
                        <span
                            v-if="row.type === 'configurable'"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-teal-500/10 text-[var(--teal)] border border-[var(--teal)]/30"
                        >
                            Configurable
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/10 text-blue-500 border border-blue-500/30"
                        >
                            Simple
                        </span>
                    </div>

                    <!-- Live status toggle -->
                    <button
                        type="button"
                        @click.stop="toggleStatus"
                        class="cursor-pointer transition-transform hover:scale-105 inline-flex items-center"
                        :title="row.status ? 'Active (Click to deactivate)' : 'Inactive (Click to activate)'"
                    >
                        <span
                            v-if="row.status"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-500 border border-emerald-500/30"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Active</span>
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-500/10 text-zinc-400 border border-zinc-500/30"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-400"></span>
                            <span>Inactive</span>
                        </span>
                    </button>
                </div>

                <!-- Family & Variants Count -->
                <div class="flex items-center justify-between text-xs text-[var(--muted)] pt-1">
                    <span class="flex items-center gap-1.5">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                        <span>Family: <strong class="text-[var(--text)]">@{{ row.family || '—' }}</strong></span>
                    </span>
                    <span v-if="row.variants_count !== undefined" class="text-[var(--teal)] font-mono text-[11px] font-bold">
                        @{{ row.variants_count }} variants
                    </span>
                </div>

                <!-- Card Footer: Position reorder arrows + Edit Button -->
                <div class="pt-2.5 border-t border-[var(--border)] flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            @click="moveUp"
                            :disabled="index === 0"
                            class="p-1 rounded-md bg-[var(--page)] border border-[var(--border)] text-[var(--muted)] hover:text-[var(--text)] disabled:opacity-30 cursor-pointer"
                            title="Move Up"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                        </button>
                        <button
                            type="button"
                            @click="moveDown"
                            class="p-1 rounded-md bg-[var(--page)] border border-[var(--border)] text-[var(--muted)] hover:text-[var(--text)] disabled:opacity-30 cursor-pointer"
                            title="Move Down"
                        >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <span class="text-[10px] text-[var(--muted)] font-mono ml-0.5">Pos @{{ index + 1 }}</span>
                    </div>

                    <a
                        :href="'/catalog/products/' + row.id + '/edit'"
                        class="px-3 py-1 rounded-lg bg-[var(--teal)]/10 hover:bg-[var(--teal)] text-[var(--teal)] hover:text-white border border-[var(--teal)]/30 text-xs font-semibold inline-flex items-center gap-1 transition-colors"
                    >
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
                        <span>Edit</span>
                    </a>
                </div>
            </div>
        </template>
    </x-admin::datagrid>

    <!-- Mobile Floating Action Button (FAB) for instant thumb reachability -->
    <div class="sm:hidden fixed bottom-6 right-5 z-40">
        <button
            type="button"
            onclick="window.dispatchEvent(new CustomEvent('ht-open-create-product'))"
            class="h-12 px-4 rounded-2xl bg-gradient-to-r from-teal-500 to-teal-400 text-slate-950 font-bold text-xs shadow-lg shadow-teal-500/30 flex items-center gap-2 active:scale-95 transition-transform cursor-pointer"
            title="Create Product"
        >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Create</span>
        </button>
    </div>

    <div class="hidden" aria-hidden="true"><products-list></products-list></div>

    <!-- Create Product Modal Popup -->
    @include('admin::products.create-modal', [
        'families' => $families ?? [],
        'initialOpen' => request()->has('create')
    ])
</div>
@endsection
