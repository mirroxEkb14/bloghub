<?php

namespace App\Filament\Resources\SubscriptionResource;

use App\Filament\Resources\SubscriptionResource\Pages\ListSubscriptions;
use App\Filament\Resources\SubscriptionResource\Pages\ViewSubscription;
use App\Filament\Resources\SubscriptionResource\Schemas\SubscriptionResourceForm;
use App\Filament\Resources\SubscriptionResource\Tables\SubscriptionResourceTable;
use App\Models\Subscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('filament.subscriptions.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.content.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('filament.subscriptions.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.subscriptions.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return SubscriptionResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubscriptionResourceTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptions::route('/'),
            'view' => ViewSubscription::route('/{record}'),
        ];
    }
}
