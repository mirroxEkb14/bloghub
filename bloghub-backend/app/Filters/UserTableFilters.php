<?php

namespace App\Filters;

use Filament\Tables\Filters\SelectFilter;

class UserTableFilters
{
    public static function filters(): array
    {
        return [
            SelectFilter::make('is_creator')
                ->label(__('filament.users.table.filters.creator'))
                ->options([
                    true => __('filament.users.table.filters.creator_yes'),
                    false => __('filament.users.table.filters.creator_no'),
                ]),
        ];
    }
}
