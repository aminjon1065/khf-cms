<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The public API is JSON-only (ToR §7.1). Forcing the Accept header guarantees
 * every error — including auth failures — is rendered as JSON (401/404/422),
 * instead of Laravel redirecting guests to a non-existent `login` route (500).
 */
class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
