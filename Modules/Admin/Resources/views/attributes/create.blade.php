@extends('admin::layouts.admin')

@section('title', 'Create Attribute')
@section('crumb', 'Create Attribute')

@section('content')
<div class="attribute-create-page space-y-6">
    {!! view_event_render('catalog.attributes.create.before') !!}

    <div class="p-8 rounded-2xl border border-[var(--border)] bg-[var(--card)] text-center max-w-lg mx-auto space-y-4 my-12 shadow-sm">
        <div class="w-12 h-12 rounded-2xl bg-[var(--soft)] text-[var(--teal)] flex items-center justify-center mx-auto">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-[var(--text)]">Create Attribute</h1>
            <p class="text-xs text-[var(--muted)] mt-1">Attributes are created quickly using the modal dialog and configured on the edit page.</p>
        </div>
        <div class="flex items-center justify-center gap-3 pt-2">
            <a href="{{ url('catalog/attributes') }}" class="button button--outline text-xs">
                Back to Catalog
            </a>
            <button
                type="button"
                onclick="window.dispatchEvent(new CustomEvent('ht-open-create-attribute'))"
                class="button button--primary inline-flex items-center gap-2 text-xs"
            >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Open Create Modal</span>
            </button>
        </div>
    </div>

    @include('admin::attributes.create-modal', ['types' => $types ?? [], 'initialOpen' => true])

    {!! view_event_render('catalog.attributes.create.after') !!}
</div>
@endsection
