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
                    ->disabled(fn (string $operation): bool => $operation !== 'create')
                    ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                TextInput::make('username')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (string $operation): bool => $operation !== 'create')
                    ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->disabled(fn (string $operation): bool => $operation !== 'create')
                    ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                TextInput::make('phone')
                    ->tel()
                    ->maxLength(255)
                    ->disabled(fn (string $operation): bool => $operation !== 'create')
                    ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn (string $operation): bool => $operation === 'create')
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->maxLength(255)
                    ->disabled(fn (string $operation): bool => $operation !== 'create'),
            ]);
    }
}
