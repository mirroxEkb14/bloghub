<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\ValidatesApiCreatorProfileUpdate;
use App\Models\CreatorProfile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCreatorProfileRequest extends FormRequest
{
    use ValidatesApiCreatorProfileUpdate;

    public function authorize(): bool
    {
        $profile = $this->route('creatorProfile');

        return $profile instanceof CreatorProfile
            && $this->user()?->creatorProfile?->id === $profile->id;
    }

    public function rules(): array
    {
        return $this->creatorProfileApiUpdateRules($this->route('creatorProfile'));
    }

    public function messages(): array
    {
        return $this->creatorProfileApiUpdateMessages();
    }
}
