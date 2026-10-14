<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Settings\RoleController;
use Modules\Admin\Http\Controllers\Settings\UserController;

Route::middleware(['web'])->prefix('settings')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('settings.users');
    Route::get('roles/create', [RoleController::class, 'create'])->name('settings.roles.create');
});
