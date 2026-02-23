<?php

namespace App\Filament\Resources\TagResource\Tables;

use App\Support\TagResourceActions;
use App\Support\TagResourceSupport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagResourceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(TagResourceSupport::recordViewUrl(...))
            ->defaultSort('id')
            ->modifyQueryUsing(TagResourceSupport::tagTableModifyQueryUsing())
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('filament.tags.table.columns.slug'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('filament.tags.table.columns.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('creator_profiles_label')
                    ->label(__('filament.tags.table.columns.creator_profiles'))
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('filament.tags.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->label(__('filament.tags.table.actions.view')),
                EditAction::make()->label(__('filament.tags.table.actions.edit'))->requiresConfirmation(),
                TagResourceActions::deleteActionForTable(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
