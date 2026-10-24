<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AttributeController;
use Modules\Admin\Http\Controllers\AttributeGroupController;

Route::middleware(['web', 'auth'])->group(function () {
    // Standard Catalog Attributes routes
    Route::controller(AttributeController::class)->prefix('catalog/attributes')->group(function () {
        Route::get('', 'index')->name('admin.catalog.attributes.index');
        Route::get('create', 'create')->name('admin.catalog.attributes.create');
        Route::get('{attribute}/edit', 'edit')->name('admin.catalog.attributes.edit');
        Route::delete('{attribute}', 'destroy')->name('admin.catalog.attributes.destroy');
    });

    // Catalog Attribute Groups routes
    Route::controller(AttributeGroupController::class)->prefix('catalog/attribute-groups')->group(function () {
        Route::get('', 'index')->name('admin.catalog.attribute-groups.index');
        Route::get('create', 'create')->name('admin.catalog.attribute-groups.create');
        Route::get('{attribute_group}/edit', 'edit')->name('admin.catalog.attribute-groups.edit');
        Route::delete('{attribute_group}', 'destroy')->name('admin.catalog.attribute-groups.destroy');
    });

    // Root-level Attributes routes
    Route::controller(AttributeController::class)->prefix('attributes')->name('admin.attributes.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{attribute}/edit', 'edit')->name('edit');
        Route::delete('{attribute}', 'destroy')->name('destroy');
    });
});
