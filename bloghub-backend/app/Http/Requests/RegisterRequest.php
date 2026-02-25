<?php

namespace App\Http\Requests;

use App\Rules\EmailRule;
use App\Rules\PhoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users', 'username'),
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                new EmailRule,
                Rule::unique('users', 'email'),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', new PhoneRule],
            'terms_accepted' => ['nullable', 'boolean'],
            'privacy_accepted' => ['nullable', 'boolean'],
        ];
    }
}
