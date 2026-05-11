<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\Concerns\ValidatesApiCreatorProfileUpdate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMyCreatorProfileRequest extends FormRequest
{
    use ValidatesApiCreatorProfileUpdate;

    public function authorize(): bool
    {
        return $this->user()?->creatorProfile !== null;
    }

    public function rules(): array
    {
        return $this->creatorProfileApiUpdateRules($this->user()?->creatorProfile);
    }

    public function messages(): array
    {
        return $this->creatorProfileApiUpdateMessages();
    }
}
