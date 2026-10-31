<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\ProductController;

Route::prefix('catalog/products')->group(function () {
    Route::get('', [ProductController::class, 'index']);
    Route::post('', [ProductController::class, 'store']);
    Route::get('{product}', [ProductController::class, 'show']);
    Route::put('{product}', [ProductController::class, 'update']);
    Route::delete('{product}', [ProductController::class, 'destroy']);
});
