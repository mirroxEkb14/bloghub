<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label(__('filament.users.form.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label(__('filament.users.form.username'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->label(__('filament.users.form.email'))
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label(__('filament.users.form.phone'))
                    ->tel()
                    ->maxLength(255),
                TextInput::make('password')
                    ->label(__('filament.users.form.password'))
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255)
                    ->dehydrated(fn (?string $state): bool => filled($state)),
                Toggle::make('is_creator')
                    ->label(__('filament.users.form.is_creator'))
                    ->default(false),
            ]);
    }
}
