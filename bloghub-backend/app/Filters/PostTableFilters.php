<?php

namespace App\Filters;

use App\Support\PostResourceSupport;
use Filament\Tables\Filters\SelectFilter;

class PostTableFilters
{
    public static function filters(): array
    {
        return [
            SelectFilter::make('creator_profile_id')
                ->label(__('filament.posts.table.columns.creator_profile'))
                ->relationship('creatorProfile', 'display_name')
                ->searchable()
                ->preload(),
            SelectFilter::make('media_type')
                ->label(__('filament.posts.table.columns.media_type'))
                ->options(PostResourceSupport::mediaTypeOptions()),
        ];
    }
}
