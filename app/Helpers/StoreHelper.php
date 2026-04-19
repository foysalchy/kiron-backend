<?php

use App\Models\DomainSetup;

if (!function_exists('getCurrentCompany')) {
    if (!function_exists('getCurrentCompany')) {
    function getCurrentCompany()
    {
        if (request()->is('api/*')) {
            return null;
        }

        $host = request()->getHost();

        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        $parts = explode('.', $host);
        $subdomain = $parts[0] === 'www' ? null : $parts[0];

        if (!$subdomain) {
            return null;
        }

        $store = DomainSetup::withoutGlobalScopes()
            ->where('sub_domain', $subdomain)
            ->first();

        if ($store) {
            return $store;
        }

        abort(404, 'Store Not Found');
    }
}
}