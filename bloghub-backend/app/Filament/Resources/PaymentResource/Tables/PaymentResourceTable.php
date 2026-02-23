<?php

namespace App\Filament\Resources\PaymentResource\Tables;

use App\Filters\PaymentTableFilters;
use App\Support\PaymentResourceSupport;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentResourceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(PaymentResourceSupport::recordViewUrl(...))
            ->defaultSort('id')
            ->modifyQueryUsing(PaymentResourceSupport::tableModifyQueryUsing())
            ->filters(PaymentTableFilters::filters())
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('subscription_label')
                    ->label(__('filament.payments.table.columns.subscription'))
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('subscription_id', $direction)),
                TextColumn::make('amount')
                    ->label(__('filament.payments.table.columns.amount'))
                    ->sortable(),
                TextColumn::make('currency')
                    ->label(__('filament.payments.table.columns.currency'))
                    ->formatStateUsing(PaymentResourceSupport::formatCurrencyForTable())
                    ->sortable(),
                TextColumn::make('checkout_date')
                    ->label(__('filament.payments.table.columns.checkout_date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('card_last4')
                    ->label(__('filament.payments.table.columns.card_last4'))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('filament.payments.table.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()->label(__('filament.payments.table.actions.view')),
            ]);
    }
}
