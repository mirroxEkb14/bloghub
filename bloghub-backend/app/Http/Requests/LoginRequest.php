<?php

namespace App\Http\Requests;

use App\Rules\EmailRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', new EmailRule],
            'password' => ['required', 'string'],
        ];
    }
}
