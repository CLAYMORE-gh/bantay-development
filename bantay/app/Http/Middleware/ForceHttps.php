<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Forces HTTPS when the app is running in production. Deliberately does
 * NOT force HTTPS in local/dev — your Laragon setup serves over plain
 * http://127.0.0.1:8000, and forcing https there would just break it.
 *
 * When you eventually deploy this to a real server with a TLS certificate,
 * set APP_ENV=production in that server's .env and this kicks in automatically.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('production') && !$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        return $next($request);
    }
}
