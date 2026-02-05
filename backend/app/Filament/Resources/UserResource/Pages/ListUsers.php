<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource\UserResource;
use App\Enums\UserRoleEnum;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('filament.users.tabs.all'))
                ->badge(User::count()),
            'super_admins' => Tab::make(__('filament.users.tabs.super_admins'))
                ->modifyQueryUsing(fn (Builder $query) => $query->role(UserRoleEnum::SuperAdmin->value))
                ->badge(User::role(UserRoleEnum::SuperAdmin->value)->count()),
            'admins' => Tab::make(__('filament.users.tabs.admins'))
                ->modifyQueryUsing(fn (Builder $query) => $query->role(UserRoleEnum::Admin->value))
                ->badge(User::role(UserRoleEnum::Admin->value)->count()),
            'others' => Tab::make(__('filament.users.tabs.others'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDoesntHave('roles'))
                ->badge(User::whereDoesntHave('roles')->count()),
        ];
    }
}
