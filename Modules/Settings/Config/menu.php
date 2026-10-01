<?php

return [
    [
        'key' => 'settings',
        'label' => 'Settings',
        'icon' => 'settings',
        'route' => 'users',
        'sort' => 50,
        'permission' => 'authenticated',
        'children' => [
            [
                'key' => 'settings.users',
                'label' => 'Users',
                'icon' => 'users',
                'route' => 'users',
                'sort' => 10,
                'permission' => 'authenticated',
            ],
            [
                'key' => 'settings.roles',
                'label' => 'Roles',
                'icon' => 'shield-check',
                'route' => 'roles',
                'sort' => 20,
                'permission' => 'authenticated',
            ],
        ],
    ],
];
