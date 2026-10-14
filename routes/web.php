<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\User\SessionController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }

    return app(SessionController::class)->create();
})->name('login');

Route::middleware('auth')->prefix('api')->group(function () {

    // Auto-discover API routes across all modules and sub-menus (Admin manages its own auth boundaries)
    $apiRoutes = array_unique([
        ...File::glob(base_path('Modules/*/Routes/api.php')),
        ...File::glob(base_path('Modules/*/*/Routes/api.php')),
    ]);
    foreach ($apiRoutes as $routeFile) {
        if (str_contains($routeFile, 'Modules/Admin/Routes/api.php')) {
            continue;
        }
        require $routeFile;
    }
});

// Admin authentication API routes
Route::middleware('web')->prefix('api/admin')->group(function () {
    if (file_exists(base_path('Modules/Admin/Routes/api.php'))) {
        require base_path('Modules/Admin/Routes/api.php');
    }
});

// Auto-discover web routes across all modules and sub-menus
$webRoutes = array_unique([
    ...File::glob(base_path('Modules/*/Routes/web.php')),
    ...File::glob(base_path('Modules/*/*/Routes/web.php')),
]);
foreach ($webRoutes as $webRouteFile) {
    require $webRouteFile;
}

Route::fallback(function (Request $request) {
    if ($request->is('api/*') || ! $request->isMethod('GET')) {
        abort(404);
    }

    return response()->view('app');
});
