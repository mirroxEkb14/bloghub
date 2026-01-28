<?php

return [
    'profile' => [
        'navigation_label' => 'Profil',
        'language_label' => 'Jazyk',
        'language_options' => [
            'en' => 'Angličtina',
            'cs' => 'Čeština',
        ],
        'save' => 'Uložit',
        'saved' => 'Profil byl aktualizován.',
    ],
    'navigation_groups' => [
        'role_panel' => 'Panel rolí',
    ],
    'users' => [
        'navigation_label' => 'Uživatelé',
        'table' => [
            'columns' => [
                'name' => 'Jméno',
                'username' => 'Uživatelské jméno',
                'email' => 'E-mail',
                'phone' => 'Telefon',
                'roles' => 'Role',
                'created_at' => 'Vytvořeno',
            ],
            'actions' => [
                'view' => 'Zobrazit',
                'edit' => 'Upravit',
            ],
        ],
        'tabs' => [
            'all' => 'Všichni',
            'super_admins' => 'Super administrátoři',
            'admins' => 'Administrátoři',
            'others' => 'Ostatní',
        ],
    ],
];
