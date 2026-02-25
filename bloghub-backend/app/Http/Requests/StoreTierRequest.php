<?php

namespace App\Http\Requests;

use App\Enums\Currency;
use App\Support\TierResourceSupport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->creatorProfile;
    }

    public function rules(): array
    {
        $creatorProfileId = $this->user()?->creatorProfile?->id;

        return [
            'level' => [
                'required',
                'integer',
                Rule::in(TierResourceSupport::LEVEL_VALUES),
                Rule::unique('tiers', 'level')
                    ->where(fn ($q) => $q->where('creator_profile_id', $creatorProfileId)),
            ],
            'tier_name' => ['required', 'string', 'max:'.TierResourceSupport::NAME_MAX_LENGTH],
            'tier_desc' => ['required', 'string', 'max:'.TierResourceSupport::DESC_MAX_LENGTH],
            'price' => ['required', 'integer', 'min:1'],
            'tier_currency' => ['required', new Enum(Currency::class)],
            'tier_cover_path' => [
                'nullable',
                'image',
                'mimes:jpeg,png,webp',
                'max:'.TierResourceSupport::COVER_MAX_FILE_SIZE_KB,
                'dimensions:max_width='.TierResourceSupport::COVER_MAX_WIDTH.',max_height='.TierResourceSupport::COVER_MAX_HEIGHT,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'level.in' => __('messages.tier_level_invalid'),
        ];
    }
}
