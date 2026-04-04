<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubdomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = request()->getHost(); // Example: storeone.localhost
        $subdomain = explode('.', $host)[0]; // Extract 'storeone'

        if ($subdomain !== 'localhost' && $subdomain !== 'www') {
            // Force Laravel to recognize subdomain route
            return $next($request);
        }
        return redirect('/home');
        // Redirect to main route if no subdomain is found
    }
}
