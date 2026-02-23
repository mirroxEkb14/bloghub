<?php

namespace App\Filament\Resources\SubscriptionResource\Tables;

use App\Filters\SubscriptionTableFilters;
use App\Support\SubscriptionResourceSupport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class SubscriptionResourceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(SubscriptionResourceSupport::recordViewUrl(...))
            ->defaultSort('id')
            ->modifyQueryUsing(SubscriptionResourceSupport::tableModifyQueryUsing())
            ->filters(SubscriptionTableFilters::filters())
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                ViewColumn::make('user.name')
                    ->label(__('filament.subscriptions.table.columns.user'))
                    ->view('filament.tables.columns.subscription-user')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tier.tier_name')
                    ->label(__('filament.subscriptions.table.columns.tier'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label(__('filament.subscriptions.table.columns.start_date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label(__('filament.subscriptions.table.columns.end_date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('payments_count')
                    ->label(__('filament.subscriptions.table.columns.payments_count'))
                    ->counts('payments')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('filament.subscriptions.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->label(__('filament.subscriptions.table.actions.view')),
                DeleteAction::make()->label(__('filament.subscriptions.table.actions.delete'))->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
