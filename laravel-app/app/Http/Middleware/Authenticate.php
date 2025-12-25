<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (session('authenticated') !== true) {
            // Store intended URL for redirect after login
            return redirect()
                ->route('login')
                ->with('redirect', $request->url());
        }

        return $next($request);
    }
}
