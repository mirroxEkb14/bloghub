<?php

namespace App\Http\Requests\Api;

use App\Enums\SubStatus;
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
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->tierExists()) {
                $validator->errors()->add('tier_id', __('Tier not found'));
                return;
            }
            $user = $this->user();
            if ($user && $this->hasActiveSubscriptionToThisCreator()) {
                $validator->errors()->add('tier_id', __('You already have an active subscription to this creator'));
            }
        });
    }

    private function tierExists(): bool
    {
        return Tier::query()
            ->where('id', $this->input('tier_id'))
            ->whereHas('creatorProfile')
            ->exists();
    }

    private function hasActiveSubscriptionToThisCreator(): bool
    {
        $tier = Tier::query()
            ->where('id', $this->input('tier_id'))
            ->whereHas('creatorProfile')
            ->first();

        if (! $tier) {
            return false;
        }

        return $this->user()
            ->subscriptions()
            ->where('sub_status', SubStatus::Active)
            ->where('end_date', '>', now())
            ->whereHas('tier', fn ($q) => $q->where('creator_profile_id', $tier->creator_profile_id))
            ->exists();
    }
}
