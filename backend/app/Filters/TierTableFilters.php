<?php

namespace App\Filters;

use App\Models\Tier;
use App\Support\TierResourceSupport;
use Filament\Tables\Filters\SelectFilter;

class TierTableFilters
{
    public static function filters(): array
    {
        return [
            SelectFilter::make('creator_profile_id')
                ->label(__('filament.tiers.table.columns.creator_profile'))
                ->relationship('creatorProfile', 'display_name')
                ->searchable()
                ->preload(),
            SelectFilter::make('level')
                ->label(__('filament.tiers.table.columns.level'))
                ->options(TierResourceSupport::levelOptions()),
            SelectFilter::make('price')
                ->label(__('filament.tiers.table.columns.price'))
                ->options(fn (): array => Tier::query()->distinct()->orderBy('price')->pluck('price', 'price')->all()),
            SelectFilter::make('tier_currency')
                ->label(__('filament.tiers.table.columns.tier_currency'))
                ->options(TierResourceSupport::currencyOptions()),
        ];
    }
}
