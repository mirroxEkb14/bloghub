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
            'all' => Tab::make(__('admin.tabs.all'))
                ->badge(User::count()),
            'super_admins' => Tab::make(__('admin.tabs.super_admins'))
                ->modifyQueryUsing(fn (Builder $query) => $query->role(UserRoleEnum::SUPER_ADMIN->value))
                ->badge(User::role(UserRoleEnum::SUPER_ADMIN->value)->count()),
            'admins' => Tab::make(__('admin.tabs.admins'))
                ->modifyQueryUsing(fn (Builder $query) => $query->role(UserRoleEnum::ADMIN->value))
                ->badge(User::role(UserRoleEnum::ADMIN->value)->count()),
            'others' => Tab::make(__('admin.tabs.others'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDoesntHave('roles'))
                ->badge(User::whereDoesntHave('roles')->count()),
        ];
    }
}
