<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\ProductTypeController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::controller(ProductTypeController::class)->prefix('catalog/product-types')->group(function () {
        Route::get('', 'index')->name('admin.catalog.product-types.index');
        Route::get('create', 'create')->name('admin.catalog.product-types.create');
        Route::get('{product_type}/edit', 'edit')->name('admin.catalog.product-types.edit');
        Route::delete('{product_type}', 'destroy')->name('admin.catalog.product-types.destroy');
    });
});
