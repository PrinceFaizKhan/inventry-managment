<?php

namespace App\Providers;

use App\Support\Grid\GridRegistry;
use App\Support\Menu\MenuAuthorizer;
use App\Support\Menu\MenuRegistry;
use App\Support\Permissions\PermissionRegistry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
        $this->app->singleton(MenuAuthorizer::class);
        $this->app->singleton(MenuRegistry::class);
        $this->app->singleton(GridRegistry::class);
        $this->app->singleton(PermissionRegistry::class);
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
    }
}
