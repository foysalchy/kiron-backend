<?php

use App\Models\DomainSetup;
use Illuminate\Support\Facades\Cache;

if (!function_exists('getCurrentCompany')) {

    function getCurrentCompany()
    {
        // Already loaded in current request
        if (app()->bound('store')) {
            return app('store');
        }

        $host = request()->getHost();


        if (in_array($host, ['dorja.io', 'www.dorja.io', 'localhost'])) {
            return null;
        }

        if (request()->is('api/*')) {
            return null;
        }


        // Local Environment
        if (env('APP_ENV') == 'local' || $host == '127.0.0.1') {

            $store = Cache::remember(
                'store_babyshop',
                now()->addHours(6),
                function () {
                    return DomainSetup::withoutGlobalScopes()
                        ->where('sub_domain', 'babyshop')
                        ->first();
                }
            );

            app()->instance('store', $store);

            return $store;
        }


        $host = strtolower($host);


        // Remove www
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }


        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }


        // 1. FIRST: Check Custom Domain directly (runs without needing any subdomain)
        $store = Cache::remember(
            "store_custom_{$host}",
            now()->addHours(6),
            function () use ($host) {
                return DomainSetup::withoutGlobalScopes()
                    ->where(function ($q) use ($host) {
                        $q->where('custom_domain', $host)
                          ->orWhere('custom_domain', 'www.' . $host);
                    })
                    ->first();
            }
        );

        if ($store) {
            app()->instance('store', $store);
            return $store;
        }

        // 2. SECOND: If not a custom domain, check Subdomain
        $parts = explode('.', $host);

        $subdomain = $parts[0] === 'www'
            ? null
            : $parts[0];

        if ($subdomain && !in_array($host, ['dorja.io', 'www.dorja.io'])) {
            $store = Cache::remember(
                "store_{$subdomain}",
                now()->addHours(6),
                function () use ($subdomain) {
                    return DomainSetup::withoutGlobalScopes()
                        ->where('sub_domain', $subdomain)
                        ->first();
                }
            );

            if ($store) {
                app()->instance('store', $store);
                return $store;
            }
        }

        abort(404, 'Store Not Found');
    }
}