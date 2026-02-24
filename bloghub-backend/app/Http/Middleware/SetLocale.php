<?php

namespace App\Http\Middleware;

use App\Contracts\AdminLocaleProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(
        private AdminLocaleProvider $adminLocale
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale($this->adminLocale->get());

        return $next($request);
    }
}
