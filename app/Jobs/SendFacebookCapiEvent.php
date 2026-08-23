<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendFacebookCapiEvent implements ShouldQueue
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
            'data' => [
                [
                    'event_name' => $this->eventName,
                    'event_time' => $this->eventData['event_time'] ?? time(),
                    'action_source' => 'website',
                    'event_source_url' => $this->eventSourceUrl ?? config('app.url'),
                    'user_data' => $this->userData,
                    'custom_data' => $this->customData,
                ]
            ]
        ];

        if ($this->eventId) {
            $payload['data'][0]['event_id'] = $this->eventId;
        }

        try {
            $response = Http::post("https://graph.facebook.com/v19.0/{$this->pixelId}/events?access_token={$this->accessToken}", $payload);
            
            if (!$response->successful()) {
                Log::error('Facebook CAPI Error', ['response' => $response->json()]);
            }
        } catch (\Exception $e) {
            Log::error('Facebook CAPI Exception', ['message' => $e->getMessage()]);
        }
    }
}
