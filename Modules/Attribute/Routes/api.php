<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AttributeController;
use Modules\Admin\Http\Controllers\AttributeGroupController;

Route::prefix('catalog')->group(function () {
    Route::get('/attribute-types', [AttributeController::class, 'types']);
    Route::post('/attributes', [AttributeController::class, 'store']);
    Route::get('/attributes/filterable', [AttributeController::class, 'filterable']);
    Route::get('/attributes/{attribute}', [AttributeController::class, 'show']);
    Route::put('/attributes/{attribute}', [AttributeController::class, 'update']);
    Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy']);

    // Attribute Groups API
    Route::get('/attribute-groups', [AttributeGroupController::class, 'index']);
    Route::post('/attribute-groups', [AttributeGroupController::class, 'store']);
    Route::get('/attribute-groups/{attribute_group}', [AttributeGroupController::class, 'show']);
    Route::put('/attribute-groups/{attribute_group}', [AttributeGroupController::class, 'update']);
    Route::delete('/attribute-groups/{attribute_group}', [AttributeGroupController::class, 'destroy']);
});

Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy']);
