<?php

namespace Modules\Catalog\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Catalog\Attributes\AttributeTypeRegistry;
use Modules\Catalog\Models\Attribute;
use Modules\Catalog\Policies\AttributePolicy;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AttributeTypeRegistry::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(base_path('Modules/Catalog/Database/Migrations'));
        Gate::policy(Attribute::class, AttributePolicy::class);

        // Auto-discover translations across all catalog sub-menus
        foreach (File::glob(base_path('Modules/Catalog/*/Resources/lang'), GLOB_ONLYDIR) as $langDir) {
            $sliceName = strtolower(basename(dirname($langDir, 2)));
            $this->loadTranslationsFrom($langDir, 'catalog.'.$sliceName);
        }

        // Auto-discover migrations across all catalog sub-menus
        foreach (File::glob(base_path('Modules/Catalog/*/Database/Migrations'), GLOB_ONLYDIR) as $subMigrationDir) {
            $this->loadMigrationsFrom($subMigrationDir);
        }
    }
}
