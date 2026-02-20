<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'post_id' => [
                'required',
                'integer',
                Rule::exists('posts', 'id'),
            ],
            'content_text' => ['required', 'string', 'max:65535'],
        ];
    }
}
