<?php

namespace App\Filament\Pages;

use App\Contracts\AdminLocaleProvider;
use App\Support\AdminLocale;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class Profile extends Page
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-identification';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.profile';

    public array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('filament.profile.navigation_label');
    }

    public function getTitle(): string
    {
        return __('filament.profile.navigation_label');
    }

    public function mount(): void
    {
        $this->form->fill([
            'locale' => app(AdminLocaleProvider::class)->get(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('locale')
                    ->label(__('filament.profile.language_label'))
                    ->options([
                        'en' => __('filament.profile.language_options.en'),
                        'cs' => __('filament.profile.language_options.cs'),
                    ])
                    ->required(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $locale = $data['locale'] ?? null;

        if (! is_string($locale) || ! AdminLocale::isValid($locale)) {
            return;
        }

        session([AdminLocale::ADMIN_LOCALE_SESSION_KEY => $locale]);
        session()->save();

        Notification::make()
            ->title(__('filament.profile.saved'))
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }
}
