<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class Profile extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.profile';

    public string $locale = 'en';

    public function mount(): void
    {
        $this->locale = session('admin_locale', app()->getLocale());
    }

    public function updatedLocale(string $locale): void
    {
        $this->setLocale($locale);
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.profile');
    }

    public function getTitle(): string
    {
        return __('admin.navigation.profile');
    }

    private function setLocale(string $locale): void
    {
        if (! in_array($locale, ['en', 'cs'], true)) {
            return;
        }

        session(['admin_locale' => $locale]);
        app()->setLocale($locale);
    }
}
