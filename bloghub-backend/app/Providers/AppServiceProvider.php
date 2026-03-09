<?php

namespace App\Providers;

use App\Contracts\AdminLocaleProvider;
use App\Contracts\AdminTimezoneProvider;
use App\Support\SessionAdminLocaleProvider;
use App\Support\SessionAdminTimezoneProvider;
use Dedoc\Scramble\Scramble;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Scramble::ignoreDefaultRoutes();

        $this->app->bind(AdminLocaleProvider::class, SessionAdminLocaleProvider::class);
        $this->app->bind(AdminTimezoneProvider::class, SessionAdminTimezoneProvider::class);

        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient(config('services.stripe.secret'));
        });
    }

    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
