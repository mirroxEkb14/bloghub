<?php

namespace App\Filament\Resources\CreatorProfileResource\Tables;

use App\Filters\CreatorProfileTableFilters;
use App\Support\CreatorProfileResourceSupport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class CreatorProfileResourceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(CreatorProfileResourceSupport::recordViewUrl(...))
            ->defaultSort('id')
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->filters(CreatorProfileTableFilters::filters())
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                ViewColumn::make('user.name')
                    ->label(__('filament.creator_profiles.table.columns.user'))
                    ->view('filament.tables.columns.creator-profile-name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('filament.creator_profiles.table.columns.slug'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label(__('filament.creator_profiles.table.columns.display_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('posts_count')
                    ->label(__('filament.creator_profiles.table.columns.posts_count'))
                    ->counts('posts')
                    ->toggleable(),
                TextColumn::make('tiers_count')
                    ->label(__('filament.creator_profiles.table.columns.tiers_count'))
                    ->counts('tiers')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('filament.creator_profiles.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->label(__('filament.creator_profiles.table.actions.view')),
                EditAction::make()->label(__('filament.creator_profiles.table.actions.edit'))->requiresConfirmation(),
                DeleteAction::make()->label(__('filament.creator_profiles.table.actions.delete'))->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
