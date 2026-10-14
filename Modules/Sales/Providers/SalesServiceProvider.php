<?php

namespace Modules\Sales\Providers;

use Illuminate\Support\ServiceProvider;

class SalesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $viewsDir = __DIR__.'/../Resources/views';
        if (is_dir($viewsDir)) {
            $this->loadViewsFrom($viewsDir, 'sales');
        }

        $migrationsDir = __DIR__.'/../Database/Migrations';
        if (is_dir($migrationsDir)) {
            $this->loadMigrationsFrom($migrationsDir);
        }

        $langDir = __DIR__.'/../Resources/lang';
        if (is_dir($langDir)) {
            $this->loadTranslationsFrom($langDir, 'sales');
        }

        $routesFile = __DIR__.'/../Routes/web.php';
        if (file_exists($routesFile)) {
            $this->loadRoutesFrom($routesFile);
        }
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $menuFile = __DIR__.'/../Config/menu.php';
        if (file_exists($menuFile)) {
            $this->mergeConfigFrom($menuFile, 'menu.sales');
        }

        $permissionsFile = __DIR__.'/../Config/permissions.php';
        if (file_exists($permissionsFile)) {
            $this->mergeConfigFrom($permissionsFile, 'permissions.sales');
        }
    }
}
