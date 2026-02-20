<?php

namespace App\Rules;

use App\Models\Tier;
use Closure;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Contracts\Validation\ValidationRule;

readonly class LevelUniquePerCreatorProfileRule implements ValidationRule
{
    public function __construct(
        private mixed $get
    ) {
    }

    public static function fromGet(callable $get): self
    {
        return new self($get);
    }

    public static function forForm(): Closure
    {
        return fn (Get $get): self => self::fromGet($get);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $get = $this->get;
        $creatorProfileId = $get('creator_profile_id');
        if ($creatorProfileId === null || $creatorProfileId === '') {
            return;
        }

        $query = Tier::query()
            ->where('creator_profile_id', $creatorProfileId)
            ->where('level', $value);

        $record = $get('id');
        if ($record !== null && $record !== '') {
            $query->whereKeyNot($record);
        }

        if ($query->exists()) {
            $fail(__('validation.unique', ['attribute' => __('filament.tiers.form.level')]));
        }
    }
}
