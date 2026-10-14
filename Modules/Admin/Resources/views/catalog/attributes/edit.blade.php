@extends('admin::layouts.admin')

@section('title', 'Edit Attribute')
@section('crumb', 'Edit Attribute')

@section('content')
<div class="attribute-edit-page">
    {!! view_event_render('catalog.attributes.edit.before') !!}

    <attribute-edit-form
        :initial-attribute='@json($attribute)'
        :initial-types='@json($types)'
        :attribute-id="{{ is_array($attribute) ? $attribute['id'] : $attribute->id }}"
        back-url="{{ url('catalog/attributes') }}"
        csrf-token="{{ csrf_token() }}"
    ></attribute-edit-form>

    {!! view_event_render('catalog.attributes.edit.after') !!}
</div>
@endsection
