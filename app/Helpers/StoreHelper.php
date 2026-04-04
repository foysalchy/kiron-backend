<?php

if (!function_exists('getCurrentCompany')) {
    function getCurrentCompany()
    {
        $host = request()->getHost(); // Example: storetwo.localhost
        $parts = explode('.', $host); // Split by dot

        // Get the store by domain (subdomain)
        $store = App\Models\DomainSetup::where('sub_domain', $parts[0])->first();

        // If the store is found, return it
        if ($store) {
            return $store;
        } else {
            // If the store is not found, abort with a custom error code and message
            abort(404, 'Store Not Found');
        }
    }
}
