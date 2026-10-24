@extends('admin::layouts.admin')

@section('title', 'Create Attribute Group')
@section('crumb', 'Create Attribute Group')

@section('content')
<div class="attribute-group-create-page space-y-6">
    <v-attribute-group-form
        mode="create"
        back-url="{{ route('admin.catalog.attribute-groups.index') }}"
    ></v-attribute-group-form>

    <div class="hidden" aria-hidden="true">
        <attribute-group-create-form></attribute-group-create-form>
    </div>

    @include('admin::attributes.groups.form')
</div>
@endsection
