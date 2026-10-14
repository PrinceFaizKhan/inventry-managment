<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Catalog\Attributes\AttributeTypeRegistry;
use Modules\Catalog\Http\Controllers\AttributeController;
use Modules\Catalog\Models\Attribute;

Route::middleware(['web'])->prefix('catalog')->name('catalog.')->group(function () {
    Route::get('/attributes', function (Request $request) {
        if (! auth()->check()) {
            return view('app');
        }

        return app(AttributeController::class)->index($request);
    })->name('attributes.index');

    Route::get('/attributes/create', function (AttributeTypeRegistry $types) {
        if (! auth()->check()) {
            return view('app');
        }

        return app(AttributeController::class)->create($types);
    })->name('attributes.create');

    Route::get('/attributes/{attribute}/edit', function (Attribute $attribute, AttributeTypeRegistry $types) {
        if (! auth()->check()) {
            return view('app');
        }

        return app(AttributeController::class)->edit($attribute, $types);
    })->name('attributes.edit');
});
