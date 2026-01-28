<?php

namespace App\Filament\Resources\UserResource\Tables;

use App\Models\User;
use App\Filament\Resources\UserResource\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class UserResourceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn (User $record): string => UserResource::getUrl('view', ['record' => $record]))
            ->defaultSort('id')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                ViewColumn::make('name')
                    ->label(__('filament.users.table.columns.name'))
                    ->view('filament.tables.columns.user-name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('username')
                    ->label(__('filament.users.table.columns.username'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->label(__('filament.users.table.columns.email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label(__('filament.users.table.columns.phone'))
                    ->searchable()
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
            ->actions([
                Action::make('view')
                    ->label(__('filament.users.table.actions.view'))
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (User $record): string => UserResource::getUrl('view', ['record' => $record])),
                Action::make('edit')
                    ->label(__('filament.users.table.actions.edit'))
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
