<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
class SubdomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $host = strtolower($request->getHost());

        $mainDomain = 'dorja.io';

        // SaaS Domain
        if ($host == $mainDomain || $host == "www.$mainDomain") {
            return redirect()->route('saas.index');
        }

        // Subdomain
        if (str_ends_with($host, '.' . $mainDomain)) {

            $subdomain = explode('.', $host)[0];

            URL::defaults(['store' => $subdomain]);

            return $next($request);
        }

        // Custom Domain
        $store = \App\Models\Store::where('domain', $host)->first();

        if ($store) {

            URL::defaults(['store' => $store->slug]);

            return $next($request);
        }

        abort(404);
    }
}
