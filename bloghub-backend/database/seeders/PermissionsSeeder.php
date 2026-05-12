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

        $resourceModels = [
            'Role', 'User', 'CreatorProfile', 'Tier', 'Comment', 'Post', 'Subscription', 'Payment', 'Tag',
        ];
        $resourceActions = [
            'ViewAny', 'View', 'Create', 'Update', 'Delete', 'Restore', 'ForceDelete',
            'ForceDeleteAny', 'RestoreAny', 'Replicate', 'Reorder',
        ];

        $permissionNames = [];
        foreach ($resourceModels as $model) {
            foreach ($resourceActions as $action) {
                $permissionNames[] = $action.':'.$model;
            }
        }
        $permissionNames = array_merge($permissionNames, [
            'View:Profile',
            'View:OverviewStatsWidget',
            'View:PaymentStatsOverviewWidget',
            'View:PaymentsChartWidget',
        ]);

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
