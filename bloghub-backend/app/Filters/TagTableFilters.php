<?php

namespace App\Filters;

use App\Models\CreatorProfile;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class TagTableFilters
{
    public static function filters(): array
    {
        return [
            SelectFilter::make('creator_profile_id')
                ->label(__('filament.tags.table.filters.creator_profile'))
                ->options(
                    CreatorProfile::query()->orderBy('display_name')->pluck('display_name', 'id')
                )
                ->searchable()
                ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                    $id = $data['creator_profile_id'] ?? null;
                    if (filled($id)) {
                        $query->whereHas('creatorProfiles', fn ($q) => $q->where('creator_profiles.id', $id));
                    }
                    return $query;
                }),
        ];
    }
}
