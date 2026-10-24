@extends('admin::layouts.admin')

@section('title', __('catalog.attributes::app.title') ?? 'Attributes')
@section('crumb', __('catalog.attributes::app.title') ?? 'Attributes')

@section('content')
<div class="attributes-page space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-[var(--border)]">
        <div>
            <p class="eyebrow">Catalog / Attributes</p>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">Attributes</h1>
            <p class="page-copy text-sm text-[var(--muted)] mt-1">Define and manage reusable product attributes for your catalog.</p>
        </div>
        <div class="flex items-center gap-2">
            {!! view_event_render('catalog.attributes.header.actions') !!}
            <button
                type="button"
                onclick="window.dispatchEvent(new CustomEvent('ht-open-create-attribute'))"
                class="button button--primary inline-flex items-center gap-2 cursor-pointer shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-transform"
                title="Create New Attribute"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Create Attribute</span>
            </button>
        </div>
    </div>

    {!! view_event_render('catalog.attributes.grid.before') !!}

    <x-admin::datagrid
        src="/api/grids/attributes"
        search-placeholder="Search by code or name..."
    >
        <template #cell-code="{ row }">
            <a :href="'/catalog/attributes/' + row.id + '/edit'" class="font-mono font-semibold text-[var(--teal)] hover:underline">
                @{{ row.code }}
            </a>
        </template>
        <template #cell-name="{ row }">
            <span class="font-medium text-[var(--text)]">@{{ row.name }}</span>
        </template>
        <template #cell-type="{ row }">
            <v-status-badge :value="row.type" type="badge" size="sm"></v-status-badge>
        </template>
    </x-admin::datagrid>

    <div class="hidden" aria-hidden="true"><attributes-list></attributes-list></div>

    @include('admin::attributes.create-modal', ['types' => $types ?? []])

    {!! view_event_render('catalog.attributes.grid.after') !!}
</div>
@endsection
