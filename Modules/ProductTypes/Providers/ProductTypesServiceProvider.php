<?php

namespace Modules\ProductTypes\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\ProductTypes\Models\ProductType;
use Modules\ProductTypes\Policies\ProductTypePolicy;

class ProductTypesServiceProvider extends ServiceProvider
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
            $this->loadTranslationsFrom($langDir, 'catalog.producttypes');
        }

        $migrationDir = dirname(__DIR__).'/Database/Migrations';
        if (File::isDirectory($migrationDir)) {
            $this->loadMigrationsFrom($migrationDir);
        }

        Gate::policy(ProductType::class, ProductTypePolicy::class);
    }
}
