<?php

namespace App\Http\Requests\Api;

use App\Models\CreatorProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCreatorProfileRequest extends FormRequest
{
    private const SLUG_REGEX = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->creatorProfile === null;
    }

    public function rules(): array
    {
        $slugRules = [
            'nullable',
            'string',
            'max:255',
            'regex:' . self::SLUG_REGEX,
            Rule::unique('creator_profiles', 'slug'),
        ];

        return [
            'display_name' => ['required', 'string', 'max:50'],
            'slug' => $slugRules,
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

    public function validatedWithSlug(): array
    {
        $data = $this->validated();
        if (empty($data['slug']) && ! empty($data['display_name'])) {
            $data['slug'] = CreatorProfile::uniqueSlugForDisplayName($data['display_name'], null);
        }
        if (empty($data['slug'])) {
            $data['slug'] = CreatorProfile::uniqueSlugForDisplayName('creator', null);
        }
        return $data;
    }
}
