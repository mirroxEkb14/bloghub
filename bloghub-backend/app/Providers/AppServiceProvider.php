<?php

namespace App\Providers;

use App\Contracts\AdminLocaleProvider;
use App\Support\SessionAdminLocaleProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminLocaleProvider::class, SessionAdminLocaleProvider::class);
    }

    public function boot(): void
    {
        //
    }
}
