@extends('admin::layouts.admin')

@section('title', 'Create Attribute')
@section('crumb', 'Create Attribute')

@section('content')
<div class="attribute-create-page">
    <div class="attribute-modal__header flex justify-between items-center mb-6">
        <div>
            <p class="eyebrow">Catalog / Attributes</p>
            <h1>Create Attribute</h1>
        </div>
        <a href="{{ url('catalog/attributes') }}" class="button button--outline">
            Back
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
