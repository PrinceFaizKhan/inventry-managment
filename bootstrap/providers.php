<?php

use App\Providers\AppServiceProvider;
use Modules\Admin\Providers\AdminServiceProvider;
use Modules\Attribute\Models\Attribute;
use Modules\Attribute\Models\AttributeOption;
use Modules\Attribute\Providers\AttributeServiceProvider;
use Modules\Core\Providers\CoreServiceProvider;
use Modules\Product\Providers\ProductsServiceProvider;
use Modules\ProductTypes\Providers\ProductTypesServiceProvider;
use Modules\Settings\Providers\SettingsServiceProvider;
use Modules\Settings\Roles\Providers\RolesServiceProvider;
use Modules\Settings\Users\Providers\UsersServiceProvider;

// Aliases for seamless backward compatibility during modular extraction
if (! class_exists('Modules\Catalog\Attributes\Providers\AttributesServiceProvider', false)) {
    class_alias(AttributeServiceProvider::class, 'Modules\Catalog\Attributes\Providers\AttributesServiceProvider');
}
if (! class_exists('Modules\Catalog\Models\Attribute', false)) {
    class_alias(Attribute::class, 'Modules\Catalog\Models\Attribute');
}
if (! class_exists('Modules\Catalog\Models\AttributeOption', false)) {
    class_alias(AttributeOption::class, 'Modules\Catalog\Models\AttributeOption');
}

return [
    AppServiceProvider::class,

    // Core Framework Module
    CoreServiceProvider::class,

    // Admin & Auth Connector
    AdminServiceProvider::class,

    // Domain Modules (Decoupled root level packages like UnoPim)
    AttributeServiceProvider::class,
    ProductsServiceProvider::class,
    ProductTypesServiceProvider::class,

    // Settings Module & Independent Sub-menus
    SettingsServiceProvider::class,
    UsersServiceProvider::class,
    RolesServiceProvider::class,
];
