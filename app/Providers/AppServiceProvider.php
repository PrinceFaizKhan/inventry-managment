<?php

namespace App\Providers;

use App\Support\Menu\MenuAuthorizer;
use App\Support\Menu\MenuRegistry;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MenuAuthorizer::class);
        $this->app->singleton(MenuRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
