<?php

namespace Modules\Product\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Product\Models\Product;
use Modules\Product\Policies\ProductPolicy;

class ProductsServiceProvider extends ServiceProvider
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
            $this->loadTranslationsFrom($langDir, 'catalog.products');
        }

        $migrationDir = dirname(__DIR__).'/Database/Migrations';
        if (File::isDirectory($migrationDir)) {
            $this->loadMigrationsFrom($migrationDir);
        }

        Gate::policy(Product::class, ProductPolicy::class);
    }
}
