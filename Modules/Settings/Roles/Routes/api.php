<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Roles\Http\Controllers\RoleController;

Route::post('/roles', [RoleController::class, 'store']);
