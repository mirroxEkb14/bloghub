<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Enums\SubStatus;
use App\Filament\Resources\SubscriptionResource\SubscriptionResource;
use App\Models\Subscription;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('filament.subscriptions.tabs.all'))
                ->badge(Subscription::query()->count()),
            'active' => Tab::make(__('filament.subscriptions.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sub_status', SubStatus::Active))
                ->badge(Subscription::query()->where('sub_status', SubStatus::Active)->count()),
            'canceled' => Tab::make(__('filament.subscriptions.tabs.canceled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('sub_status', SubStatus::Canceled))
                ->badge(Subscription::query()->where('sub_status', SubStatus::Canceled)->count()),
        ];
    }
}
