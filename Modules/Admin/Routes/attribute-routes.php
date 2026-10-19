<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AttributeController;

Route::middleware(['web', 'auth'])->group(function () {
    // Standard Catalog Attributes routes
    Route::controller(AttributeController::class)->prefix('catalog/attributes')->group(function () {
        Route::get('', 'index')->name('admin.catalog.attributes.index');
        Route::get('create', 'create')->name('admin.catalog.attributes.create');
        Route::get('{attribute}/edit', 'edit')->name('admin.catalog.attributes.edit');
        Route::delete('{attribute}', 'destroy')->name('admin.catalog.attributes.destroy');
    });

    // Root-level Attributes routes
    Route::controller(AttributeController::class)->prefix('attributes')->name('admin.attributes.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('{attribute}/edit', 'edit')->name('edit');
        Route::delete('{attribute}', 'destroy')->name('destroy');
    });
});
