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
            'name' => UserRoleEnum::SuperAdmin->value,
            'guard_name' => 'web',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => UserRoleEnum::Admin->value,
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
            'ViewAny:CreatorProfile',
            'View:CreatorProfile',
            'Create:CreatorProfile',
            'Update:CreatorProfile',
            'Delete:CreatorProfile',
            'Restore:CreatorProfile',
            'ForceDelete:CreatorProfile',
            'ForceDeleteAny:CreatorProfile',
            'RestoreAny:CreatorProfile',
            'Replicate:CreatorProfile',
            'Reorder:CreatorProfile',
            'ViewAny:Tier',
            'View:Tier',
            'Create:Tier',
            'Update:Tier',
            'Delete:Tier',
            'Restore:Tier',
            'ForceDelete:Tier',
            'ForceDeleteAny:Tier',
            'RestoreAny:Tier',
            'Replicate:Tier',
            'Reorder:Tier',
            'ViewAny:Comment',
            'View:Comment',
            'Create:Comment',
            'Update:Comment',
            'Delete:Comment',
            'Restore:Comment',
            'ForceDelete:Comment',
            'ForceDeleteAny:Comment',
            'RestoreAny:Comment',
            'Replicate:Comment',
            'Reorder:Comment',
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
