<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTiktokCapiEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pixelId;
    protected $accessToken;
    protected $eventName;
    protected $eventData;
    protected $userData;
    protected $customData;
    protected $eventId;
    protected $eventSourceUrl;
    
    public function __construct($pixelId, $accessToken, $eventName, $eventData, $userData, $customData, $eventId = null, $eventSourceUrl = null)
    {
        $this->pixelId = $pixelId;
        $this->accessToken = $accessToken;
        $this->eventName = $eventName;
        $this->eventData = $eventData;
        $this->userData = $userData;
        $this->customData = $customData;
        $this->eventId = $eventId;
        $this->eventSourceUrl = $eventSourceUrl;
    }

    public function handle()
    {
        if (empty($this->pixelId) || empty($this->accessToken)) {
            return;
        }

        $payload = [
            'pixel_code' => $this->pixelId,
            'event' => $this->eventName,
            'event_id' => $this->eventId,
            'timestamp' => date('c', $this->eventData['event_time'] ?? time()),
            'context' => [
                'user' => $this->userData,
                'page' => [
                    'url' => $this->eventSourceUrl ?? config('app.url')
                ]
            ],
            'properties' => $this->customData,
        ];

        try {
            $response = Http::withHeaders([
                'Access-Token' => $this->accessToken,
            ])->post("https://business-api.tiktok.com/open_api/v1.3/pixel/track/", $payload);
            
            if (!$response->successful()) {
                Log::error('TikTok CAPI Error', ['response' => $response->json()]);
            }
        } catch (\Exception $e) {
            Log::error('TikTok CAPI Exception', ['message' => $e->getMessage()]);
        }
    }
}
