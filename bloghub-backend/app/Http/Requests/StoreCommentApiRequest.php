<?php

namespace App\Http\Requests;

use App\Support\CommentResourceSupport;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'content_text' => [
                'required',
                'string',
                'max:'.CommentResourceSupport::CONTENT_TEXT_MAX_LENGTH,
            ],
        ];
    }
}
