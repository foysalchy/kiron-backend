<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BkashService
{ 
    private $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('BKASH_BASE_URL');
    }
    /**
     * Get Common Headers
     */
    private function getHeaders(?string $token = null): array
    {
        $headers = [
            'username'     => env('BKASH_USERNAME'),
            'password'     => env('BKASH_PASSWORD'),
            'accept'       => 'application/json',
            'content-type' => 'application/json'
        ];

        if ($token) {
            $headers['Authorization'] = "Bearer $token";
            $headers['X-App-Key']     = env('BKASH_APP_KEY');
        }

        return $headers;
    }

    /**
     * Step 1: Grant Token (id_token & refresh_token)
     */
    public function grantToken(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())->post("{$this->baseUrl}/tokenized/checkout/token/grant", [
                'app_key'    => env('BKASH_APP_KEY'),
                'app_secret' => env('BKASH_APP_SECRET'),
            ]);

            $result = $response->json();

            if ($response->successful() && isset($result['id_token'])) {
                Cache::put('bkash_id_token', $result['id_token'], 3500);
                return $result;
            }

            LogHelper::error("bKash Token Failed: " . ($result['statusMessage'] ?? 'Unknown Error'));
            throw ApiException::serverError($result['statusMessage'] ?? 'Failed to get bKash token');

        } catch (\Exception $e) {
            Log::error('bKash Grant Token Error: ' . $e->getMessage());
            throw ApiException::serverError('Something went wrong with bKash Connectivity');
        }
    }


}
