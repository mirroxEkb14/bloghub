<?php

return [
    'required' => 'Toto pole je povinné',
    'max' => [
        'string' => 'Text je příliš dlouhý (max. :max znaků)',
    ],
    'unique' => 'Tato hodnota je již obsazena',
    'phone' => 'Telefon musí být ve správném formátu',
    'email' => 'Zadejte platnou e-mailovou adresu',
    'password' => [
        'letters' => 'Heslo musí obsahovat alespoň jedno písmeno',
        'mixed' => 'Heslo musí obsahovat alespoň jedno velké a jedno malé písmeno',
        'numbers' => 'Heslo musí obsahovat alespoň jednu číslici',
        'symbols' => 'Heslo musí obsahovat alespoň jeden speciální znak',
        'uncompromised' => 'Zvolené heslo se objevilo v úniku dat, zvolte jiné',
    ],
    'password_contains_user_data' => 'Heslo nesmí obsahovat hodnotu „:field“',
    'custom' => [
        'data.name' => [
            'required' => 'Jméno je povinné',
            'max' => 'Jméno může mít maximálně :max znaků',
        ],
        'data.username' => [
            'required' => 'Uživatelské jméno je povinné',
            'unique' => 'Uživatelské jméno je již obsazené',
            'max' => 'Uživatelské jméno může mít maximálně :max znaků',
        ],
        'data.email' => [
            'required' => 'E-mail je povinný',
            'email' => 'E-mail musí být platná e-mailová adresa',
            'unique' => 'E-mail je již obsazený',
            'max' => 'E-mail může mít maximálně :max znaků',
        ],
        'data.phone' => [
            'phone' => 'Telefon musí být ve správném formátu',
            'regex' => 'Telefon musí být ve správném formátu',
            'max' => 'Telefon může mít maximálně :max znaků',
        ],
        'data.password' => [
            'required' => 'Heslo je povinné',
            'min' => 'Heslo musí mít alespoň :min znaků',
        ],
    ],
];
