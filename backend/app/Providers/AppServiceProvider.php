<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $configureDeleteAction = static function ($action): void {
            $action
                ->disabled(static function ($record): bool {
                    return $record instanceof Role && $record->users()->exists();
                })
                ->tooltip(static function ($record): ?string {
                    if ($record instanceof Role && $record->users()->exists()) {
                        return 'This role cannot be deleted because it is assigned to at least one user.';
                    }

                    return null;
                });
        };

        if (class_exists(\Filament\Tables\Actions\DeleteAction::class)) {
            \Filament\Tables\Actions\DeleteAction::configureUsing($configureDeleteAction);
        }

        if (class_exists(\Filament\Actions\DeleteAction::class)) {
            \Filament\Actions\DeleteAction::configureUsing($configureDeleteAction);
        }
    }
}
