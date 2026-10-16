<?php

namespace Modules\Admin\Providers;

use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register any admin services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/auth.php', 'admin.auth');
    }

    /**
     * Bootstrap any admin services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'admin');

        $viewsDir = __DIR__.'/../Resources/views';
        if (is_dir($viewsDir)) {
            $this->loadViewsFrom($viewsDir, 'admin');

            $attributesViews = $viewsDir.'/attributes';
            if (is_dir($attributesViews)) {
                $this->loadViewsFrom($attributesViews, 'catalog.attributes');
                $this->loadViewsFrom($attributesViews, 'attributes');
            }

            $usersViews = $viewsDir.'/settings/users';
            if (is_dir($usersViews)) {
                $this->loadViewsFrom($usersViews, 'settings.users');
            }

            $rolesViews = $viewsDir.'/settings/roles';
            if (is_dir($rolesViews)) {
                $this->loadViewsFrom($rolesViews, 'settings.roles');
            }
        }
    }
}
