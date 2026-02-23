<?php

namespace App\Filters;

use Filament\Tables\Filters\SelectFilter;

class CommentTableFilters
{
    public static function filters(): array
    {
        return [
            SelectFilter::make('user_id')
                ->label(__('filament.comments.table.columns.user'))
                ->relationship('user', 'name')
                ->searchable()
                ->preload(),
        ];
    }
}
