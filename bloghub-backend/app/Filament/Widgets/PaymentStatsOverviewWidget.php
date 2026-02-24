<?php

namespace App\Filament\Widgets;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = null;

    protected ?string $pollingInterval = null;

    public function getHeading(): string
    {
        return __('filament.dashboard.payments_stats.heading');
    }

    protected function getStats(): array
    {
        $pendingCount = Payment::query()->where('payment_status', PaymentStatus::Pending)->count();
        $failedCount = Payment::query()->where('payment_status', PaymentStatus::Failed)->count();
        $totalCount = Payment::query()->count();

        $lastMonths = collect(range(5, 0))->map(fn (int $monthsAgo) => now()->subMonths($monthsAgo));
        $countChart = $lastMonths->map(function ($start) {
            return Payment::query()
                ->whereBetween('checkout_date', [$start->copy()->startOfMonth(), $start->copy()->endOfMonth()])
                ->count();
        })->values()->all();

        $stats = [
            Stat::make(__('filament.dashboard.payments_stats.total_payments'), number_format($totalCount))
                ->description(__('filament.dashboard.payments_stats.all_time'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->chart($countChart)
                ->color('primary'),
            Stat::make(__('filament.dashboard.payments_stats.pending'), number_format($pendingCount))
                ->description(__('filament.dashboard.payments_stats.tabs.pending'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make(__('filament.dashboard.payments_stats.failed'), number_format($failedCount))
                ->description(__('filament.dashboard.payments_stats.tabs.failed'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];

        foreach (Currency::cases() as $currency) {
            $revenue = Payment::query()
                ->where('payment_status', PaymentStatus::Completed)
                ->where('currency', $currency)
                ->sum('amount');
            $stats[] = Stat::make(
                __('filament.dashboard.payments_stats.revenue_currency', ['currency' => $currency->value]),
                number_format($revenue)
            )
                ->description(__('filament.dashboard.payments_stats.completed_only'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success');
        }

        return $stats;
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
