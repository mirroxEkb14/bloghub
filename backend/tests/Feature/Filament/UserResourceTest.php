<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRoleEnum;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Models\User;
use App\Rules\PhoneRule;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionsSeeder::class);
    }

    public function test_admin_can_create_user_via_resource_form(): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'name' => 'Test User',
            'username' => 'test.user',
            'email' => 'test.user@example.com',
            'phone' => '420123456789',
            'password' => 'Password123!',
            'is_creator' => true,
        ];

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'username' => $payload['username'],
            'is_creator' => true,
        ]);
    }

    #[DataProvider('invalidPasswordProvider')]
    public function test_admin_cannot_create_user_with_invalid_password(string $password): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'name' => 'Test User',
            'username' => 'test.user',
            'email' => 'test.user@example.com',
            'phone' => '420123456789',
            'password' => $password,
            'is_creator' => true,
        ];

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['password']);
    }

    #[DataProvider('passwordContainingUserDataProvider')]
    public function test_admin_cannot_create_user_with_password_containing_user_data(string $password): void
    {
        $admin = $this->createAdminUser();

        $payload = [
            'name' => 'Test User',
            'username' => 'test.user',
            'email' => 'test.user@example.com',
            'phone' => '420123456789',
            'password' => $password,
            'is_creator' => true,
        ];

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['password']);
    }

    #[DataProvider('validPhoneProvider')]
    public function test_admin_can_save_valid_phone_numbers_on_edit(string $phone): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create([
            'username' => fake()->unique()->userName(),
        ]);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'username' => 'user.' . fake()->unique()->userName(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => $phone,
                'is_creator' => $user->is_creator,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(PhoneRule::normalize($phone), $user->refresh()->phone);
    }

    #[DataProvider('validEmailProvider')]
    public function test_admin_can_save_valid_email_addresses_on_edit(string $emailTemplate): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create([
            'username' => fake()->unique()->userName(),
        ]);

        $email = sprintf($emailTemplate, Str::lower(Str::random(6)));

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'username' => 'user.' . fake()->unique()->userName(),
                'email' => $email,
                'phone' => '420111222333',
                'is_creator' => $user->is_creator,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($email, $user->refresh()->email);
    }

    #[DataProvider('invalidEmailProvider')]
    public function test_admin_cannot_save_invalid_email_on_edit(string $email): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create([
            'username' => fake()->unique()->userName(),
        ]);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'username' => 'user.' . fake()->unique()->userName(),
                'email' => $email,
                'phone' => '420111222333',
                'is_creator' => $user->is_creator,
            ])
            ->call('save')
            ->assertHasFormErrors(['email']);
    }

    public function test_admin_cannot_save_invalid_phone_on_edit(): void
    {
        $admin = $this->createAdminUser();
        $user = User::factory()->create([
            'username' => fake()->unique()->userName(),
        ]);

        Livewire::actingAs($admin)
            ->test(EditUser::class, ['record' => $user->getRouteKey()])
            ->fillForm([
                'name' => $user->name,
                'username' => 'user.' . fake()->unique()->userName(),
                'email' => 'valid.user.' . fake()->unique()->safeEmail(),
                'phone' => 'invalid-phone',
                'is_creator' => $user->is_creator,
            ])
            ->call('save')
            ->assertHasFormErrors(['phone']);
    }

    public static function validPhoneProvider(): array
    {
        return [
            ['420111222333'],
            ['7 111 222 333'],
            ['49111 22 2333'],
            ['420-111-222-333'],
            ['7-111 222-333'],
            ['7 (111) 222 333'],
            ['7 (111) 222-333'],
            ['7 (701) 928-67-95'],
        ];
    }

    public static function validEmailProvider(): array
    {
        return [
            ['user.%s@example.com'],
            ['user.%s+tag@example.co.uk'],
            ['user_%s@example.io'],
        ];
    }

    public static function invalidEmailProvider(): array
    {
        return [
            ['not-an-email'],
            ['user@'],
            ['@example.com'],
            ['user@example'],
        ];
    }

    public static function invalidPasswordProvider(): array
    {
        return [
            ['S7!a'],
            ['password123!'],
            ['PASSWORD123!'],
            ['Password!!!!'],
            ['Password123'],
            ['12345678!'],
        ];
    }

    public static function passwordContainingUserDataProvider(): array
    {
        return [
            ['Test User123!'],
            ['test.user123!A'],
            ['test.user@example.com123!A'],
        ];
    }

    private function createAdminUser(): User
    {
        $admin = User::factory()->create([
            'username' => fake()->unique()->userName(),
        ]);

        $role = Role::where('name', UserRoleEnum::ADMIN->value)->firstOrFail();
        $admin->assignRole($role);

        return $admin;
    }
}
