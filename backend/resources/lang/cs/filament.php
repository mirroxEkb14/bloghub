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
        'saved' => 'Profil byl aktualizován',
    ],
    'roles' => [
        'navigation_group' => 'Panel Rolí',
    ],
    'content' => [
        'navigation_group' => 'Obsah',
    ],
    'creator_profiles' => [
        'navigation_label' => 'Profily tvůrců',
        'model_label' => 'Profil tvůrce',
        'plural_label' => 'Profily tvůrců',
        'form' => [
            'user_id' => 'Uživatel',
            'slug' => 'Slug',
            'display_name' => 'Zobrazované jméno',
            'about' => 'O mně',
            'profile_avatar_path' => 'Avatar',
            'profile_cover_path' => 'Úvodní obrázek',
            'slug_auto_hint' => 'vygenerováno automaticky ze Zobrazovaného jména',
            'display_name_placeholder' => 'Dr. Gregory House',
            'about_placeholder' => 'Krátký popis nebo bio',
            'about_hint' => 'max. 255 znaků',
        ],
        'table' => [
            'columns' => [
                'user' => 'Uživatel',
                'slug' => 'Slug',
                'display_name' => 'Zobrazované jméno',
                'about' => 'O mně',
                'posts_count' => 'Příspěvky',
                'tiers_count' => 'Úrovně',
                'created_at' => 'Vytvořeno',
            ],
            'actions' => [
                'view' => 'Zobrazit',
                'edit' => 'Upravit',
                'delete' => 'Smazat',
            ],
        ],
    ],
    'users' => [
        'navigation_label' => 'Uživatelé',
        'model_label' => 'Uživatele',
        'plural_label' => 'Uživatelé',
        'cannot_delete_yourself' => 'Nemůžete smazat sám sebe',
        'form' => [
            'name' => 'Jméno',
            'username' => 'Uživatelské jméno',
            'email' => 'E-mail',
            'phone' => 'Telefon',
            'password' => 'Heslo',
            'is_creator' => 'Tvůrce',
            'name_placeholder' => 'Dana Scullyová',
            'username_placeholder' => 'queequeg_1851',
            'email_placeholder' => 'queequeg@gmail.com',
            'phone_placeholder' => '1 123 456 789',
            'name_helper' => '100 znaků max',
            'username_helper' => '50 znaků max',
            'email_helper' => '255 znaků max, musí být platná e-mailová adresa',
            'phone_helper' => '+420123456789, +7 (987) 654 32 10, +49-456-987-321',
            'password_helper' => '8 znaků, malé i velké písmeno, číslici a speciální znak',
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
                'delete' => 'Smazat',
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
