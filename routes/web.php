<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NavigationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'app');
Route::post('/login', [AuthController::class, 'store'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth');
Route::get('/api/bootstrap', fn (Request $request) => response()->json(['user' => $request->user()?->only('id', 'name', 'email')]))->middleware('web');
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/navigation', NavigationController::class);
    Route::get('/admins', [AdminController::class, 'index']);
});
