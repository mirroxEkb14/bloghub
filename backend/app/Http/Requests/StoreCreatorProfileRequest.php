<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCreatorProfileRequest extends FormRequest
{
    private const SLUG_REGEX = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('creator_profiles', 'slug'),
                'regex:' . self::SLUG_REGEX,
            ],
            'display_name' => ['required', 'string', 'max:50'],
            'about' => ['nullable', 'string', 'max:255'],
            'profile_avatar_path' => ['nullable', 'string', 'max:255'],
            'profile_cover_path' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => __('messages.slug_invalid'),
        ];
    }
}
