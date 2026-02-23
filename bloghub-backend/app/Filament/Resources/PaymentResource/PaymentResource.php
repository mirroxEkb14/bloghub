<?php

namespace App\Filament\Resources\PaymentResource;

use App\Filament\Resources\PaymentResource\Pages\ListPayments;
use App\Filament\Resources\PaymentResource\Pages\ViewPayment;
use App\Filament\Resources\PaymentResource\Schemas\PaymentResourceForm;
use App\Filament\Resources\PaymentResource\Tables\PaymentResourceTable;
use App\Models\Payment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return __('filament.payments.navigation_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament.content.navigation_group');
    }

    public static function getModelLabel(): string
    {
        return __('filament.payments.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.payments.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return PaymentResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentResourceTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayments::route('/'),
            'view' => ViewPayment::route('/{record}'),
        ];
    }
}
