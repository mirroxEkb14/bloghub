<?php

namespace App\Rules;

use App\Models\Post;
use Closure;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class SlugUniquePerCreatorProfileRule implements ValidationRule
{
    public function __construct(private mixed $get)
    {
    }

    public static function fromGet(callable $get): self
    {
        return new self($get);
    }

    public static function forForm(): \Closure
    {
        return fn (Get $get): self => self::fromGet($get);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $creatorProfileId = $this->get('creator_profile_id');
        if ($creatorProfileId === null || $creatorProfileId === '') {
            return;
        }

        $query = Post::query()
            ->where('creator_profile_id', $creatorProfileId)
            ->where('slug', $value);

        $recordId = $this->get('id');
        if ($recordId !== null && $recordId !== '') {
            $query->whereKeyNot($recordId);
        }

        if ($query->exists()) {
            $fail(__('validation.unique', ['attribute' => __('filament.posts.form.slug')]));
        }
    }
}
