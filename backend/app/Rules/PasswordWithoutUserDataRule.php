<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Filament\Schemas\Components\Utilities\Get;

readonly class PasswordWithoutUserDataRule implements ValidationRule
{
    public function __construct(private array $fields)
    {
    }

    public static function fromGet(Get $get): self
    {
        return new self([
            'email' => [
                'value' => $get('email'),
                'label' => __('filament.users.form.email'),
            ],
            'username' => [
                'value' => $get('username'),
                'label' => __('filament.users.form.username'),
            ],
            'name' => [
                'value' => $get('name'),
                'label' => __('filament.users.form.name'),
            ],
        ]);
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
