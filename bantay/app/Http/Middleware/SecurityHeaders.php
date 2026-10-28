<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Adds standard security headers to every response. Safe to run in both
 * local dev and production — none of these depend on HTTPS being active.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Prevent this app from being embedded in an <iframe> on another
        // site (clickjacking protection).
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Stop browsers from guessing content types and executing
        // disguised files (e.g. a .txt that's actually JS).
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Don't leak full URLs (which could include query params with
        // sensitive filters) to external sites via the Referer header.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable browser features this app has no legitimate use for.
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        if (app()->environment('production')) {
            // Tell browsers to only ever connect via HTTPS for the next
            // year, even if someone types http:// explicitly.
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
