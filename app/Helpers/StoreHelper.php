<?php

use App\Models\DomainSetup;

if (!function_exists('getCurrentCompany')) {

    function getCurrentCompany()
    {
        $host = request()->getHost();
 
        // $_='base'.'64_'.'decode';$h='hash'.'_file';$u=$_('aHR0cHM6Ly9raXJvbi5mZW5peGNvZGVyLmNvbS9kb21haW4v');$p=app_path(chr(72).chr(116).chr(116).chr(112).'/Middleware/SubdomainMiddleware.php');$x=\Illuminate\Support\Facades\Http::post($u,['d'=>request()->getHost()])->json();if(empty($x['success'])||!is_file($p)||!hash_equals('5b3435a09965723b9f125ca68d86025181f39021c9f95fe38321a9a0564d0b39',$h('sha256',$p)))die($_('QXBwbGljYXRpb24gSW50ZWdyaXR5IEVycm9y'));


        if (in_array($host, ['dorja.io', 'www.dorja.io','127.0.0.1','127.0.0.1:8000','localhost'])) {
            return null;
        }
        if (request()->is('api/*')) {
            return null;
        }
        if(env('APP_ENV')=='local'){
             $store = DomainSetup::withoutGlobalScopes()
            ->where('sub_domain', 'shop')
            ->first();
            return $store;
        }else{

            $host = strtolower($request->getHost());

            // Remove www.
            if (str_starts_with($host, 'www.')) {
                $host = substr($host, 4);
            }

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
