@extends('admin::layouts.admin')

@section('title', 'Edit Product Type: ' . ($productType['name'] ?? ''))
@section('crumb', 'Edit Product Type')

@section('content')
<div class="product-type-edit-page space-y-6">
    <v-product-type-builder
        mode="edit"
        :initial-type='@json($productType)'
        :all-groups='@json($allGroups)'
        :all-attributes='@json($allAttributes)'
        :select-attributes='@json($selectAttributes)'
        back-url="{{ route('admin.catalog.product-types.index') }}"
    ></v-product-type-builder>

    <div class="hidden" aria-hidden="true">
        <product-type-edit-builder></product-type-edit-builder>
    </div>

    @include('admin::product-types.builder')
</div>
@endsection
