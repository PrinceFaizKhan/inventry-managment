@extends('admin::layouts.admin')

@section('title', 'Edit Attribute Group')
@section('crumb', 'Edit Attribute Group')

@section('content')
<div class="attribute-group-edit-page space-y-6">
    <v-attribute-group-form
        mode="edit"
        :initial-group='@json($group)'
        back-url="{{ route('admin.catalog.attribute-groups.index') }}"
    ></v-attribute-group-form>

    <div class="hidden" aria-hidden="true">
        <attribute-group-edit-form></attribute-group-edit-form>
    </div>

    @include('admin::attributes.groups.form')
</div>
@endsection
