<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

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

        // Remove www.
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }


        $mainDomain = 'dorja.io';
        
        // Allow direct preview routes on dorja.io or any domain without redirection
        if ($request->is('landing-preview*') || $request->is('theme-preview*')) {
            return $next($request);
        }
        
        // SaaS Domain
        if ($host === $mainDomain) {
            return redirect()->route('saas.index');
        }
        // 1. Custom Domain Check: runs directly with custom domain without needing any subdomain
        $store = \App\Models\DomainSetup::withoutGlobalScopes()
            ->where(function ($q) use ($host) {
                $q->where('custom_domain', $host)
                  ->orWhere('custom_domain', 'www.' . $host);
            })
            ->first();

        if ($store) {
            $storeIdentifier = $store->custom_domain ?: $store->sub_domain;
            app()->instance('currentStore', $storeIdentifier);
            app()->instance('store', $store);
            URL::defaults([
                'store' => $storeIdentifier
            ]);

            return $next($request);
        }

        // 2. Subdomain (*.dorja.io)
        if (str_ends_with($host, '.' . $mainDomain)) {

            $subdomain = explode('.', $host)[0];

            URL::defaults([
                'store' => $subdomain
            ]);
            app()->instance('currentStore', $subdomain);

            return $next($request);
        }
        if ($host == '127.0.0.1') {
            app()->instance('currentStore', 'babyshop');
            URL::defaults([
                'store' => 'babyshop'
            ]);

            return $next($request);
        }

        abort(404);
    }
}