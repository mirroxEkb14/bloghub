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
            'name_placeholder' => 'Fox Mulder',
            'username_placeholder' => 'trust_no1',
            'email_placeholder' => 'trust_no1@gmail.com',
            'phone_placeholder' => '1 123 456 789',
            'name_helper' => '100 characters max',
            'username_helper' => '50 chars max',
            'email_helper' => '255 chars max, must be a valid email address',
            'phone_helper' => '+420123456789, +7 (987) 654 32 10, +49-456-987-321',
            'password_helper' => '8 chars, upper- & lowercase letters, a number, a special char',
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
