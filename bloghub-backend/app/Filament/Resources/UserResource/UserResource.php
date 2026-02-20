<?php

namespace App\Filament\Resources\UserResource;

use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\Schemas\UserResourceForm;
use App\Filament\Resources\UserResource\Tables\UserResourceTable;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BackedEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('filament.users.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.roles.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('filament.users.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.users.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return UserResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserResourceTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
