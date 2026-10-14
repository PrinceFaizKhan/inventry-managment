<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        RateLimiter::for('login', function (Request $request): Limit {
            $key = Str::lower($request->string('email')->toString()).'|'.$request->ip();

            return Limit::perMinute(5)->by($key);
        });

        // Auto-discover migrations from all modules and sub-menus
        $migrationDirs = array_unique([
            ...File::glob(base_path('Modules/*/Database/Migrations'), GLOB_ONLYDIR),
            ...File::glob(base_path('Modules/*/*/Database/Migrations'), GLOB_ONLYDIR),
        ]);

        foreach ($migrationDirs as $migrationDir) {
            $this->loadMigrationsFrom($migrationDir);
        }
    }
}
