<?php

namespace Tests\Support;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
trait SeedsPermissions
{
    protected function seedPermissions(): void
    {
        $this->seed(PermissionsSeeder::class);
    }

    protected function createSuperAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRoleEnum::SuperAdmin->value);

        return $user;
    }

    protected function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(UserRoleEnum::Admin->value);

        return $user;
    }

    protected function createUserWithoutPanelPermissions(): User
    {
        return User::factory()->create();
    }

    protected function assignPermission(User $user, string $permission): void
    {
        $user->givePermissionTo($permission);
    }
}
