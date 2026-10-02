<?php

namespace Modules\Catalog\Providers;

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
    }
}
