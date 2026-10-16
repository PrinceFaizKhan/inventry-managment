<?php

return [
    /**
     * Catalog ACL.
     */
    [
        'key' => 'catalog',
        'label' => 'Catalog',
        'children' => [
            [
                'key' => 'catalog.attributes',
                'label' => 'Attributes',
                'children' => [
                    [
                        'key' => 'catalog.attributes.view',
                        'label' => 'View',
                    ],
                    [
                        'key' => 'catalog.attributes.create',
                        'label' => 'Create',
                    ],
                    [
                        'key' => 'catalog.attributes.edit',
                        'label' => 'Edit',
                    ],
                    [
                        'key' => 'catalog.attributes.delete',
                        'label' => 'Delete',
                    ],
                ],
            ],
            [
                'key' => 'catalog.product-types',
                'label' => 'Product Types',
                'children' => [
                    [
                        'key' => 'catalog.product-types.view',
                        'label' => 'View',
                    ],
                    [
                        'key' => 'catalog.product-types.create',
                        'label' => 'Create',
                    ],
                    [
                        'key' => 'catalog.product-types.edit',
                        'label' => 'Edit',
                    ],
                    [
                        'key' => 'catalog.product-types.delete',
                        'label' => 'Delete',
                    ],
                ],
            ],
            [
                'key' => 'catalog.products',
                'label' => 'Products',
                'children' => [
                    [
                        'key' => 'catalog.products.view',
                        'label' => 'View',
                    ],
                    [
                        'key' => 'catalog.products.create',
                        'label' => 'Create',
                    ],
                    [
                        'key' => 'catalog.products.edit',
                        'label' => 'Edit',
                    ],
                    [
                        'key' => 'catalog.products.delete',
                        'label' => 'Delete',
                    ],
                ],
            ],
        ],
    ],

    /**
     * Settings ACL.
     */
    [
        'key' => 'settings',
        'label' => 'Settings',
        'children' => [
            [
                'key' => 'settings.users',
                'label' => 'Users',
                'children' => [
                    [
                        'key' => 'settings.users.view',
                        'label' => 'View',
                    ],
                    [
                        'key' => 'settings.users.create',
                        'label' => 'Create',
                    ],
                    [
                        'key' => 'settings.users.edit',
                        'label' => 'Edit',
                    ],
                    [
                        'key' => 'settings.users.delete',
                        'label' => 'Delete',
                    ],
                ],
            ],
            [
                'key' => 'settings.roles',
                'label' => 'Roles',
                'children' => [
                    [
                        'key' => 'settings.roles.view',
                        'label' => 'View',
                    ],
                    [
                        'key' => 'settings.roles.create',
                        'label' => 'Create',
                    ],
                    [
                        'key' => 'settings.roles.edit',
                        'label' => 'Edit',
                    ],
                    [
                        'key' => 'settings.roles.delete',
                        'label' => 'Delete',
                    ],
                ],
            ],
        ],
    ],
];
