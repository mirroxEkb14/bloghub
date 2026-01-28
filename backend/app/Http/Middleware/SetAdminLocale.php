<?php

namespace App\Http\Middleware;

use Closure;
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

        return $next($request);
    }
}
