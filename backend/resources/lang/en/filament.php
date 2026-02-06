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
    'content' => [
        'navigation_group' => 'Content',
    ],
    'creator_profiles' => [
        'navigation_label' => 'Creator Profiles',
        'model_label' => 'Creator Profile',
        'plural_label' => 'Creator Profiles',
        'form' => [
            'user_id' => 'User',
            'slug' => 'Slug',
            'display_name' => 'Display name',
            'about' => 'About',
            'profile_avatar_path' => 'Avatar',
            'profile_cover_path' => 'Cover',
            'slug_auto_hint' => 'auto-generated from Display name',
            'display_name_placeholder' => 'Ellen Ripley',
            'about_placeholder' => 'Short bio or description',
            'about_hint' => 'max. 255 characters',
        ],
        'table' => [
            'columns' => [
                'user' => 'User',
                'slug' => 'Slug',
                'display_name' => 'Display name',
                'about' => 'About',
                'posts_count' => 'Posts',
                'tiers_count' => 'Tiers',
                'created_at' => 'Created',
            ],
            'actions' => [
                'view' => 'View',
                'edit' => 'Edit',
                'delete' => 'Delete',
            ],
        ],
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
        'cannot_delete_yourself' => 'Cannot delete yourself',
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
                'delete' => 'Delete',
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
