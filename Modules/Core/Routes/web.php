<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\TranslationController;

Route::get('/api/translations', [TranslationController::class, 'index'])->middleware('web');
