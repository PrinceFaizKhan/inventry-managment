@extends('admin::layouts.admin')

@section('title', 'Attribute Groups')
@section('crumb', 'Attribute Groups')

@section('content')
<div class="attribute-groups-page space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-[var(--border)]">
        <div>
            <p class="eyebrow">Catalog / Attribute Groups</p>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">Attribute Groups</h1>
            <p class="page-copy text-sm text-[var(--muted)] mt-1">Organize product attributes into logical groups like General, Description, and Money.</p>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.catalog.attribute-groups.create') }}"
                class="button button--primary inline-flex items-center gap-2 cursor-pointer shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-transform text-xs"
                title="Create New Attribute Group"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Create Group</span>
            </a>
        </div>
    </div>

    <!-- DataGrid -->
    <x-admin::datagrid
        src="/api/grids/attribute-groups"
        search-placeholder="Search by code or name..."
    >
        <template #cell-code="{ row }">
            <a
                :href="'/catalog/attribute-groups/' + row.id + '/edit'"
                class="font-mono font-semibold text-[var(--teal)] hover:underline inline-flex items-center gap-1.5"
                title="Click to edit group"
            >
                <span>@{{ row.code }}</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-60">
                    <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                </svg>
            </a>
        </template>
        <template #cell-name="{ row }">
            <a
                :href="'/catalog/attribute-groups/' + row.id + '/edit'"
                class="font-semibold text-[var(--text)] hover:text-[var(--teal)] transition-colors"
                title="Click to edit group"
            >
                @{{ row.name }}
            </a>
        </template>
    </x-admin::datagrid>

    <div class="hidden" aria-hidden="true"><attribute-groups-list></attribute-groups-list></div>
</div>
@endsection
