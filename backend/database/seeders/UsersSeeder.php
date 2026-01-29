<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::firstOrCreate([
            'name' => UserRoleEnum::SUPER_ADMIN->value,
            'guard_name' => 'web',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => UserRoleEnum::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $superEmail = config('seed.super_admin.email');
        $superUsername = config('seed.super_admin.username');
        $superPassword = config('seed.super_admin.password');

        $adminEmail = config('seed.admin.email');
        $adminUsername = config('seed.admin.username');
        $adminPassword = config('seed.admin.password');

        $userEmail = config('seed.user.email');
        $userUsername = config('seed.user.username');
        $userPassword = config('seed.user.password');

        $super = User::firstOrCreate(
            ['email' => $superEmail],
            [
                'name' => 'Super Admin',
                'username' => $superUsername,
                'phone' => '+7123456789',
                'is_creator' => false,
                'email_verified_at' => now(),
                'password' => Hash::make($superPassword),
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'username' => $adminUsername,
                'phone' => '+420987654321',
                'is_creator' => false,
                'email_verified_at' => now(),
                'password' => Hash::make($adminPassword),
            ]
        );

        $user = User::firstOrCreate(
            ['email' => $userEmail],
            [
                'name' => 'User',
                'username' => $userUsername,
                'phone' => '+49456123789',
                'is_creator' => true,
                'email_verified_at' => now(),
                'password' => Hash::make($userPassword),
            ]
        );

        if (! $super->hasRole($superAdminRole)) {
            $super->assignRole($superAdminRole);
        }

        if (! $admin->hasRole($adminRole)) {
            $admin->assignRole($adminRole);
        }
    }
}
