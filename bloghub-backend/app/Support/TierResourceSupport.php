<?php

namespace App\Support;

use App\Enums\Currency;
use App\Filament\Resources\TierResource\TierResource;
use App\Models\Tier;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class TierResourceSupport
{
    public const LEVEL_VALUES = [1, 2, 3];
    public const NAME_MAX_LENGTH = 50;
    public const DESC_MAX_LENGTH = 255;
    public const COVER_MAX_FILE_SIZE_KB = 5 * 1024;
    public const COVER_MAX_WIDTH = 1080;
    public const COVER_MAX_HEIGHT = 1920;
    public const COVER_DIRECTORY = 'tiers/covers';

    private function __construct()
    {
    }

    public static function acceptedCoverImageMimeTypes(): array
    {
        return ['image/jpeg', 'image/png', 'image/webp'];
    }

    public static function levelOptions(): array
    {
        $options = [];
        foreach (self::LEVEL_VALUES as $value) {
            $options[$value] = (string) $value;
        }

        return $options;
    }

    public static function currencyOptions(): array
    {
        $options = [];
        foreach (Currency::cases() as $case) {
            $options[$case->value] = $case->value;
        }

        return $options;
    }

    public static function creatorProfileRelationshipQuery(): Closure
    {
        return static function (Builder $query): Builder {
            return $query->orderBy('display_name');
        };
    }

    public static function levelUniqueRules(): array
    {
        return [
            Rule::unique('tiers', 'level')
                ->where(fn ($query) => $query->where(
                    'creator_profile_id',
                    request()->input('creator_profile_id')
                )),
        ];
    }

    public static function recordViewUrl(Tier $record): string
    {
        return TierResource::getUrl('view', ['record' => $record]);
    }

    public static function tierTableModifyQueryUsing(): Closure
    {
        return static function (Builder $query): Builder {
            return $query->with('creatorProfile');
        };
    }

    public static function formatCurrencyForTable(): Closure
    {
        return static function (mixed $state): mixed {
            return $state?->value ?? $state;
        };
    }
}
