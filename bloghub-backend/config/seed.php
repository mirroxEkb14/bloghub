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
];
