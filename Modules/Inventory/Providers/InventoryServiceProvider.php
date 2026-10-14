<?php

namespace Modules\Inventory\Providers;

use Illuminate\Support\ServiceProvider;

class InventoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $viewsDir = __DIR__.'/../Resources/views';
        if (is_dir($viewsDir)) {
            $this->loadViewsFrom($viewsDir, 'inventory');
        }

        $migrationsDir = __DIR__.'/../Database/Migrations';
        if (is_dir($migrationsDir)) {
            $this->loadMigrationsFrom($migrationsDir);
        }

        $langDir = __DIR__.'/../Resources/lang';
        if (is_dir($langDir)) {
            $this->loadTranslationsFrom($langDir, 'inventory');
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
            $this->mergeConfigFrom($menuFile, 'menu.inventory');
        }

        $permissionsFile = __DIR__.'/../Config/permissions.php';
        if (file_exists($permissionsFile)) {
            $this->mergeConfigFrom($permissionsFile, 'permissions.inventory');
        }
    }
}
