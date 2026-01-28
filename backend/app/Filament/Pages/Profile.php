<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.profile';

    public string $locale = 'en';
    public ?array $data = [];

    public function mount(): void
    {
        $this->locale = session('admin_locale', app()->getLocale());
        $this->form->fill([
            'selectedLocale' => $this->locale,
        ]);
    }

    public function saveLocale(): void
    {
        $state = $this->form->getState();
        $selectedLocale = $state['selectedLocale'] ?? 'en';

        $this->setLocale($selectedLocale);
        $this->locale = $selectedLocale;
        $this->redirect(static::getUrl(), navigate: false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('admin.profile.language'))
                    ->description(__('admin.profile.language_help'))
                    ->schema([
                        ToggleButtons::make('selectedLocale')
                            ->label(false)
                            ->options([
                                'en' => __('admin.languages.en'),
                                'cs' => __('admin.languages.cs'),
                            ])
                            ->inline()
                            ->required(),
                    ]),
            ])
            ->statePath('data');
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
