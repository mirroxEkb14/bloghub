<?php

namespace App\Filament\Resources\UserResource\Tables;

use App\Support\UserResourceSupport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
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
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                ViewColumn::make('name')
                    ->label(__('filament.users.table.columns.name'))
                    ->view('filament.tables.columns.user-name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->label(__('filament.users.table.columns.username'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('filament.users.table.columns.email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label(__('filament.users.table.columns.phone'))
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('is_creator')
                    ->label(__('filament.users.table.columns.is_creator'))
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('roles.name')
                    ->label(__('filament.users.table.columns.roles'))
                    ->badge()
                    ->separator(', ')
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
                DeleteAction::make()->label(__('filament.users.table.actions.delete'))->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
