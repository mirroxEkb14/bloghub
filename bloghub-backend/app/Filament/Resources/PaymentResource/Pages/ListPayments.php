<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Enums\PaymentStatus;
use App\Filament\Resources\PaymentResource\PaymentResource;
use App\Models\Payment;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('filament.payments.tabs.all'))
                ->badge(Payment::query()->count()),
            'pending' => Tab::make(__('filament.payments.tabs.pending'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_status', PaymentStatus::Pending))
                ->badge(Payment::query()->where('payment_status', PaymentStatus::Pending)->count()),
            'completed' => Tab::make(__('filament.payments.tabs.completed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_status', PaymentStatus::Completed))
                ->badge(Payment::query()->where('payment_status', PaymentStatus::Completed)->count()),
            'failed' => Tab::make(__('filament.payments.tabs.failed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_status', PaymentStatus::Failed))
                ->badge(Payment::query()->where('payment_status', PaymentStatus::Failed)->count()),
        ];
    }
}
