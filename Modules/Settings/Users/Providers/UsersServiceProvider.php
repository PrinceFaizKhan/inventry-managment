<?php

namespace Modules\Settings\Users\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class UsersServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $langDir = dirname(__DIR__).'/Resources/lang';
        if (File::isDirectory($langDir)) {
            $this->loadTranslationsFrom($langDir, 'settings.users');
        }

        $viewsDir = dirname(__DIR__).'/Resources/views';
        if (File::isDirectory($viewsDir)) {
            $this->loadViewsFrom($viewsDir, 'settings.users');
        }

        $webRoutes = dirname(__DIR__).'/Routes/web.php';
        if (File::exists($webRoutes)) {
            $this->loadRoutesFrom($webRoutes);
        }

        $migrationDir = dirname(__DIR__).'/Database/Migrations';
        if (File::isDirectory($migrationDir)) {
            $this->loadMigrationsFrom($migrationDir);
        }
    }
}
