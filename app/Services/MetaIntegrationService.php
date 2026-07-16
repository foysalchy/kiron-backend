<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MetaIntegrationService
{
    protected ?string $version = null;
    protected ?string $appId = null;
    protected ?string $appSecret = null;
    protected ?string $redirectUri = null;

    public function __construct()
    {
        $this->version = (string) config('services.meta.version', 'v20.0');
        $this->appId = (string) config('services.meta.app_id');
        $this->appSecret = (string) config('services.meta.app_secret');
        $this->redirectUri = (string) config('services.meta.redirect_uri');
    }


    public function loginUrl(string $state, array $scopes): string
    {
        return "https://www.facebook.com/{$this->version}/dialog/oauth?" . http_build_query([
            'client_id' => $this->appId,
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
            'scope' => implode(',', $scopes),
            'response_type' => 'code',
        ]);
    }

    public function exchangeCodeForToken(string $code): array
    {
        $res = Http::get("https://graph.facebook.com/{$this->version}/oauth/access_token", [
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
        ])->throw()->json();

        // exchange short-lived for long-lived token
        $long = Http::get("https://graph.facebook.com/{$this->version}/oauth/access_token", [
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->appId,
            'client_secret' => $this->appSecret,
            'fb_exchange_token' => $res['access_token'],
        ])->throw()->json();

        return $long; // ['access_token' => ..., 'expires_in' => ...]
    }

    public function getBusinesses(string $userToken): array
    {
        return Http::get("https://graph.facebook.com/{$this->version}/me/businesses", [
            'access_token' => $userToken,
        ])->throw()->json('data', []);
    }

    public function getPages(string $userToken, ?string $businessId = null): array
    {
        $url = $businessId
            ? "https://graph.facebook.com/{$this->version}/{$businessId}/owned_pages"
            : "https://graph.facebook.com/{$this->version}/me/accounts";

        return Http::get($url, [
            'access_token' => $userToken,
            'fields' => 'id,name,access_token,instagram_business_account{id,username}',
        ])->throw()->json('data', []);
    }

    public function getWhatsappBusinessAccounts(string $userToken): array
    {
        return Http::get("https://graph.facebook.com/{$this->version}/me/businesses", [
            'access_token' => $userToken,
            'fields' => 'owned_whatsapp_business_accounts{id,name,phone_numbers{id,display_phone_number,verified_name}}',
        ])->throw()->json('data', []);
    }

    public function subscribePageToWebhooks(string $pageId, string $pageAccessToken): void
    {
        Http::post("https://graph.facebook.com/{$this->version}/{$pageId}/subscribed_apps", [
            'access_token' => $pageAccessToken,
            'subscribed_fields' => 'messages,messaging_postbacks,message_deliveries,message_reads',
        ])->throw();
    }

    public function registerWhatsappPhoneNumber(string $phoneNumberId, string $wabaToken, string $pin): void
    {
        Http::post("https://graph.facebook.com/{$this->version}/{$phoneNumberId}/register", [
            'access_token' => $wabaToken,
            'messaging_product' => 'whatsapp',
            'pin' => $pin,
        ])->throw();
    }
}
