<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource\UserResource;
use App\Enums\UserRoleEnum;
use App\Models\User;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->badge(User::count()),
            'super_admins' => Tab::make('Super Admins')
                ->modifyQueryUsing(fn (Builder $query) => $query->role(UserRoleEnum::SUPER_ADMIN->value))
                ->badge(User::role(UserRoleEnum::SUPER_ADMIN->value)->count()),
            'admins' => Tab::make('Admins')
                ->modifyQueryUsing(fn (Builder $query) => $query->role(UserRoleEnum::ADMIN->value))
                ->badge(User::role(UserRoleEnum::ADMIN->value)->count()),
            'others' => Tab::make('Others')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDoesntHave('roles'))
                ->badge(User::whereDoesntHave('roles')->count()),
        ];
    }
}
