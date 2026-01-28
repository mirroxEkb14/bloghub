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

        $this->call([
            RolePermissionsSeeder::class,
            UserPermissionsSeeder::class,
        ]);

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
