<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('settings/roles')->group(function () {
    Route::get('/create', function () {
        if (! auth()->check()) {
            return view('app');
        }

        return view('settings.roles::create');
    })->name('settings.roles.create');
});
