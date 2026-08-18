<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasSubdomainCache
{
    public static function clearSubdomainCache(string $subdomain)
    {
        Cache::forget("store_{$subdomain}");
    }
}