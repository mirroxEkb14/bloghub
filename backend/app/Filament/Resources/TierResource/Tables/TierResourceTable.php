<?php

namespace App\Filament\Resources\TierResource\Tables;

use App\Filters\TierTableFilters;
use App\Support\TierResourceSupport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class TierResourceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(TierResourceSupport::recordViewUrl(...))
            ->defaultSort('creator_profile_id')
            ->modifyQueryUsing(TierResourceSupport::tierTableModifyQueryUsing())
            ->filters(TierTableFilters::filters(), FiltersLayout::AboveContentCollapsible)
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                ImageColumn::make('tier_cover_path')
                    ->label(__('filament.tiers.table.columns.cover'))
                    ->disk('public')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('creatorProfile.display_name')
                    ->label(__('filament.tiers.table.columns.creator_profile'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level')
                    ->label(__('filament.tiers.table.columns.level'))
                    ->sortable(),
                TextColumn::make('tier_name')
                    ->label(__('filament.tiers.table.columns.tier_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('tier_desc')
                    ->label(__('filament.tiers.table.columns.tier_desc'))
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('price')
                    ->label(__('filament.tiers.table.columns.price'))
                    ->sortable(),
                TextColumn::make('tier_currency')
                    ->label(__('filament.tiers.table.columns.tier_currency'))
                    ->formatStateUsing(TierResourceSupport::formatCurrencyForTable())
                    ->sortable(),
                TextColumn::make('subscriptions_count')
                    ->label(__('filament.tiers.table.columns.subscriptions_count'))
                    ->counts('subscriptions')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('filament.tiers.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->label(__('filament.tiers.table.actions.view')),
                EditAction::make()->label(__('filament.tiers.table.actions.edit'))->requiresConfirmation(),
                DeleteAction::make()->label(__('filament.tiers.table.actions.delete'))->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
