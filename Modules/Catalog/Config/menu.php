<?php

return [
    [
        'key' => 'catalog',
        'label' => 'Catalog',
        'icon' => 'package',
        'route' => 'catalog.products',
        'sort' => 20,
        'permission' => 'authenticated',
        'children' => [
            [
                'key' => 'catalog.attributes',
                'label' => 'Attributes',
                'icon' => 'sliders-horizontal',
                'route' => 'catalog.attributes',
                'sort' => 10,
                'permission' => 'catalog.attributes.view',
            ],
            [
                'key' => 'catalog.product-types',
                'label' => 'Product Types',
                'icon' => 'tags',
                'route' => 'catalog.product-types',
                'sort' => 20,
                'permission' => 'authenticated',
                'enabled' => false,
                'coming_soon' => true,
            ],
            [
                'key' => 'catalog.products',
                'label' => 'Products',
                'icon' => 'package',
                'route' => 'catalog.products',
                'sort' => 30,
                'permission' => 'authenticated',
                'enabled' => false,
                'coming_soon' => true,
            ],
        ],
    ],
];
