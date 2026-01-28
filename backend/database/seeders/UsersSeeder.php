<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdminRole = Role::firstOrCreate([
            'name' => UserRoleEnum::SUPER_ADMIN->value,
            'guard_name' => 'web',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => UserRoleEnum::ADMIN->value,
            'guard_name' => 'web',
        ]);

        $permissions = Permission::query()->get();
        $superAdminRole->syncPermissions($permissions);
        $adminRole->syncPermissions(
            $permissions->reject(fn (Permission $permission) => str_ends_with($permission->name, ':Role'))
        );

        $superEmail = env('SEED_SUPER_ADMIN_EMAIL', 'super@local.test');
        $superUsername = env('SEED_SUPER_ADMIN_USERNAME', 'superadmin');
        $superPassword = env('SEED_SUPER_ADMIN_PASSWORD', 'ChangeMe123!');

        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@local.test');
        $adminUsername = env('SEED_ADMIN_USERNAME', 'admin');
        $adminPassword = env('SEED_ADMIN_PASSWORD', 'ChangeMe123!');

        $userEmail = env('SEED_USER_EMAIL', 'user@local.test');
        $userUsername = env('SEED_USER_USERNAME', 'user');
        $userPassword = env('SEED_USER_PASSWORD', 'ChangeMe123!');

        $super = User::firstOrCreate(
            ['email' => $superEmail],
            [
                'name' => 'Super Admin',
                'username' => $superUsername,
                'phone' => null,
                'email_verified_at' => now(),
                'password' => Hash::make($superPassword),
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'username' => $adminUsername,
                'phone' => null,
                'email_verified_at' => now(),
                'password' => Hash::make($adminPassword),
            ]
        );

        $user = User::firstOrCreate(
            ['email' => $userEmail],
            [
                'name' => 'User',
                'username' => $userUsername,
                'phone' => null,
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
