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
        
        
        // SaaS Domain
        if ($host === $mainDomain) {
            return redirect()->route('saas.index');
        }
        // Subdomain (*.dorja.io)
        if (str_ends_with($host, '.' . $mainDomain)) {

            $subdomain = explode('.', $host)[0];

            URL::defaults([
                'store' => $subdomain
            ]);

            return $next($request);
        }

        // Custom Domain
        $store = \App\Models\DomainSetup::where('custom_domain', $host)->first();

        if ($store) {
            URL::defaults([
                'store' => $store->subdomain
            ]);

            return $next($request);
        }

        abort(404);
    }
}
