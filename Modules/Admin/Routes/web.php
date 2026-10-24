<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\DashboardController;

// Auth Routes (Login, Logout, Session Bootstrap)
require __DIR__.'/auth-routes.php';

// Dashboard Routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
});

// Attribute Admin Routes
require __DIR__.'/attribute-routes.php';

// Product Type Admin Routes
require __DIR__.'/product-type-routes.php';

// Settings Admin Routes
require __DIR__.'/settings-routes.php';
