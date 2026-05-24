<?php

namespace Tests\Unit;

use App\Rules\PhoneRule;
use Closure;
use Tests\TestCase;

class PhoneRuleTest extends TestCase
{
    private function assertPhoneRulePasses(mixed $value): void
    {
        $failed = false;
        $rule = new PhoneRule();
        $rule->validate('phone', $value, function () use (&$failed): void {
            $failed = true;
        });
        $this->assertFalse($failed, 'Expected phone validation to pass');
    }

    private function assertPhoneRuleFails(mixed $value, ?Closure $fail = null): void
    {
        $failed = false;
        $rule = new PhoneRule();
        $rule->validate('phone', $value, $fail ?? function () use (&$failed): void {
            $failed = true;
        });
        $this->assertTrue($failed, 'Expected phone validation to fail');
    }

    public function test_normalize_returns_null_for_null_or_blank(): void
    {
        $this->assertNull(PhoneRule::normalize(null));
        $this->assertNull(PhoneRule::normalize(''));
        $this->assertNull(PhoneRule::normalize('   '));
        $this->assertNull(PhoneRule::normalize("\n\t"));
    }

    public function test_normalize_strips_separators_and_prepends_plus(): void
    {
        $this->assertSame('+420111222333', PhoneRule::normalize('420111222333'));
        $this->assertSame('+420111222333', PhoneRule::normalize('+420111222333'));
        $this->assertSame('+420111222333', PhoneRule::normalize('420 111 222 333'));
        $this->assertSame('+420111222333', PhoneRule::normalize('420-111-222-333'));
        $this->assertSame('+420111222333', PhoneRule::normalize('(420) 111 222 333'));
        $this->assertSame('+420111222333', PhoneRule::normalize('+420 (111) 222-333'));
    }

    public function test_normalize_returns_null_for_disallowed_characters_or_no_digits(): void
    {
        $this->assertNull(PhoneRule::normalize('invalid-phone'));
        $this->assertNull(PhoneRule::normalize('+420-abc-123'));
        $this->assertNull(PhoneRule::normalize('()--'));
        $this->assertNull(PhoneRule::normalize('+'));
    }

    public function test_validate_accepts_null_or_empty_without_failing(): void
    {
        $this->assertPhoneRulePasses(null);
        $this->assertPhoneRulePasses('');
    }

    public function test_validate_accepts_valid_e164_phone_numbers(): void
    {
        $this->assertPhoneRulePasses('+420111222333');
        $this->assertPhoneRulePasses('420 111 222 333');
        $this->assertPhoneRulePasses('+1 (555) 123-4567');
    }

    public function test_validate_rejects_invalid_phone_numbers(): void
    {
        $this->assertPhoneRuleFails('not-a-phone');
        $this->assertPhoneRuleFails('+123');
        $this->assertPhoneRuleFails('12345678901234567');
        $this->assertPhoneRuleFails(1234567890);
    }
}

