<?php

return [
    /**
     * Dashboard.
     */
    [
        'key' => 'dashboard',
        'label' => 'Dashboard',
        'icon' => 'layout-dashboard',
        'route' => 'dashboard',
        'sort' => 10,
        'permission' => 'authenticated',
        'children' => [],
    ],

    /**
     * Catalog & Submenus.
     */
    [
        'key' => 'catalog',
        'label' => 'Catalog',
        'icon' => 'package',
        'route' => 'catalog/attributes',
        'sort' => 20,
        'permission' => 'authenticated',
        'children' => [],
    ],
    [
        'key' => 'catalog.attributes',
        'label' => 'Attributes',
        'icon' => 'sliders-horizontal',
        'route' => 'catalog/attributes',
        'sort' => 10,
        'permission' => 'authenticated',
        'parent' => 'catalog',
        'enabled' => true,
        'coming_soon' => false,
        'children' => [],
    ],
    [
        'key' => 'catalog.attribute-groups',
        'label' => 'Attribute Groups',
        'icon' => 'boxes',
        'route' => 'catalog/attribute-groups',
        'sort' => 15,
        'permission' => 'authenticated',
        'parent' => 'catalog',
        'enabled' => true,
        'coming_soon' => false,
        'children' => [],
    ],
    [
        'key' => 'catalog.product-types',
        'label' => 'Product Types',
        'icon' => 'tags',
        'route' => 'catalog/product-types',
        'sort' => 20,
        'permission' => 'authenticated',
        'parent' => 'catalog',
        'enabled' => true,
        'coming_soon' => false,
        'children' => [],
    ],
    [
        'key' => 'catalog.products',
        'label' => 'Products',
        'icon' => 'package',
        'route' => 'catalog/products',
        'sort' => 30,
        'permission' => 'authenticated',
        'parent' => 'catalog',
        'enabled' => true,
        'coming_soon' => false,
        'children' => [],
    ],

    /**
     * Settings & Submenus.
     */
    [
        'key' => 'settings',
        'label' => 'Settings',
        'icon' => 'settings',
        'route' => 'settings/users',
        'sort' => 50,
        'permission' => 'authenticated',
        'children' => [],
    ],
    [
        'key' => 'settings.users',
        'label' => 'Users',
        'icon' => 'users',
        'route' => 'settings/users',
        'sort' => 10,
        'permission' => 'authenticated',
        'parent' => 'settings',
        'enabled' => true,
        'coming_soon' => false,
        'children' => [],
    ],
    [
        'key' => 'settings.roles',
        'label' => 'Roles',
        'icon' => 'shield-check',
        'route' => 'settings/roles/create',
        'sort' => 20,
        'permission' => 'authenticated',
        'parent' => 'settings',
        'enabled' => true,
        'coming_soon' => false,
        'children' => [],
    ],
];
