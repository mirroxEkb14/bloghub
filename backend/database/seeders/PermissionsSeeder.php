<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
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

        $permissionNames = [
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
            'ViewAny:User',
            'View:User',
            'Create:User',
            'Update:User',
            'Delete:User',
            'Restore:User',
            'ForceDelete:User',
            'ForceDeleteAny:User',
            'RestoreAny:User',
            'Replicate:User',
            'Reorder:User',
        ];

        foreach ($permissionNames as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $allPermissions = Permission::query()->get();

        if ($allPermissions->isEmpty()) {
            return;
        }

        $roleManagementPermissions = $allPermissions->filter(
            static fn (Permission $permission): bool => Str::endsWith($permission->name, ':Role')
        );

        $superAdminRole->syncPermissions($allPermissions);
        $adminRole->syncPermissions($allPermissions->diff($roleManagementPermissions));
    }
}
