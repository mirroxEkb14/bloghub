<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PasswordNotContainingUserData implements ValidationRule
{
    /**
     * @param array<string, array{value: string|null, label: string}> $fields
     */
    public function __construct(private readonly array $fields)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $password = mb_strtolower((string) $value);

        foreach ($this->fields as $field) {
            $valueString = trim((string) ($field['value'] ?? ''));

            if ($valueString === '') {
                continue;
            }

            $lowerValue = mb_strtolower($valueString);

            if (str_contains($password, $lowerValue)) {
                $fail(__('validation.password_contains_user_data', [
                    'attribute' => $attribute,
                    'field' => $field['label'],
                ]));
                break;
            }
        }
    }
}
