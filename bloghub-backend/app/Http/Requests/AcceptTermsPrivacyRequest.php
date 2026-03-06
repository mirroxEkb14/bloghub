<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcceptTermsPrivacyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terms_accepted' => ['required', 'boolean', 'accepted'],
            'privacy_accepted' => ['required', 'boolean', 'accepted'],
        ];
    }
}
