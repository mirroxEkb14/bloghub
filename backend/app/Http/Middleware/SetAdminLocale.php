<?php

namespace App\Http\Middleware;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class SetAdminLocale
{
    /**
     * @param  Closure(Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('admin_locale', config('app.locale'));

        if (is_string($locale)) {
            app()->setLocale($locale);
        }

        $panel = Filament::getCurrentPanel();

        if ($panel) {
            foreach ($panel->getPlugins() as $plugin) {
                if ($plugin instanceof FilamentShieldPlugin && method_exists($plugin, 'navigationGroup')) {
                    $plugin->navigationGroup(__('admin.navigation.role_panel'));
                }
            }
        }

        return $next($request);
    }
}
