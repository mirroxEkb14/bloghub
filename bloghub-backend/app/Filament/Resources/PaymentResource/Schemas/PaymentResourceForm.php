<?php

namespace App\Filament\Resources\PaymentResource\Schemas;

use App\Support\PaymentResourceSupport;
use Carbon\Carbon;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Hidden::make('id'),
                Grid::make()
                    ->columnSpanFull()
                    ->columns()
                    ->schema([
                        Section::make(__('filament.payments.form.section_main'))
                            ->columns()
                            ->schema([
                                Select::make('subscription_id')
                                    ->label(__('filament.payments.form.subscription_id'))
                                    ->relationship('subscription', 'id', fn ($query) => $query->with('user'))
                                    ->getOptionLabelFromRecordUsing(fn ($record) => '#'.$record->id.($record->relationLoaded('user') ? " ({$record->user->name})" : ''))
                                    ->searchable(['id'])
                                    ->preload()
                                    ->required(),
                                TextInput::make('amount')
                                    ->label(__('filament.payments.form.amount'))
                                    ->placeholder(__('filament.payments.form.amount_placeholder'))
                                    ->required()
                                    ->integer()
                                    ->minValue(1),
                                Select::make('currency')
                                    ->label(__('filament.payments.form.currency'))
                                    ->options(PaymentResourceSupport::currencyOptions())
                                    ->required(),
                                TextInput::make('checkout_date')
                                    ->label(__('filament.payments.form.checkout_date'))
                                    ->placeholder(__('filament.payments.form.checkout_date_placeholder'))
                                    ->default(fn () => now()->format('Y-m-d H:i:s'))
                                    ->dehydrated(true)
                                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('Y-m-d H:i:s') : ''),
                                TextInput::make('card_last4')
                                    ->label(__('filament.payments.form.card_last4'))
                                    ->placeholder(__('filament.payments.form.card_last4_placeholder'))
                                    ->required()
                                    ->maxLength(PaymentResourceSupport::CARD_LAST4_MAX_LENGTH)
                                    ->regex('/^\d{4}$/'),
                                Select::make('payment_status')
                                    ->label(__('filament.payments.form.payment_status'))
                                    ->options(PaymentResourceSupport::paymentStatusOptions())
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
