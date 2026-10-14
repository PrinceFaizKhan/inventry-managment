<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\BulkGridActionController;
use Modules\Core\Http\Controllers\GridController;
use Modules\Core\Http\Controllers\NavigationController;
use Modules\Core\Http\Controllers\PermissionController;

Route::get('/navigation', NavigationController::class);
Route::get('/grids/{grid}', [GridController::class, 'index']);
Route::post('/grids/{grid}/bulk/{action}', BulkGridActionController::class);
Route::get('/permissions', PermissionController::class);
