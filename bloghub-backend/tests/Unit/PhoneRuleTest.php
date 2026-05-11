<?php

namespace Tests\Unit;

use App\Rules\PhoneRule;
use PHPUnit\Framework\TestCase;

class PhoneRuleTest extends TestCase
{
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
}

