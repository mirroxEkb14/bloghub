<?php

namespace App\Http\Requests\Api;

use App\Enums\Currency;
use App\Support\TierResourceSupport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->creatorProfile;
    }

    public function rules(): array
    {
        return [
            'tier_name' => ['required', 'string', 'max:'.TierResourceSupport::NAME_MAX_LENGTH],
            'tier_desc' => ['required', 'string', 'max:'.TierResourceSupport::DESC_MAX_LENGTH],
            'price' => ['required', 'integer', 'min:0'],
            'tier_currency' => ['required', new Enum(Currency::class)],
            'tier_cover_path' => ['nullable', 'string', 'max:500'],
        ];
    }
}
