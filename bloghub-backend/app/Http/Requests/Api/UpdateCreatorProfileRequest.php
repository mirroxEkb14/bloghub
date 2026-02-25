<?php

namespace App\Http\Requests\Api;

use App\Models\CreatorProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCreatorProfileRequest extends FormRequest
{
    private const SLUG_REGEX = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function authorize(): bool
    {
        $profile = $this->route('creatorProfile');
        return $profile instanceof CreatorProfile
            && $this->user()?->creatorProfile?->id === $profile->id;
    }

    public function rules(): array
    {
        $profile = $this->route('creatorProfile');
        $slugUnique = Rule::unique('creator_profiles', 'slug');
        if ($profile instanceof CreatorProfile) {
            $slugUnique->ignore($profile->id);
        }

        return [
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                $slugUnique,
                'regex:' . self::SLUG_REGEX,
            ],
            'display_name' => ['sometimes', 'required', 'string', 'max:50'],
            'about' => ['nullable', 'string', 'max:255'],
            'profile_avatar_path' => ['nullable', 'string', 'max:255'],
            'profile_cover_path' => ['nullable', 'string', 'max:255'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', Rule::exists('tags', 'id')],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => __('messages.slug_invalid'),
        ];
    }
}
