<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    private const FIXTURES_AVATARS = 'database/seeders/fixtures/avatars';

    private const STORAGE_AVATAR_DIR = 'users/avatars';

    private const SEED_USERS = [
        ['name' => 'Fox Mulder', 'username' => 'trust_no1', 'email' => 'trust_no1@gmail.com', 'is_creator' => true, 'phone' => '12025550141'],
        ['name' => 'Dana Scully', 'username' => 'queequeg', 'email' => 'queequeg@gmail.com', 'is_creator' => true, 'phone' => '13015550142'],
        ['name' => 'Gordon Freeman', 'username' => 'blackmesa', 'email' => 'blackmesa@gmail.com', 'is_creator' => true, 'phone' => '15055550143'],
        ['name' => 'Gregory House', 'username' => 'ppth', 'email' => 'ppth@gmail.com', 'is_creator' => true, 'phone' => '16095550144'],
        ['name' => 'Caroline', 'username' => 'glados', 'email' => 'glados@gmail.com', 'is_creator' => true, 'phone' => '13135550145'],
        ['name' => 'Ellen Ripley', 'username' => 'nostromo', 'email' => 'nostromo@gmail.com', 'is_creator' => true, 'phone' => '15125550146'],
        ['name' => 'Maggie Rhee', 'username' => 'laurenCohan', 'email' => 'laurenCohan@gmail.com', 'is_creator' => true, 'phone' => '17035550147'],
        ['name' => 'Negan', 'username' => 'jeffreyDeanMorgan', 'email' => 'jeffreyDeanMorgan@gmail.com', 'is_creator' => true, 'phone' => '15405550148'],
        ['name' => 'Carl Johnson', 'username' => 'grove4life', 'email' => 'grove4life@gmail.com', 'is_creator' => false, 'phone' => '13235550149'],
        ['name' => 'Thomas A. Anderson', 'username' => 'neo', 'email' => 'neo@gmail.com', 'is_creator' => false, 'phone' => '13125550150'],
        ['name' => 'Trinity Zion', 'username' => 'trinity', 'email' => 'trinity@gmail.com', 'is_creator' => false, 'phone' => '14155550151'],
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
                'phone' => '77011234567',
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
                'phone' => '420732123456',
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
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'phone' => $data['phone'],
                    'is_creator' => $data['is_creator'],
                    'email_verified_at' => now(),
                    'terms_accepted_at' => now(),
                    'privacy_accepted_at' => now(),
                    'password' => $seedPassword,
                ]
            );
            $this->copyUserAvatar($user);
        }
    }

    private function copyUserAvatar(User $user): void
    {
        $fixturePath = base_path(self::FIXTURES_AVATARS).DIRECTORY_SEPARATOR.$user->username.'.png';
        if (! is_file($fixturePath)) {
            return;
        }

        $stored = Storage::disk('public')->putFileAs(
            self::STORAGE_AVATAR_DIR,
            new File($fixturePath),
            $user->username.'.png'
        );
        $user->update(['avatar_path' => $stored]);
    }
}
