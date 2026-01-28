<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('username')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(false),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255)
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}
