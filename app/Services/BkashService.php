<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class BkashService
{
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('BKASH_BASE_URL');
    }
    //grant token
    public function grantToken()
    {
        return Http::withHeaders([
            'username' => env('BKASH_USERNAME'),
            'password' => env('BKASH_PASSWORD'),
        ])->post("{$this->baseUrl}/tokenized/checkout/token/grant", [
            'app_key'    => env('BKASH_APP_KEY'),
            'app_secret' => env('BKASH_APP_SECRET'),
        ])->json();
    }
}
