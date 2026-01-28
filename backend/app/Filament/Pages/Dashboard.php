<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 0;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.dashboard');
    }

    public function getTitle(): string
    {
        return __('admin.navigation.dashboard');
    }
}
