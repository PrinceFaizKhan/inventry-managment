@extends('admin::layouts.admin')

@section('title', __('catalog.attributes::app.title') ?? 'Create Attribute')
@section('crumb', __('catalog.attributes::app.title') ?? 'Create Attribute')

@section('content')
<div class="attribute-create-page">
    <div class="attribute-modal__header flex justify-between items-center mb-6">
        <div>
            <p class="eyebrow">{{ __('nav.catalog') ?? 'Catalog' }} / {{ __('catalog.attributes::app.title') ?? 'Attributes' }}</p>
            <h1>{{ __('catalog.attributes::app.created') ?? 'Create Attribute' }}</h1>
        </div>
        <a href="{{ url('catalog/attributes') }}" class="button button--outline">
            {{ __('action.back') ?? 'Back' }}
        </a>
    </div>

    {!! view_event_render('catalog.attributes.create.before') !!}

    <attribute-create-modal
        :is-open="true"
        :types='@json($types)'
        @close="window.location.href = '{{ url('catalog/attributes') }}'"
        @created="window.location.href = '{{ url('catalog/attributes') }}'"
    ></attribute-create-modal>

    {!! view_event_render('catalog.attributes.create.after') !!}
</div>
@endsection
