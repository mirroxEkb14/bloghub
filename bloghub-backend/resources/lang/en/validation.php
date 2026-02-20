<?php

return [
    'attributes' => require __DIR__ . '/attributes.php',

    'required' => 'This field is required',
    'max' => [
        'string' => 'This value is too long (max :max characters)',
    ],
    'unique' => 'This value has already been taken',
    'phone' => 'Phone number format is invalid',
    'email' => 'Please enter a valid email address',
    'password' => [
        'letters' => 'Password must contain at least one letter',
        'mixed' => 'Password must contain at least one uppercase and one lowercase letter',
        'numbers' => 'Password must contain at least one number',
        'symbols' => 'Password must contain at least one special character',
        'uncompromised' => 'This password has appeared in a data leak, please choose a different one',
    ],
    'password_contains_user_data' => 'Password cannot contain “:field”',
    'custom' => [
        'data.name' => [
            'required' => 'Name is required',
            'max' => 'Name may not be greater than :max characters',
        ],
        'data.username' => [
            'required' => 'Username is required',
            'unique' => 'Username has already been taken',
            'max' => 'Username may not be greater than :max characters',
        ],
        'data.email' => [
            'required' => 'Email is required',
            'email' => 'Email must be a valid email address',
            'unique' => 'Email has already been taken',
            'max' => 'Email may not be greater than :max characters',
        ],
        'data.phone' => [
            'phone' => 'Phone number format is invalid',
            'regex' => 'Phone number format is invalid',
            'max' => 'Phone number may not be greater than :max characters',
        ],
        'data.password' => [
            'required' => 'Password is required',
            'min' => 'Password must be at least :min characters',
        ],
    ],
    'regex' => 'This format is invalid',
];
