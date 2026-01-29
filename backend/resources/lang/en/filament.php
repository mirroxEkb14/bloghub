<?php

return [
    'profile' => [
        'navigation_label' => 'Profile',
        'language_label' => 'Language',
        'language_options' => [
            'en' => 'English',
            'cs' => 'Czech',
        ],
        'save' => 'Save',
        'saved' => 'Profile updated',
    ],
    'roles' => [
        'navigation_group' => 'Role Panel',
    ],
    'users' => [
        'navigation_label' => 'Users',
        'model_label' => 'User',
        'plural_label' => 'Users',
        'form' => [
            'name' => 'Name',
            'username' => 'Username',
            'email' => 'Email',
            'phone' => 'Phone',
            'password' => 'Password',
            'is_creator' => 'Creator',
        ],
        'table' => [
            'columns' => [
                'name' => 'Name',
                'username' => 'Username',
                'email' => 'Email',
                'phone' => 'Phone',
                'is_creator' => 'Creator',
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
