<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() && $user = auth('sanctum')->user()) {
            $request->setUserResolver(fn () => $user);
        }

        return $next($request);
    }
}
