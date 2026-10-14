<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Catalog\AttributeController;

Route::middleware(['web', 'auth'])->prefix('catalog')->group(function () {
    Route::controller(AttributeController::class)->prefix('attributes')->group(function () {
        Route::get('', 'index')->name('admin.catalog.attributes.index');
        Route::get('create', 'create')->name('admin.catalog.attributes.create');
        Route::get('{attribute}/edit', 'edit')->name('admin.catalog.attributes.edit');
    });
});
