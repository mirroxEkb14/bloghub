<?php

return [
    'profile' => [
        'navigation_label' => 'Profile',
        'language_label' => 'Language',
        'language_options' => [
            'en' => 'English',
            'cz' => 'Czech',
        ],
        'save' => 'Save',
        'saved' => 'Profile updated.',
        'missing_locale_column' => 'Locale column is missing. Please run database migrations.',
    ],
    'users' => [
        'navigation_label' => 'Users',
        'navigation_group' => 'Role Panel',
        'table' => [
            'columns' => [
                'name' => 'Name',
                'username' => 'Username',
                'email' => 'Email',
                'phone' => 'Phone',
                'roles' => 'Roles',
                'created_at' => 'Created',
            ],
            'actions' => [
                'view' => 'View',
                'edit' => 'Edit',
            ],
        ],
        'tabs' => [
            'all' => 'All',
            'super_admins' => 'Super Admins',
            'admins' => 'Admins',
            'others' => 'Others',
        ],
    ],
];
