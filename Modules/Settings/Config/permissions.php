<?php

return [
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
