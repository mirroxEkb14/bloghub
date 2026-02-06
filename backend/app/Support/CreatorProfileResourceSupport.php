<?php

namespace App\Support;

use App\Filament\Resources\CreatorProfileResource\CreatorProfileResource;
use App\Models\CreatorProfile;
use Closure;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreatorProfileResourceSupport
{
    public const MAX_FILE_SIZE_KB = 5 * 1024;

    public const AVATAR_DIRECTORY = 'creator-profiles/avatars';

    public const COVER_DIRECTORY = 'creator-profiles/covers';

    private function __construct()
    {
    }

    public static function acceptedImageMimeTypes(): array
    {
        return ['image/jpeg', 'image/png', 'image/webp'];
    }

    public static function userRelationshipQuery(): Closure
    {
        return static function (Builder $query, ?Model $record): Builder {
            if ($record !== null) {
                return $query->whereDoesntHave('creatorProfile')->orWhere('id', $record->user_id);
            }

            return $query->whereDoesntHave('creatorProfile');
        };
    }

    public static function setSlugFromDisplayName(): Closure
    {
        return static fn (Set $set, ?string $state): mixed => $set('slug', Str::slug($state ?? ''));
    }

    public static function recordViewUrl(CreatorProfile $record): string
    {
        return CreatorProfileResource::getUrl('view', ['record' => $record]);
    }
}
