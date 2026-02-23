<?php

namespace App\Support;

use App\Filament\Resources\TagResource\TagResource;
use App\Models\Tag;
use Closure;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;

class TagResourceSupport
{
    public const SLUG_MAX_LENGTH = 50;
    public const NAME_MAX_LENGTH = 50;

    private function __construct()
    {
    }

    public static function setSlugFromName(): Closure
    {
        return static function (Set $set, Get $get, ?string $state): mixed {
            $excludeId = $get('id');
            if ($excludeId !== null && $excludeId !== '') {
                $excludeId = (int) $excludeId;
            } else {
                $excludeId = null;
            }

            return $set('slug', Tag::uniqueSlugForName($state ?? '', $excludeId));
        };
    }

    public static function recordViewUrl(Tag $record): string
    {
        return TagResource::getUrl('view', ['record' => $record]);
    }

    public static function tagTableModifyQueryUsing(): Closure
    {
        return static function (Builder $query): Builder {
            return $query->with('creatorProfiles');
        };
    }
}
