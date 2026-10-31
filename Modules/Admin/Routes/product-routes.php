<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\ProductController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::controller(ProductController::class)->prefix('catalog/products')->group(function () {
        Route::get('', 'index')->name('admin.catalog.products.index');
        Route::get('create', 'create')->name('admin.catalog.products.create');
        Route::get('{product}/edit', 'edit')->name('admin.catalog.products.edit');
        Route::delete('{product}', 'destroy')->name('admin.catalog.products.destroy');
    });
});
