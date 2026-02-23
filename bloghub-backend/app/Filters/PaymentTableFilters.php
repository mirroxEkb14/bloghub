<?php

namespace App\Filters;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PaymentTableFilters
{
    public static function filters(): array
    {
        return [
            Filter::make('amount')
                ->label(__('filament.payments.table.filters.amount'))
                ->schema([
                    TextInput::make('amount_from')
                        ->label(__('filament.payments.table.filters.amount_from'))
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->placeholder(__('filament.payments.table.filters.amount_from_placeholder')),
                    TextInput::make('amount_to')
                        ->label(__('filament.payments.table.filters.amount_to'))
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->placeholder(__('filament.payments.table.filters.amount_to_placeholder')),
                ])
                ->columns(2)
                ->query(function (Builder $query, array $data): Builder {
                    $from = isset($data['amount_from']) && $data['amount_from'] !== '' ? (int) $data['amount_from'] : null;
                    $to = isset($data['amount_to']) && $data['amount_to'] !== '' ? (int) $data['amount_to'] : null;
                    if ($from !== null) {
                        $query->where('amount', '>=', $from);
                    }
                    if ($to !== null) {
                        $query->where('amount', '<=', $to);
                    }
                    return $query;
                })
                ->indicateUsing(function (array $data): ?string {
                    $from = $data['amount_from'] ?? null;
                    $to = $data['amount_to'] ?? null;
                    if (($from === null || $from === '') && ($to === null || $to === '')) {
                        return null;
                    }
                    $parts = [];
                    if ($from !== null && $from !== '') {
                        $parts[] = __('filament.payments.table.filters.amount_from_indicator', ['value' => $from]);
                    }
                    if ($to !== null && $to !== '') {
                        $parts[] = __('filament.payments.table.filters.amount_to_indicator', ['value' => $to]);
                    }
                    return implode(' ', $parts);
                }),
            Filter::make('checkout_date')
                ->label(__('filament.payments.table.filters.checkout_date'))
                ->schema([
                    DatePicker::make('checkout_from')
                        ->label(__('filament.payments.table.filters.checkout_from'))
                        ->placeholder(__('filament.payments.table.filters.checkout_from_placeholder')),
                    DatePicker::make('checkout_until')
                        ->label(__('filament.payments.table.filters.checkout_until'))
                        ->placeholder(__('filament.payments.table.filters.checkout_until_placeholder'))
                ])
                ->columns(2)
                ->query(function (Builder $query, array $data): Builder {
                    $from = $data['checkout_from'] ?? null;
                    $to = $data['checkout_until'] ?? null;
                    if ($from !== null) {
                        $query->whereDate('checkout_date', '>=', $from);
                    }
                    if ($to !== null) {
                        $query->whereDate('checkout_date', '<=', $to);
                    }
                    return $query;
                })
                ->indicateUsing(function (array $data): ?string {
                    $from = $data['checkout_from'] ?? null;
                    $to = $data['checkout_until'] ?? null;
                    if ($from === null && $to === null) {
                        return null;
                    }
                    $parts = [];
                    if ($from !== null) {
                        $parts[] = __('filament.payments.table.filters.checkout_from_indicator', ['value' => $from]);
                    }
                    if ($to !== null) {
                        $parts[] = __('filament.payments.table.filters.checkout_until_indicator', ['value' => $to]);
                    }
                    return implode(' ', $parts);
                }),
        ];
    }
}
