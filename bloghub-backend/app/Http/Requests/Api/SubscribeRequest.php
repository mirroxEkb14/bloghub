<?php

namespace App\Http\Requests\Api;

use App\Models\Tier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'tier_id' => [
                'required',
                'integer',
                Rule::exists('tiers', 'id'),
            ],
            'confirm_upgrade' => ['sometimes', 'boolean'],
        ];
    }

    private function tierExists(): bool
    {
        return Tier::query()
            ->where('id', $this->input('tier_id'))
            ->whereHas('creatorProfile')
            ->exists();
    }
}
