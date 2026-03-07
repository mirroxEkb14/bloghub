<?php

namespace App\Http\Requests\Api;

use App\Enums\Currency;
use App\Models\Tier;
use App\Support\TierResourceSupport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tier = $this->route('tier');

        return $tier instanceof Tier
            && $this->user()?->creatorProfile?->id === $tier->creator_profile_id;
    }

    public function rules(): array
    {
        return [
            'tier_name' => ['sometimes', 'required', 'string', 'max:'.TierResourceSupport::NAME_MAX_LENGTH],
            'tier_desc' => ['sometimes', 'required', 'string', 'max:'.TierResourceSupport::DESC_MAX_LENGTH],
            'price' => ['sometimes', 'required', 'integer', 'min:0'],
            'tier_currency' => ['sometimes', 'required', new Enum(Currency::class)],
            'tier_cover_path' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
