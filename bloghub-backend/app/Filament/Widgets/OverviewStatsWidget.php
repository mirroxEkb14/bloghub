<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\Subscription;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = null;

    protected ?string $pollingInterval = null;

    public function getHeading(): string
    {
        return __('filament.dashboard.overview_stats.heading');
    }

    protected function getStats(): array
    {
        $now = now();
        $weekStart = $now->copy()->subWeek();
        $monthStart = $now->copy()->subMonth();

        $userTotal = User::query()->count();
        $userWeek = User::query()->where('created_at', '>=', $weekStart)->count();
        $userMonth = User::query()->where('created_at', '>=', $monthStart)->count();

        $postTotal = Post::query()->count();
        $postWeek = Post::query()->where('created_at', '>=', $weekStart)->count();
        $postMonth = Post::query()->where('created_at', '>=', $monthStart)->count();

        $subscriptionTotal = Subscription::query()->count();
        $subscriptionWeek = Subscription::query()->where('created_at', '>=', $weekStart)->count();
        $subscriptionMonth = Subscription::query()->where('created_at', '>=', $monthStart)->count();

        return [
            Stat::make(__('filament.dashboard.overview_stats.period.total'), number_format($userTotal))
                ->description(__('filament.dashboard.overview_stats.user_registrations'))
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),
            Stat::make(__('filament.dashboard.overview_stats.period.total'), number_format($postTotal))
                ->description(__('filament.dashboard.overview_stats.created_posts'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make(__('filament.dashboard.overview_stats.period.total'), number_format($subscriptionTotal))
                ->description(__('filament.dashboard.overview_stats.subscriptions_made'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('primary'),

            Stat::make(__('filament.dashboard.overview_stats.period.week'), number_format($userWeek))
                ->color('gray'),
            Stat::make(__('filament.dashboard.overview_stats.period.week'), number_format($postWeek))
                ->color('gray'),
            Stat::make(__('filament.dashboard.overview_stats.period.week'), number_format($subscriptionWeek))
                ->color('gray'),

            Stat::make(__('filament.dashboard.overview_stats.period.month'), number_format($userMonth))
                ->color('gray'),
            Stat::make(__('filament.dashboard.overview_stats.period.month'), number_format($postMonth))
                ->color('gray'),
            Stat::make(__('filament.dashboard.overview_stats.period.month'), number_format($subscriptionMonth))
                ->color('gray'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
