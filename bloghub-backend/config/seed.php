<?php

return [
    'super_admin' => [
        'email' => env('SEED_SUPER_ADMIN_EMAIL', 'super@local.test'),
        'username' => env('SEED_SUPER_ADMIN_USERNAME', 'superadmin'),
        'password' => env('SEED_SUPER_ADMIN_PASSWORD', 'ChangeMe123!'),
    ],
    'admin' => [
        'email' => env('SEED_ADMIN_EMAIL', 'admin@local.test'),
        'username' => env('SEED_ADMIN_USERNAME', 'admin'),
        'password' => env('SEED_ADMIN_PASSWORD', 'ChangeMe123!'),
    ],
    'user' => [
        'email' => env('SEED_USER_EMAIL', 'user@local.test'),
        'username' => env('SEED_USER_USERNAME', 'user'),
        'password' => env('SEED_USER_PASSWORD', 'ChangeMe123!'),
    ],
];
