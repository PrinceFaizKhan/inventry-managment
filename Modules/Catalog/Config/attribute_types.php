<?php

return [
    'text' => [
        'label' => 'Text',
        'settings' => [
            'validation' => ['label' => 'Validation', 'type' => 'select', 'default' => 'any', 'options' => ['any' => 'Any text', 'numeric' => 'Numeric', 'decimal' => 'Decimal', 'email' => 'Email', 'url' => 'URL', 'letters_only' => 'Letters only']],
            'min_length' => ['label' => 'Minimum text length', 'type' => 'integer', 'min' => 0, 'applies_to' => ['any', 'email', 'letters_only']],
            'max_length' => ['label' => 'Maximum text length', 'type' => 'integer', 'min' => 1, 'applies_to' => ['any', 'email', 'letters_only']],
            'min_value' => ['label' => 'Minimum value', 'type' => 'decimal', 'applies_to' => ['numeric', 'decimal']],
            'max_value' => ['label' => 'Maximum value', 'type' => 'decimal', 'applies_to' => ['numeric', 'decimal']],
            'decimal_precision' => ['label' => 'Decimal precision', 'type' => 'integer', 'min' => 0, 'max' => 8, 'applies_to' => ['decimal']],
        ],
    ],
    'textarea' => [
        'label' => 'Textarea',
        'settings' => [
            'rich_text' => ['label' => 'Enable rich-text editor', 'type' => 'boolean', 'default' => false],
            'min_length' => ['label' => 'Minimum text length', 'type' => 'integer', 'min' => 0],
            'max_length' => ['label' => 'Maximum text length', 'type' => 'integer', 'min' => 1],
        ],
    ],
    'boolean' => ['label' => 'Boolean', 'settings' => []],
    'checkbox' => ['label' => 'Checkbox', 'settings' => []],
    'select' => ['label' => 'Select', 'settings' => []],
    'multiselect' => ['label' => 'Multiselect', 'settings' => []],
    'image' => [
        'label' => 'Image',
        'settings' => [
            'allowed_formats' => ['label' => 'Allowed formats', 'type' => 'multi_select', 'options' => ['image/jpeg' => 'JPEG', 'image/png' => 'PNG', 'image/webp' => 'WebP']],
            'max_file_size_kb' => ['label' => 'Maximum file size (KB)', 'type' => 'integer', 'min' => 1, 'max' => 102400, 'default' => 5120],
        ],
    ],
    'gallery' => [
        'label' => 'Gallery',
        'settings' => [
            'allowed_formats' => ['label' => 'Allowed formats', 'type' => 'multi_select', 'options' => ['image/jpeg' => 'JPEG', 'image/png' => 'PNG', 'image/webp' => 'WebP']],
            'max_file_size_kb' => ['label' => 'Maximum file size per image (KB)', 'type' => 'integer', 'min' => 1, 'max' => 102400, 'default' => 5120],
            'max_images' => ['label' => 'Maximum number of images', 'type' => 'integer', 'min' => 1, 'max' => 100, 'default' => 10],
        ],
    ],
    'radio' => ['label' => 'Radio', 'settings' => []],
    'money' => [
        'label' => 'Money',
        'settings' => [
            'currency' => ['label' => 'Currency', 'type' => 'currency'],
            'decimal_precision' => ['label' => 'Decimal precision', 'type' => 'integer', 'min' => 0, 'max' => 8, 'default' => 2],
            'min_value' => ['label' => 'Minimum value', 'type' => 'decimal'],
            'max_value' => ['label' => 'Maximum value', 'type' => 'decimal'],
        ],
    ],
];
