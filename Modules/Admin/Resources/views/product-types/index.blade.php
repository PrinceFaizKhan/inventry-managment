@extends('admin::layouts.admin')

@section('title', 'Product Types')
@section('crumb', 'Product Types')

@section('content')
<div class="product-types-page space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-[var(--border)]">
        <div>
            <p class="eyebrow">Catalog / Product Types</p>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">Product Types</h1>
            <p class="page-copy text-sm text-[var(--muted)] mt-1">Configure product types with structured attribute groups and drag-and-drop attribute mappings.</p>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.catalog.product-types.create') }}"
                class="button button--primary inline-flex items-center gap-2 cursor-pointer shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-transform text-xs"
                title="Create New Product Type"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Create Product Type</span>
            </a>
        </div>
    </div>

    <!-- DataGrid -->
    <x-admin::datagrid
        src="/api/grids/product-types"
        search-placeholder="Search by code or name..."
    >
        <template #cell-code="{ row }">
            <a
                :href="'/catalog/product-types/' + row.id + '/edit'"
                class="font-mono font-semibold text-[var(--teal)] hover:underline inline-flex items-center gap-1.5"
                title="Click to edit product type"
            >
                <span>@{{ row.code }}</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-60">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                </svg>
            </a>
        </template>
        <template #cell-name="{ row }">
            <a
                :href="'/catalog/product-types/' + row.id + '/edit'"
                class="font-semibold text-[var(--text)] hover:text-[var(--teal)] transition-colors"
                title="Click to edit product type"
            >
                @{{ row.name }}
            </a>
        </template>
        <template #cell-groups_count="{ row }">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-[var(--soft)] text-[var(--text)] border border-[var(--border)]">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                <span>@{{ row.groups_count }} groups</span>
            </span>
        </template>
        <template #cell-attributes_count="{ row }">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-[var(--soft)] text-[var(--text)] border border-[var(--border)]">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]">
                    <line x1="4" y1="9" x2="20" y2="9"></line>
                    <line x1="4" y1="15" x2="20" y2="15"></line>
                    <line x1="10" y1="3" x2="8" y2="21"></line>
                    <line x1="16" y1="3" x2="14" y2="21"></line>
                </svg>
                <span>@{{ row.attributes_count }} attributes</span>
            </span>
        </template>
        <template #cell-variants_count="{ row }">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-50 dark:bg-teal-950/40 text-[var(--teal)] border border-[var(--teal)]/30">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="18" cy="18" r="3"/>
                    <circle cx="6" cy="6" r="3"/>
                    <path d="M6 21V9a9 9 0 0 0 9 9"/>
                </svg>
                <span>@{{ row.variants_count }} variant types</span>
            </span>
        </template>
    </x-admin::datagrid>

    <div class="hidden" aria-hidden="true"><product-types-list></product-types-list></div>
</div>
@endsection
