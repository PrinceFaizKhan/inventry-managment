<?php

use App\Providers\AppServiceProvider;
use Modules\Admin\Providers\AdminServiceProvider;
use Modules\Catalog\Attributes\Providers\AttributesServiceProvider;
use Modules\Catalog\Products\Providers\ProductsServiceProvider;
use Modules\Catalog\ProductTypes\Providers\ProductTypesServiceProvider;
use Modules\Catalog\Providers\CatalogServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Inventory\Providers\InventoryServiceProvider;
use Modules\Sales\Providers\SalesServiceProvider;
use Modules\Settings\Providers\SettingsServiceProvider;
use Modules\Settings\Roles\Providers\RolesServiceProvider;
use Modules\Settings\Users\Providers\UsersServiceProvider;

return [
    AppServiceProvider::class,

    // Core Framework Module
    CoreServiceProvider::class,

    // Admin & Auth Connector
    AdminServiceProvider::class,

    // Catalog Module & Independent Sub-menus
    CatalogServiceProvider::class,
    AttributesServiceProvider::class,
    ProductTypesServiceProvider::class,
    ProductsServiceProvider::class,

    // Settings Module & Independent Sub-menus
    SettingsServiceProvider::class,
    UsersServiceProvider::class,
    RolesServiceProvider::class,

    // External Pluggable Connectors (Can be extracted to independent Git packages)
    SalesServiceProvider::class,
    InventoryServiceProvider::class,
];
