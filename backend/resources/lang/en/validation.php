<?php

return [
    'email' => 'The :attribute field must be a valid email address',
    'phone' => 'The :attribute field format is invalid',
    'unique' => 'The :attribute has already been taken',
    'password' => [
        'letters' => 'The :attribute must contain at least one letter',
        'mixed' => 'The :attribute must contain at least one uppercase and one lowercase letter',
        'numbers' => 'The :attribute must contain at least one number',
        'symbols' => 'The :attribute must contain at least one special character',
        'uncompromised' => 'The :attribute has appeared in a data leak. Please choose a different :attribute',
        'min' => 'The :attribute must be at least :min characters',
    ],
    'password_contains_user_data' => 'The :attribute cannot contain your :field',
    'attributes' => [
        'password' => 'password',
        'data.password' => 'password',
        'email' => 'email',
        'data.email' => 'email',
        'username' => 'username',
        'data.username' => 'username',
        'name' => 'name',
        'data.name' => 'name',
    ],
];
