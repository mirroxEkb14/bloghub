<?php

namespace App\Providers;

use Filament\Tables\Actions\DeleteAction;
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
        DeleteAction::configureUsing(static function (DeleteAction $action): void {
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
        });
    }
}
