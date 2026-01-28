<?php

namespace App\Filament\Resources\UserResource\Tables;

use App\Models\User;
use App\Filament\Resources\UserResource\UserResource;
use Illuminate\Support\Facades\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResourceTable
{
    public static function configure(Table $table): Table
    {
        $table = $table->defaultSort('id');

        if (Schema::hasColumn('users', 'sort_order')) {
            $table = $table->reorderable('sort_order');
        }

        return $table
            ->recordUrl(fn (User $record): string => UserResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('username')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(', ')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ]);
    }
}
