<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppWebController extends Controller
{
    private string $gatewayUrl;

    public function __construct()
    {
        $this->gatewayUrl = env('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3001');
    }

    /**
     * Get or generate QR code for WhatsApp Web connect
     */
    public function getQrCode(Request $request)
    {
        $companyId = $request->user()->company_id;
        $sessionId = "comp_{$companyId}";

        try {
            $response = Http::timeout(10)->get("{$this->gatewayUrl}/qr/{$sessionId}");
            if ($response->successful()) {
                $data = $response->json();

                // If already connected, auto-register / sync Channel
                if (($data['status'] ?? '') === 'connected' && !empty($data['phone'])) {
                    $channel = $this->ensureWhatsAppChannel($companyId, $data['phone'], $data['name'] ?? null);
                    return response()->json([
                        'status' => 'connected',
                        'phone' => $data['phone'],
                        'name' => $data['name'] ?? $channel->name,
                        'channel_id' => $channel->id,
                    ]);
                }

                return response()->json([
                    'status' => $data['status'] ?? 'waiting_for_scan',
                    'qr' => $data['qr'] ?? null,
                ]);
            }

            return response()->json(['error' => 'WhatsApp Gateway is not responding.'], 503);
        } catch (\Exception $e) {
            Log::error("WhatsApp QR Error: " . $e->getMessage());
            return response()->json(['error' => 'Could not connect to WhatsApp Gateway service.'], 500);
        }
    }

    /**
     * Check connection status of WhatsApp session
     */
    public function checkStatus(Request $request)
    {
        $companyId = $request->user()->company_id;
        $sessionId = "comp_{$companyId}";

        try {
            $response = Http::timeout(5)->get("{$this->gatewayUrl}/status/{$sessionId}");
            if ($response->successful()) {
                $data = $response->json();

                if (($data['status'] ?? '') === 'connected' && !empty($data['phone'])) {
                    $channel = $this->ensureWhatsAppChannel($companyId, $data['phone'], $data['name'] ?? null);
                    return response()->json([
                        'status' => 'connected',
                        'phone' => $data['phone'],
                        'name' => $channel->name,
                        'channel_id' => $channel->id,
                    ]);
                }

                return response()->json(['status' => $data['status'] ?? 'disconnected']);
            }

            return response()->json(['status' => 'disconnected']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'disconnected', 'error' => $e->getMessage()]);
        }
    }

    /**
     * Disconnect / Logout WhatsApp Session
     */
    public function disconnect(Request $request, $channelId)
    {
        $channel = Channel::where('id', $channelId)
            ->where('company_id', $request->user()->company_id)
            ->firstOrFail();

        $companyId = $request->user()->company_id;
        $sessionId = "comp_{$companyId}";

        try {
            Http::timeout(5)->post("{$this->gatewayUrl}/logout/{$sessionId}");
        } catch (\Exception $e) {
            Log::warning("WhatsApp Logout Error: " . $e->getMessage());
        }

        $channel->delete();

        return response()->json(['message' => 'WhatsApp channel disconnected successfully.']);
    }

    /**
     * Helper: Create or update Channel for WhatsApp Web
     */
    private function ensureWhatsAppChannel(int $companyId, string $phone, ?string $name = null): Channel
    {
        $group = ChannelGroup::firstOrCreate(
            ['platform' => 'whatsapp', 'company_id' => $companyId],
            [
                'profile_name' => 'WhatsApp Business',
                'profile_image' => null,
            ]
        );

        $displayName = $name ?: "WhatsApp ({$phone})";
        $pageId = "wa_{$phone}";

        return Channel::updateOrCreate(
            ['page_id' => $pageId, 'company_id' => $companyId],
            [
                'channel_group_id' => $group->id,
                'name' => $displayName,
                'type' => 'whatsapp',
                'slug' => 'wa-' . $phone,
                'profile_image' => null,
                'page_token' => 'wa_web_session',
                'color' => '#22C35E',
            ]
        );
    }
}

