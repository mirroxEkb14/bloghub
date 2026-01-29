<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail(__('validation.phone'));
            return;
        }

        $normalized = self::normalize($value);

        if ($normalized === null || !preg_match('/^\+[1-9]\d{7,14}$/', $normalized)) {
            $fail(__('validation.phone'));
        }
    }

    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if (!str_starts_with($trimmed, '+')) {
            return null;
        }

        if (!preg_match('/^[+\d\s\-()]+$/u', $trimmed)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $trimmed);
        if ($digits === null || $digits === '') {
            return null;
        }

        return '+' . $digits;
    }
}
