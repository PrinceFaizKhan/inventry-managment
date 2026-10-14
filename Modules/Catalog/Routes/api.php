<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\AttributeController;

Route::prefix('catalog')->group(function () {
    Route::get('/attribute-types', [AttributeController::class, 'types']);
    Route::post('/attributes', [AttributeController::class, 'store']);
    Route::get('/attributes/filterable', [AttributeController::class, 'filterable']);
    Route::get('/attributes/{attribute}', [AttributeController::class, 'show']);
    Route::put('/attributes/{attribute}', [AttributeController::class, 'update']);
    Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy']);
});
