<?php

use App\Models\DomainSetup;

if (!function_exists('getCurrentCompany')) {

    function getCurrentCompany()
    {
        if (request()->is('api/*')) {
            return null;
        }
        if(env('APP_ENV')=='local'){
             $store = DomainSetup::withoutGlobalScopes()
            ->where('sub_domain', 'khatibhai')
            ->first();
            return $store;
        }else{

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
