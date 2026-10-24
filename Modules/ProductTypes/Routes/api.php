<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\ProductTypeController;

Route::prefix('catalog/product-types')->group(function () {
    Route::get('', [ProductTypeController::class, 'index']);
    Route::post('', [ProductTypeController::class, 'store']);
    Route::get('{product_type}', [ProductTypeController::class, 'show']);
    Route::put('{product_type}', [ProductTypeController::class, 'update']);
    Route::delete('{product_type}', [ProductTypeController::class, 'destroy']);
});
