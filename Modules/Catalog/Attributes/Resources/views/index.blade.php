@extends('admin::layouts.admin')

@section('title', __('catalog.attributes::app.title') ?? 'Attributes')
@section('crumb', __('catalog.attributes::app.title') ?? 'Attributes')

@section('content')
<div class="attributes-page">
    <div class="attributes-page__heading flex justify-between items-center mb-6">
        <div>
            <p class="eyebrow">{{ __('nav.catalog') ?? 'Catalog' }} / {{ __('catalog.attributes::app.title') ?? 'Attributes' }}</p>
            <h1>{{ __('catalog.attributes::app.title') ?? 'Attributes' }}</h1>
            <p class="page-copy">{{ __('catalog.attributes::app.description') ?? 'Define reusable fields for your catalog.' }}</p>
        </div>
        <div class="flex items-center gap-2">
            {!! view_event_render('catalog.attributes.header.actions') !!}
            <a href="{{ url('catalog/attributes/create') }}" class="button button--primary inline-flex items-center gap-2">
                <span>{{ __('action.create') ?? 'Create' }}</span>
            </a>
        </div>
    </div>

    {!! view_event_render('catalog.attributes.grid.before') !!}

    <attributes-list></attributes-list>

    {!! view_event_render('catalog.attributes.grid.after') !!}
</div>
@endsection
