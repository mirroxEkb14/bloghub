<?php

namespace App\Providers;

use App\Filament\Support\RoleDeleteGuard;
use Filament\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Tables\Actions\DeleteAction as TablesDeleteAction;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (class_exists(TablesDeleteAction::class)) {
            TablesDeleteAction::configureUsing(static function ($action): void {
                RoleDeleteGuard::configure($action);
            });
        }

        if (class_exists(ActionsDeleteAction::class)) {
            ActionsDeleteAction::configureUsing(static function ($action): void {
                RoleDeleteGuard::configure($action);
            });
        }
    }
}
