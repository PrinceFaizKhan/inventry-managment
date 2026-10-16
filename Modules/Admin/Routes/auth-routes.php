<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\User\AccountController;
use Modules\Admin\Http\Controllers\User\SessionController;

Route::middleware('web')->group(function () {
    Route::controller(SessionController::class)->group(function () {
        Route::get('/login', 'create')->name('admin.session.create');
        Route::post('/login', 'store')->name('admin.session.store')->middleware('throttle:login');
        Route::post('/logout', 'destroy')->name('admin.session.destroy');
        Route::get('/api/bootstrap', 'bootstrap');
        Route::get('/api/admin/bootstrap', 'bootstrap');
        Route::post('/api/admin/login', 'store')->middleware('throttle:login');
        Route::post('/api/admin/logout', 'destroy');
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::controller(AccountController::class)->group(function () {
        Route::get('/account', 'edit')->name('admin.account.edit');
        Route::put('/account', 'update')->name('admin.account.update');
    });
});
