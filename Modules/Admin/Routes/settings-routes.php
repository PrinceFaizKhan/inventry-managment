<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Settings\RoleController;
use Modules\Admin\Http\Controllers\Settings\UserController;

Route::middleware(['web'])->prefix('settings')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('settings.users');
    Route::get('users/create', [UserController::class, 'create'])->name('settings.users.create');
    Route::post('users', [UserController::class, 'store'])->name('settings.users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('settings.users.edit');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('settings.users.toggle-status');
    Route::post('users/{user}/activate', [UserController::class, 'toggleStatus']);
    Route::post('users/{user}/deactivate', [UserController::class, 'toggleStatus']);
    Route::put('users/{user}', [UserController::class, 'update'])->name('settings.users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('settings.users.destroy');
    Route::get('roles/create', [RoleController::class, 'create'])->name('settings.roles.create');
});

Route::middleware(['web', 'auth'])->prefix('api/settings')->group(function () {
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::post('users', [UserController::class, 'store']);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::post('users/{user}/activate', [UserController::class, 'toggleStatus']);
    Route::post('users/{user}/deactivate', [UserController::class, 'toggleStatus']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
});
