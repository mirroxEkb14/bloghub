<?php

namespace App\Filament\Widgets;

use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Widgets\ChartWidget;

class PaymentsChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = null;

    public ?string $filter = 'year';

    protected ?string $pollingInterval = null;

    private const CURRENCY_COLORS = [
        'CZK' => ['bg' => 'rgba(34, 197, 94, 0.2)', 'border' => 'rgb(34, 197, 94)'],
        'USD' => ['bg' => 'rgba(59, 130, 246, 0.2)', 'border' => 'rgb(59, 130, 246)'],
        'EUR' => ['bg' => 'rgba(168, 85, 247, 0.2)', 'border' => 'rgb(168, 85, 247)'],
    ];

    public function getHeading(): string
    {
        return __('filament.dashboard.payments_chart.heading');
    }

    protected function getData(): array
    {
        [$start, $end, $format, $step] = $this->getFilterRange();
        $labels = [];
        $revenueByCurrency = array_fill_keys(array_map(fn (Currency $c) => $c->value, Currency::cases()), []);
        $counts = [];

        $current = $start->copy();
        while ($current->lte($end)) {
            $periodStart = $current->copy();
            $periodEnd = $current->copy()->add($step['add'], $step['unit'])->subSecond();
            $labels[] = $current->format($format);

            foreach (Currency::cases() as $currency) {
                $revenueByCurrency[$currency->value][] = Payment::query()
                    ->where('payment_status', PaymentStatus::Completed)
                    ->where('currency', $currency)
                    ->whereBetween('checkout_date', [$periodStart, $periodEnd])
                    ->sum('amount');
            }
            $counts[] = Payment::query()
                ->whereBetween('checkout_date', [$periodStart, $periodEnd])
                ->count();
            $current->add($step['add'], $step['unit']);
        }

        $datasets = [];
        foreach (Currency::cases() as $currency) {
            $colors = self::CURRENCY_COLORS[$currency->value] ?? ['bg' => 'rgba(156, 163, 175, 0.2)', 'border' => 'rgb(156, 163, 175)'];
            $datasets[] = [
                'label' => __('filament.dashboard.payments_chart.revenue_currency', ['currency' => $currency->value]),
                'data' => $revenueByCurrency[$currency->value],
                'backgroundColor' => $colors['bg'],
                'borderColor' => $colors['border'],
                'fill' => true,
                'tension' => 0.3,
            ];
        }
        $datasets[] = [
            'label' => __('filament.dashboard.payments_chart.count'),
            'data' => $counts,
            'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
            'borderColor' => 'rgb(245, 158, 11)',
            'fill' => true,
            'tension' => 0.3,
        ];

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'week' => __('filament.dashboard.payments_chart.filters.week'),
            'month' => __('filament.dashboard.payments_chart.filters.month'),
            'year' => __('filament.dashboard.payments_chart.filters.year'),
        ];
    }

    private function getFilterRange(): array
    {
        $filter = $this->filter ?? 'year';
        $now = now();

        return match ($filter) {
            'week' => [
                $now->copy()->subWeeks(1)->startOfWeek(),
                $now->copy()->endOfWeek(),
                'D',
                ['add' => 1, 'unit' => 'day'],
            ],
            'month' => [
                $now->copy()->subDays(30)->startOfDay(),
                $now->copy()->endOfDay(),
                'd.m.',
                ['add' => 1, 'unit' => 'day'],
            ],
            default => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                'M',
                ['add' => 1, 'unit' => 'month'],
            ],
        };
    }
}
