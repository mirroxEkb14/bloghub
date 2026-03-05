<?php

namespace App\Filament\Resources\UserResource\Tables;

use App\Filters\UserTableFilters;
use App\Support\UserResourceActions;
use App\Support\UserResourceSupport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class UserResourceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(UserResourceSupport::recordViewUrl(...))
            ->defaultSort('id')
            ->modifyQueryUsing(fn ($query) => $query->with('creatorProfile'))
            ->filters(UserTableFilters::filters())
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                ViewColumn::make('name')
                    ->label(__('filament.users.table.columns.name'))
                    ->view('filament.tables.columns.user-name')
                    ->searchable()
                    ->sortable()
                    ->extraCellAttributes(['style' => 'max-width: 220px;']),
                TextColumn::make('username')
                    ->label(__('filament.users.table.columns.username'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->extraCellAttributes(['style' => 'max-width: 180px;']),
                TextColumn::make('email')
                    ->label(__('filament.users.table.columns.email'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->extraCellAttributes(['style' => 'max-width: 200px;'])
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label(__('filament.users.table.columns.phone'))
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('is_creator')
                    ->label(__('filament.users.table.columns.is_creator'))
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('filament.users.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->label(__('filament.users.table.actions.view')),
                EditAction::make()->label(__('filament.users.table.actions.edit')),
                UserResourceActions::deleteActionForTable(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
