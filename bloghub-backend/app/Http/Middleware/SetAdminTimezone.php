<?php

namespace App\Http\Middleware;

use App\Contracts\AdminTimezoneProvider;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminTimezone
{
    public function __construct(
        private AdminTimezoneProvider $adminTimezone
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        date_default_timezone_set($this->adminTimezone->get());

        return $next($request);
    }
}
