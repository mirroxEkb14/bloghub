<?php

namespace App\Filters;

use Filament\Tables\Filters\SelectFilter;

class SubscriptionTableFilters
{
    public static function filters(): array
    {
        return [
            SelectFilter::make('user_id')
                ->label(__('filament.subscriptions.table.columns.user'))
                ->relationship('user', 'name')
                ->searchable()
                ->preload(),
        ];
    }
}
