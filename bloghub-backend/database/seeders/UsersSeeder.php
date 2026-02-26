<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    private const SEED_USERS = [
        ['name' => 'Fox Mulder', 'username' => 'trust_no1', 'email' => 'trust_no1@gmail.com', 'is_creator' => true],
        ['name' => 'Dana Scully', 'username' => 'queequeg', 'email' => 'queequeg@gmail.com', 'is_creator' => true],
        ['name' => 'Gordon Freeman', 'username' => 'blackmesa', 'email' => 'blackmesa@gmail.com', 'is_creator' => true],
        ['name' => 'Gregory House', 'username' => 'ppth', 'email' => 'ppth@gmail.com', 'is_creator' => true],
        ['name' => 'Caroline', 'username' => 'glados', 'email' => 'glados@gmail.com', 'is_creator' => true],
        ['name' => 'Ellen Ripley', 'username' => 'nostromo', 'email' => 'nostromo@gmail.com', 'is_creator' => true],
        ['name' => 'Maggie Rhee', 'username' => 'laurenCohan', 'email' => 'laurenCohan@gmail.com', 'is_creator' => true],
        ['name' => 'Negan', 'username' => 'jeffreyDeanMorgan', 'email' => 'jeffreyDeanMorgan@gmail.com', 'is_creator' => true],
        ['name' => 'Carl Johnson', 'username' => 'grove4life', 'email' => 'grove4life@gmail.com', 'is_creator' => false],
        ['name' => 'Thomas A. Anderson', 'username' => 'neo', 'email' => 'neo@gmail.com', 'is_creator' => false],
        ['name' => 'Tiffany Zion', 'username' => 'trinity', 'email' => 'trinity@gmail.com', 'is_creator' => false],
    ];

    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate([
            'name' => UserRoleEnum::SuperAdmin->value,
            'guard_name' => 'web',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => UserRoleEnum::Admin->value,
            'guard_name' => 'web',
        ]);

        $superEmail = config('seed.super_admin.email');
        $superUsername = config('seed.super_admin.username');
        $superPassword = config('seed.super_admin.password');

        $adminEmail = config('seed.admin.email');
        $adminUsername = config('seed.admin.username');
        $adminPassword = config('seed.admin.password');

        $super = User::firstOrCreate(
            ['email' => $superEmail],
            [
                'name' => 'Super Admin',
                'username' => $superUsername,
                'phone' => '7123456789',
                'is_creator' => false,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'password' => Hash::make($superPassword),
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'username' => $adminUsername,
                'phone' => '420987654321',
                'is_creator' => false,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'password' => Hash::make($adminPassword),
            ]
        );

        if (! $super->hasRole($superAdminRole)) {
            $super->assignRole($superAdminRole);
        }

        if (! $admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }

        $seedPassword = Hash::make('app');
        foreach (self::SEED_USERS as $index => $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'phone' => '420'.str_pad((string) ($index + 1), 9, '0', STR_PAD_LEFT),
                    'is_creator' => $data['is_creator'],
                    'email_verified_at' => now(),
                    'terms_accepted_at' => now(),
                    'privacy_accepted_at' => now(),
                    'password' => $seedPassword,
                ]
            );
        }
    }
}
