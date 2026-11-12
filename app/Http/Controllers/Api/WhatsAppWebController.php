<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Party;
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
     * Get conversations for WhatsApp Channel
     */
    public function getConversations($channelId, Request $request)
    {
        $channel = Channel::where('id', $channelId)
            ->where('company_id', $request->user()->company_id)
            ->firstOrFail();

        $conversations = Conversation::where('channel_id', $channel->id)
            ->where('company_id', $channel->company_id)
            ->with(['customer', 'assignedUser', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_activity_at')
            ->get();

        $formatted = $conversations->map(function ($convo) {
            $lastMsg = $convo->messages->first();
            $customerName = $convo->customer?->name ?: 'WhatsApp User';
            $customerPhone = $convo->customer?->phone ?: '';

            $unreadCount = $convo->messages()->where('from', 'customer')->where('is_read', false)->count();

            return [
                'id' => (string)$convo->id,
                'customer_name' => $customerName . ($customerPhone ? " ({$customerPhone})" : ''),
                'customer_id' => $customerPhone ?: (string)$convo->customer_id,
                'last_message' => $lastMsg?->text ?? ($lastMsg?->type === 'image' ? '[Photo]' : 'No message'),
                'unread_count' => $unreadCount,
                'last_activity' => $convo->last_activity_at?->toIso8601String() ?? $convo->updated_at->toIso8601String(),
                'assigned_users' => $convo->assignedUser ? [
                    ['id' => $convo->assignedUser->id, 'name' => $convo->assignedUser->name]
                ] : [],
                'is_archived' => $convo->status === 'closed',
            ];
        });

        return response()->json([
            'data' => $formatted,
            'next_cursor' => null,
            'has_more' => false,
        ]);
    }

    /**
     * Get message history for a specific conversation
     */
    public function getMessages($channelId, $threadId)
    {
        $channel = Channel::findOrFail($channelId);

        $conversation = Conversation::where('id', $threadId)
            ->where('channel_id', $channel->id)
            ->firstOrFail();

        $messages = $conversation->messages()->orderBy('created_at', 'asc')->get();

        $formatted = $messages->map(function ($msg) {
            $attachments = [];
            if ($msg->file_path) {
                $attachments[] = [
                    'type' => $msg->type === 'image' ? 'image' : 'document',
                    'url' => $msg->file_path,
                    'name' => $msg->file_name,
                ];
            }

            return [
                'id' => (string)$msg->id,
                'from' => $msg->from, // 'agent' or 'customer'
                'type' => $msg->type,
                'text' => $msg->text ?? '',
                'file_path' => $msg->file_path,
                'attachments' => $attachments,
                'created_at' => $msg->created_at->toIso8601String(),
            ];
        });

        // Mark messages as read
        $conversation->messages()->where('from', 'customer')->where('is_read', false)->update(['is_read' => true]);

        return response()->json($formatted);
    }

    /**
     * Send message from Omni Inbox via WhatsApp Gateway
     */
    public function sendMessage(Request $request, $channelId, $threadId)
    {
        $channel = Channel::findOrFail($channelId);

        $conversation = Conversation::where('id', $threadId)
            ->where('channel_id', $channel->id)
            ->with('customer')
            ->firstOrFail();

        $data = $request->validate([
            'text' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
            'file_url' => 'nullable|string',
            'file_urls' => 'nullable|array',
        ]);

        $filePath = $data['file_url'] ?? null;
        $fileName = null;
        $type = 'text';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = FileUploadHelper::upload($file, 'inbox-attachments');
            $fileName = $file->getClientOriginalName();
            $type = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'document';
        }

        $customerPhone = $conversation->customer?->phone;
        if (!$customerPhone) {
            return response()->json(['error' => 'Customer phone number is missing.'], 400);
        }

        $companyId = $channel->company_id;
        $sessionId = "comp_{$companyId}";

        // Send via Gateway
        try {
            $postData = [
                'phone' => $customerPhone,
                'text' => $data['text'] ?? '',
                'fileUrl' => $filePath,
            ];

            $gwResponse = Http::timeout(10)->post("{$this->gatewayUrl}/send/{$sessionId}", $postData);
            if ($gwResponse->failed()) {
                Log::error("WhatsApp Gateway Send failed: " . $gwResponse->body());
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp Gateway Send Exception: " . $e->getMessage());
        }

        // Save into messages table
        $message = $conversation->messages()->create([
            'from' => 'agent',
            'type' => $type,
            'text' => $data['text'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'agent_id' => $request->user()->id,
            'is_read' => true,
        ]);

        $conversation->update(['last_activity_at' => now()]);

        return response()->json([
            'id' => (string)$message->id,
            'from' => 'agent',
            'type' => $type,
            'text' => $message->text,
            'file_path' => $message->file_path,
            'created_at' => $message->created_at->toIso8601String(),
        ]);
    }

    /**
     * Webhook receiver for WhatsApp Gateway (incoming messages)
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        $event = $payload['event'] ?? '';
        $sessionId = $payload['sessionId'] ?? '';

        if ($event !== 'message.received' || empty($sessionId)) {
            return response()->json(['status' => 'ignored']);
        }

        // Extract companyId from sessionId e.g. "comp_1"
        $companyId = (int)str_replace('comp_', '', $sessionId);
        if (!$companyId) {
            return response()->json(['error' => 'Invalid session ID'], 400);
        }

        $channel = Channel::where('company_id', $companyId)->where('type', 'whatsapp')->first();
        if (!$channel) {
            return response()->json(['error' => 'Channel not found'], 404);
        }

        $senderPhone = preg_replace('/\D/', '', $payload['senderPhone'] ?? '');
        $pushName = $payload['pushName'] ?: "WhatsApp ({$senderPhone})";
        $text = $payload['text'] ?? '';
        $fromMe = $payload['fromMe'] ?? false;

        // Ensure Party (Customer)
        $party = Party::where('company_id', $companyId)
            ->where(function ($q) use ($senderPhone) {
                $q->where('phone', $senderPhone)
                  ->orWhere('phone', 'like', "%{$senderPhone}%");
            })
            ->first();

        if (!$party) {
            $party = Party::create([
                'company_id' => $companyId,
                'name' => $pushName,
                'phone' => $senderPhone,
                'type' => 2, // Customer
                'status' => Status::Active->value,
            ]);
        }

        // Find or create Conversation
        $conversation = Conversation::firstOrCreate(
            ['company_id' => $companyId, 'channel_id' => $channel->id, 'customer_id' => $party->id],
            ['status' => 'open', 'last_activity_at' => now()]
        );

        $conversation->update(['last_activity_at' => now()]);

        // Save Message
        $conversation->messages()->create([
            'from' => $fromMe ? 'agent' : 'customer',
            'type' => 'text',
            'text' => $text,
            'is_read' => $fromMe,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Helper: Create or update Channel for WhatsApp Web
     */
    private function ensureWhatsAppChannel(int $companyId, string $phone, ?string $name = null): Channel
    {
        $group = ChannelGroup::withoutGlobalScopes()->firstOrCreate(
            ['platform' => 'whatsapp', 'company_id' => $companyId],
            [
                'profile_name' => 'WhatsApp (' . $phone . ')',
                'profile_image' => 'https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg',
                'personal_token' => 'wa_web_session_' . $phone,
            ]
        );

        $displayName = $name ?: "WhatsApp ({$phone})";
        $pageId = "wa_{$phone}";

        return Channel::withoutGlobalScopes()->updateOrCreate(
            ['page_id' => $pageId, 'company_id' => $companyId],
            [
                'channel_group_id' => $group->id,
                'name' => $displayName,
                'type' => 'whatsapp',
                'slug' => 'wa-' . $phone,
                'profile_image' => 'https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg',
                'page_token' => 'wa_web_session',
                'color' => '#22C35E',
            ]
        );
    }
}

