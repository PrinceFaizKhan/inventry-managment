<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminAuthController;

Route::post('/login', [AdminAuthController::class, 'store'])->middleware(['guest', 'throttle:login']);
Route::post('/logout', [AdminAuthController::class, 'destroy'])->middleware('auth');
Route::get('/bootstrap', [AdminAuthController::class, 'bootstrap'])->middleware('web');
