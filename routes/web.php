<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkGridActionController;
use App\Http\Controllers\GridController;
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\AttributeController;

Route::view('/', 'app');
Route::post('/login', [AuthController::class, 'store'])->middleware(['guest', 'throttle:login']);
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth');
Route::get('/api/bootstrap', fn (Request $request) => response()->json(['user' => $request->user()?->only('id', 'name', 'email')]))->middleware('web');
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/navigation', NavigationController::class);
    Route::get('/grids/{grid}', [GridController::class, 'index']);
    Route::post('/grids/{grid}/bulk/{action}', BulkGridActionController::class);
    Route::get('/permissions', PermissionController::class);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::prefix('catalog')->group(function () {
        Route::get('/attribute-types', [AttributeController::class, 'types']);
        Route::post('/attributes', [AttributeController::class, 'store']);
        Route::get('/attributes/{attribute}', [AttributeController::class, 'show']);
        Route::put('/attributes/{attribute}', [AttributeController::class, 'update']);
        Route::delete('/attributes/{attribute}', [AttributeController::class, 'destroy']);
    });
});

Route::fallback(function (Request $request) {
    if ($request->is('api/*') || ! $request->isMethod('GET')) {
        abort(404);
    }

    return response()->view('app');
});
