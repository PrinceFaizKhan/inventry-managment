<?php

namespace Modules\Catalog\Attributes\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Catalog\Attributes\AttributeTypeRegistry;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Policies\AttributePolicy;

class AttributesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        if (! $this->app->bound(AttributeTypeRegistry::class)) {
            $this->app->singleton(AttributeTypeRegistry::class);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $langDir = dirname(__DIR__).'/Resources/lang';
        if (File::isDirectory($langDir)) {
            $this->loadTranslationsFrom($langDir, 'catalog.attributes');
        }

        $viewsDir = dirname(__DIR__).'/Resources/views';
        if (File::isDirectory($viewsDir)) {
            $this->loadViewsFrom($viewsDir, 'catalog.attributes');
        }

        $migrationDir = dirname(__DIR__).'/Database/Migrations';
        if (File::isDirectory($migrationDir)) {
            $this->loadMigrationsFrom($migrationDir);
        }

        $webRoutes = dirname(__DIR__).'/Routes/web.php';
        if (File::exists($webRoutes)) {
            $this->loadRoutesFrom($webRoutes);
        }

        Gate::policy(Attribute::class, AttributePolicy::class);
    }
}
