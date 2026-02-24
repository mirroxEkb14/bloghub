<?php

namespace App\Providers;

use App\Contracts\AdminLocaleProvider;
use App\Contracts\AdminTimezoneProvider;
use App\Support\SessionAdminLocaleProvider;
use App\Support\SessionAdminTimezoneProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminLocaleProvider::class, SessionAdminLocaleProvider::class);
        $this->app->bind(AdminTimezoneProvider::class, SessionAdminTimezoneProvider::class);
    }

    public function boot(): void
    {
        //
    }
}
