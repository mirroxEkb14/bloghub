<?php

namespace Tests\Unit;

use App\Rules\PasswordWithoutUserDataRule;
use Closure;
use Tests\TestCase;

class PasswordWithoutUserDataRuleTest extends TestCase
{
    private function makeRule(array $fields): PasswordWithoutUserDataRule
    {
        return new PasswordWithoutUserDataRule($fields);
    }

    private function assertPasswordRulePasses(PasswordWithoutUserDataRule $rule, string $password): void
    {
        $failed = false;
        $rule->validate('password', $password, function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed, 'Expected password validation to pass');
    }

    private function assertPasswordRuleFails(PasswordWithoutUserDataRule $rule, string $password, ?Closure $fail = null): void
    {
        $failed = false;
        $rule->validate('password', $password, $fail ?? function () use (&$failed): void {
            $failed = true;
        });
        $this->assertTrue($failed, 'Expected password validation to fail');
    }

    public function test_validate_passes_when_password_does_not_contain_user_data(): void
    {
        $rule = $this->makeRule([
            'email' => ['value' => 'alice@example.com', 'label' => 'Email'],
            'username' => ['value' => 'alice_creator', 'label' => 'Username'],
            'name' => ['value' => 'Alice Smith', 'label' => 'Name'],
        ]);

        $this->assertPasswordRulePasses($rule, 'Str0ng!UniquePass');
    }

    public function test_validate_fails_when_password_contains_email(): void
    {
        $rule = $this->makeRule([
            'email' => ['value' => 'secret@example.com', 'label' => 'Email'],
            'username' => ['value' => 'creator', 'label' => 'Username'],
            'name' => ['value' => 'Jane', 'label' => 'Name'],
        ]);

        $this->assertPasswordRuleFails($rule, 'Uses-secret@example.com!');
    }

    public function test_validate_fails_when_password_contains_username(): void
    {
        $rule = $this->makeRule([
            'email' => ['value' => 'user@example.com', 'label' => 'Email'],
            'username' => ['value' => 'my_username', 'label' => 'Username'],
            'name' => ['value' => 'Jane', 'label' => 'Name'],
        ]);

        $this->assertPasswordRuleFails($rule, 'my_usernameIsWeak1');
    }

    public function test_validate_fails_when_password_contains_name(): void
    {
        $rule = $this->makeRule([
            'email' => ['value' => 'user@example.com', 'label' => 'Email'],
            'username' => ['value' => 'creator', 'label' => 'Username'],
            'name' => ['value' => 'Jonathan', 'label' => 'Name'],
        ]);

        $this->assertPasswordRuleFails($rule, 'Jonathan123!');
    }

    public function test_validate_is_case_insensitive(): void
    {
        $rule = $this->makeRule([
            'email' => ['value' => 'user@example.com', 'label' => 'Email'],
            'username' => ['value' => 'CreatorX', 'label' => 'Username'],
            'name' => ['value' => '', 'label' => 'Name'],
        ]);

        $this->assertPasswordRuleFails($rule, 'containsCREATORx1!');
    }

    public function test_validate_skips_empty_field_values(): void
    {
        $rule = $this->makeRule([
            'email' => ['value' => '', 'label' => 'Email'],
            'username' => ['value' => '   ', 'label' => 'Username'],
            'name' => ['value' => null, 'label' => 'Name'],
        ]);

        $this->assertPasswordRulePasses($rule, 'ValidPass123!');
    }

    public function test_validate_uses_field_label_in_failure_message(): void
    {
        $rule = $this->makeRule([
            'email' => ['value' => 'user@example.com', 'label' => 'Email'],
            'username' => ['value' => 'unique_handle', 'label' => 'Custom Username Label'],
            'name' => ['value' => '', 'label' => 'Name'],
        ]);

        $capturedMessage = null;
        $rule->validate('password', 'unique_handlePass1', function (string $message) use (&$capturedMessage): void {
            $capturedMessage = $message;
        });

        $this->assertNotNull($capturedMessage);
        $this->assertStringContainsString('Custom Username Label', $capturedMessage);
    }
}
