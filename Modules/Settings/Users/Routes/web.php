<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('settings')->group(function () {
    Route::get('/users', function () {
        if (! auth()->check()) {
            return view('app');
        }

        return view('settings.users::index');
    })->name('settings.users');
});
