<?php

namespace Tests\Unit;

use App\Rules\EmailRule;
use Closure;
use Tests\TestCase;

class EmailRuleTest extends TestCase
{
    private function assertEmailRulePasses(mixed $value): void
    {
        $failed = false;
        $rule = new EmailRule();
        $rule->validate('email', $value, function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed, 'Expected email validation to pass');
    }

    private function assertEmailRuleFails(mixed $value, ?Closure $fail = null): void
    {
        $failed = false;
        $rule = new EmailRule();
        $rule->validate('email', $value, $fail ?? function () use (&$failed): void {
            $failed = true;
        });
        $this->assertTrue($failed, 'Expected email validation to fail');
    }

    public function test_validate_accepts_valid_email_addresses(): void
    {
        $this->assertEmailRulePasses('user@example.com');
        $this->assertEmailRulePasses('test.user+tag@sub.example.co.uk');
    }

    public function test_validate_rejects_invalid_email_addresses(): void
    {
        $this->assertEmailRuleFails('not-an-email');
        $this->assertEmailRuleFails('missing-at-sign.com');
        $this->assertEmailRuleFails('@no-local-part.com');
        $this->assertEmailRuleFails('spaces in@email.com');
    }

    public function test_validate_rejects_non_string_values(): void
    {
        $this->assertEmailRuleFails(null);
        $this->assertEmailRuleFails(123);
        $this->assertEmailRuleFails([]);
    }
}
