<?php

namespace Modules\Attribute\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Attribute\AttributeTypeRegistry;
use Modules\Attribute\Models\Attribute;
use Modules\Attribute\Policies\AttributePolicy;

class AttributeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AttributeTypeRegistry::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $langDir = dirname(__DIR__).'/Resources/lang';
        if (File::isDirectory($langDir)) {
            $this->loadTranslationsFrom($langDir, 'catalog.attributes');
            $this->loadTranslationsFrom($langDir, 'attributes');
        }

        $migrationDir = dirname(__DIR__).'/Database/Migrations';
        if (File::isDirectory($migrationDir)) {
            $this->loadMigrationsFrom($migrationDir);
        }

        Gate::policy(Attribute::class, AttributePolicy::class);
    }
}
