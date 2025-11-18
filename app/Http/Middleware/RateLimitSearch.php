<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitSearch
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'search:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json(['error' => 'Too many search requests. Please wait.'], Response::HTTP_TOO_MANY_REQUESTS);
        }
        RateLimiter::hit($key, 60); // 30 requests per minute

        return $next($request);
    }
}
