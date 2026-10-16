<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Http\Middleware\SetLocale;
use Modules\Core\Support\Grid\GridBulkActionHandler;
use Modules\Core\Support\Grid\GridConfigurationException;
use Modules\Core\Support\Grid\GridDataProvider;
use Modules\Core\Support\Grid\GridRegistry;
use Modules\Core\Support\Menu\MenuAuthorizer;
use Modules\Core\Support\Menu\MenuConfigurationException;
use Modules\Core\Support\Menu\MenuRegistry;
use Modules\Core\Support\Permissions\PermissionConfigurationException;
use Modules\Core\Support\Permissions\PermissionRegistry;
use Modules\Core\Support\ViewEvent\ViewRenderEventManager;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once __DIR__.'/../Support/ViewEvent/helpers.php';

        // Register class aliases for backward compatibility if not already defined
        $aliases = [
            'App\Support\ViewEvent\ViewRenderEventManager' => ViewRenderEventManager::class,
            'App\Support\Menu\MenuAuthorizer' => MenuAuthorizer::class,
            'App\Support\Menu\MenuRegistry' => MenuRegistry::class,
            'App\Support\Menu\MenuConfigurationException' => MenuConfigurationException::class,
            'App\Support\Grid\GridRegistry' => GridRegistry::class,
            'App\Support\Grid\GridDataProvider' => GridDataProvider::class,
            'App\Support\Grid\GridBulkActionHandler' => GridBulkActionHandler::class,
            'App\Support\Grid\GridConfigurationException' => GridConfigurationException::class,
            'App\Support\Permissions\PermissionRegistry' => PermissionRegistry::class,
            'App\Support\Permissions\PermissionConfigurationException' => PermissionConfigurationException::class,
            'App\Http\Middleware\SetLocale' => SetLocale::class,
        ];
        foreach ($aliases as $alias => $target) {
            if (! class_exists($alias, false) && ! interface_exists($alias, false)) {
                class_alias($target, $alias);
            }
        }

        $this->app->singleton(ViewRenderEventManager::class);
        $this->app->singleton(MenuAuthorizer::class);
        $this->app->singleton(MenuRegistry::class);
        $this->app->singleton(GridRegistry::class);
        $this->app->singleton(PermissionRegistry::class);

        // Container aliases for backwards compatibility
        $this->app->alias(ViewRenderEventManager::class, 'App\Support\ViewEvent\ViewRenderEventManager');
        $this->app->alias(MenuAuthorizer::class, 'App\Support\Menu\MenuAuthorizer');
        $this->app->alias(MenuRegistry::class, 'App\Support\Menu\MenuRegistry');
        $this->app->alias(GridRegistry::class, 'App\Support\Grid\GridRegistry');
        $this->app->alias(PermissionRegistry::class, 'App\Support\Permissions\PermissionRegistry');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'core');

        // Register blade directive for view_event_render
        Blade::directive('view_event_render', function ($expression) {
            return "<?php echo view_event_render({$expression}); ?>";
        });
    }
}
