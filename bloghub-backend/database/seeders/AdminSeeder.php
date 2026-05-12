<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPrivilegedUser(
            UserRoleEnum::SuperAdmin,
            'seed.super_admin',
            'Super Admin',
            '77011234567',
        );

        $this->seedPrivilegedUser(
            UserRoleEnum::Admin,
            'seed.admin',
            'Admin',
            '77011234568',
        );
    }

    private function seedPrivilegedUser(UserRoleEnum $role, string $configKey, string $displayName, string $phone): void
    {
        $roleModel = Role::query()
            ->where('name', $role->value)
            ->where('guard_name', 'web')
            ->firstOrFail();

        $email = config("{$configKey}.email");
        $username = config("{$configKey}.username");
        $password = config("{$configKey}.password");

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $displayName,
                'username' => $username,
                'phone' => $phone,
                'is_creator' => false,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
                'password' => Hash::make($password),
            ]
        );

        if (! $user->hasRole($roleModel)) {
            $user->assignRole($roleModel);
        }
    }
}
