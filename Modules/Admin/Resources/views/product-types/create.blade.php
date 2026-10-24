@extends('admin::layouts.admin')

@section('title', 'Create Product Type')
@section('crumb', 'Create Product Type')

@section('content')
<div class="product-type-create-page space-y-6">
    <v-product-type-builder
        mode="create"
        :all-groups='@json($allGroups)'
        :all-attributes='@json($allAttributes)'
        :select-attributes='@json($selectAttributes)'
        back-url="{{ route('admin.catalog.product-types.index') }}"
    ></v-product-type-builder>

    <div class="hidden" aria-hidden="true">
        <product-type-create-builder></product-type-create-builder>
    </div>

    @include('admin::product-types.builder')
</div>
@endsection
