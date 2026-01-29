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
    'roles' => [
        'navigation_group' => 'Panel Rolí',
    ],
    'users' => [
        'navigation_label' => 'Uživatelé',
        'model_label' => 'Uživatel',
        'plural_label' => 'Uživatelé',
        'actions' => [
            'create' => 'Vytvořit uživatele',
        ],
        'table' => [
            'columns' => [
                'name' => 'Jméno',
                'username' => 'Uživatelské jméno',
                'email' => 'E-mail',
                'phone' => 'Telefon',
                'is_creator' => 'Tvůrce',
                'roles' => 'Role',
                'created_at' => 'Vytvořeno',
            ],
            'actions' => [
                'view' => 'Zobrazit',
                'edit' => 'Upravit',
            ],
        ],
        'form' => [
            'is_creator' => 'Tvůrčí účet',
        ],
        'tabs' => [
            'all' => 'Všichni',
            'super_admins' => 'Super administrátoři',
            'admins' => 'Administrátoři',
            'others' => 'Ostatní',
        ],
    ],
];
