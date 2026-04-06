<?php

if (!function_exists('getCurrentCompany')) {
    function getCurrentCompany()
    {
        $host = request()->getHost();
        $parts = explode('.', $host);

        $store = App\Models\DomainSetup::where('sub_domain', $parts[0])->first();

        if ($store) {
            return $store;
        }

        if (request()->is('api/*')) {
            return null;
        }

        abort(404, 'Store Not Found');
    }
}