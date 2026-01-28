<?php

namespace App\Filament\Resources\UserResource;

use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Filament\Resources\UserResource\Schemas\UserResourceForm;
use App\Filament\Resources\UserResource\Tables\UserResourceTable;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.users');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.role_panel');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.users');
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
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
