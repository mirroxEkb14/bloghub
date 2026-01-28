<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        $rolePermissionNames = [
            'ViewAny:Role',
            'View:Role',
            'Create:Role',
            'Update:Role',
            'Delete:Role',
            'Restore:Role',
            'ForceDelete:Role',
            'ForceDeleteAny:Role',
            'RestoreAny:Role',
            'Replicate:Role',
            'Reorder:Role',
        ];

        foreach ($rolePermissionNames as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $allPermissions = Permission::query()->get();
        if ($allPermissions->isNotEmpty()) {
            $roleManagementPermissions = $allPermissions->filter(
                static fn (Permission $permission): bool => Str::endsWith($permission->name, ':Role')
            );

            $superAdminRole->syncPermissions($allPermissions);
            $adminRole->syncPermissions($allPermissions->diff($roleManagementPermissions));
        }

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
