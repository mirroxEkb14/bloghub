<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class PasswordNotContainingUserData implements ValidationRule
{
    /**
     * @param array<string, array{value: string|null, label: string}> $fields
     */
    public function __construct(private array $fields)
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

            if (str_contains($password, mb_strtolower($valueString))) {
                $fail(__('validation.password_contains_user_data', [
                    'field' => $field['label'],
                ]));
                break;
            }
        }
    }
}
