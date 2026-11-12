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
use Illuminate\Support\Facades\Storage;

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

        $query = Conversation::where('channel_id', $channel->id)
            ->where('company_id', $channel->company_id)
            ->with(['customer', 'assignedUser', 'messages' => fn($q) => $q->latest()->limit(1)]);

        // Optional search query by customer name or phone
        if ($search = $request->input('search')) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $conversations = $query->orderByDesc('last_activity_at')->get();

        $formatted = $conversations->map(function ($convo) {
            $lastMsg = $convo->messages->first();
            $customerName = $convo->customer?->name ?: 'WhatsApp User';
            $customerPhone = $convo->customer?->phone ?: '';
            $customerImage = $convo->customer?->image ?: null;

            $unreadCount = $convo->messages()->where('from', 'customer')->where('is_read', false)->count();

            return [
                'id' => (string)$convo->id,
                'customer_name' => $customerName,
                'customer_id' => $customerPhone ?: (string)$convo->customer_id,
                'customer_phone' => $customerPhone,
                'customer_image' => $customerImage,
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
     * Find or initiate a WhatsApp conversation by phone number
     */
    public function findOrCreateConversation(Request $request)
    {
        $companyId = $request->user()->company_id;
        $phone = $request->input('phone');

        if (!$phone) {
            return response()->json(['error' => 'Phone number is required.'], 422);
        }

        // Clean phone digits
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '880')) {
            $normalizedPhone = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $normalizedPhone = '880' . substr($digits, 1);
        } else {
            $normalizedPhone = '880' . $digits;
        }

        $channel = Channel::where('company_id', $companyId)->where('type', 'whatsapp')->first();
        if (!$channel) {
            return response()->json(['error' => 'No active WhatsApp channel found.'], 404);
        }

        // Find or create customer party
        $party = Party::where('company_id', $companyId)
            ->where(function ($q) use ($normalizedPhone, $phone) {
                $q->where('phone', $normalizedPhone)
                  ->orWhere('phone', $phone)
                  ->orWhere('phone', 'like', "%{$normalizedPhone}%");
            })
            ->first();

        if (!$party) {
            $party = Party::create([
                'company_id' => $companyId,
                'name' => "WhatsApp ({$normalizedPhone})",
                'phone' => "{$normalizedPhone}@s.whatsapp.net",
                'type' => 2, // Customer
                'status' => Status::Active->value,
            ]);
        }

        $conversation = Conversation::firstOrCreate(
            ['company_id' => $companyId, 'channel_id' => $channel->id, 'customer_id' => $party->id],
            ['status' => 'open', 'last_activity_at' => now()]
        );

        return response()->json([
            'conversation_id' => (string)$conversation->id,
            'channel_id' => $channel->id,
            'channel_slug' => $channel->slug,
            'customer_name' => $party->name,
            'customer_phone' => $party->phone,
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

        $fileUrls = [];
        if (!empty($data['file_urls'])) {
            foreach ($data['file_urls'] as $fUrl) {
                if ($fUrl) {
                    if (!str_starts_with($fUrl, 'http://') && !str_starts_with($fUrl, 'https://')) {
                        $fUrl = Storage::disk('r2')->url($fUrl);
                    }
                    $fileUrls[] = $fUrl;
                }
            }
        } elseif (!empty($data['file_url'])) {
            $singleUrl = $data['file_url'];
            if (!str_starts_with($singleUrl, 'http://') && !str_starts_with($singleUrl, 'https://')) {
                $singleUrl = Storage::disk('r2')->url($singleUrl);
            }
            $fileUrls[] = $singleUrl;
        }

        $filePath = !empty($fileUrls) ? $fileUrls[0] : null;
        $fileName = null;
        $type = !empty($fileUrls) ? 'image' : 'text';

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $rawPath = FileUploadHelper::upload($file, 'inbox-attachments', 'r2');
            $filePath = Storage::disk('r2')->url($rawPath);
            $fileUrls[] = $filePath;
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
                'fileUrls' => $fileUrls,
            ];

            Log::info("WhatsApp Sending via Gateway: URL={$this->gatewayUrl}/send/{$sessionId}, Phone={$customerPhone}");
            $gwResponse = Http::timeout(15)->post("{$this->gatewayUrl}/send/{$sessionId}", $postData);
            Log::info("WhatsApp Gateway Send response: " . $gwResponse->body());

            if ($gwResponse->failed()) {
                Log::error("WhatsApp Gateway Send failed: " . $gwResponse->body());
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp Gateway Send Exception: " . $e->getMessage());
        }

        // Save into messages table
        $savedMessages = [];
        if (!empty($fileUrls)) {
            foreach ($fileUrls as $idx => $url) {
                $msg = $conversation->messages()->create([
                    'from' => 'agent',
                    'type' => 'image',
                    'text' => ($idx === 0) ? ($data['text'] ?? null) : null,
                    'file_path' => $url,
                    'file_name' => $fileName,
                    'agent_id' => $request->user()->id,
                    'is_read' => true,
                ]);
                $savedMessages[] = $msg;
            }
        } else {
            $msg = $conversation->messages()->create([
                'from' => 'agent',
                'type' => $type,
                'text' => $data['text'] ?? null,
                'file_path' => null,
                'file_name' => null,
                'agent_id' => $request->user()->id,
                'is_read' => true,
            ]);
            $savedMessages[] = $msg;
        }

        $conversation->update(['last_activity_at' => now()]);

        $lastMessage = end($savedMessages);

        return response()->json([
            'id' => (string)$lastMessage->id,
            'from' => 'agent',
            'type' => $lastMessage->type,
            'text' => $lastMessage->text,
            'file_path' => $lastMessage->file_path,
            'created_at' => $lastMessage->created_at->toIso8601String(),
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

        $senderPhone = $payload['senderPhone'] ?? '';
        $remoteJid = $payload['remoteJid'] ?? $senderPhone;
        $pushName = $payload['pushName'] ?: "WhatsApp ({$senderPhone})";
        $profilePicUrl = $payload['profilePicUrl'] ?? null;
        $text = $payload['text'] ?? '';
        $fromMe = $payload['fromMe'] ?? false;

        // Ensure Party (Customer)
        $party = Party::where('company_id', $companyId)
            ->where(function ($q) use ($senderPhone, $remoteJid) {
                $q->where('phone', $senderPhone)
                  ->orWhere('phone', $remoteJid)
                  ->orWhere('phone', 'like', "%{$senderPhone}%");
            })
            ->first();

        if (!$party) {
            $party = Party::create([
                'company_id' => $companyId,
                'name' => $pushName,
                'phone' => $remoteJid, // Store full JID so reply reaches exact WhatsApp account
                'image' => $profilePicUrl,
                'type' => 2, // Customer
                'status' => Status::Active->value,
            ]);
        } else {
            $updates = [];
            if (!empty($pushName) && $pushName !== $senderPhone && $party->name !== $pushName) {
                $updates['name'] = $pushName;
            }
            if ($profilePicUrl && $party->image !== $profilePicUrl) {
                $updates['image'] = $profilePicUrl;
            }
            if (!empty($remoteJid) && !str_contains($party->phone, '@')) {
                $updates['phone'] = $remoteJid;
            }
            if (!empty($updates)) {
                $party->update($updates);
            }
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

        // Trigger AI Auto-Response for WhatsApp if incoming from customer
        if (!$fromMe && !empty($text)) {
            $this->handleAiAutoResponse($channel, $conversation, $party, $text);
        }

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

    /**
     * AI Auto-Response for WhatsApp
     */
    private function handleAiAutoResponse(Channel $channel, Conversation $conversation, Party $customer, string $customerText)
    {
        try {
            $settings = \App\Models\OmniSetting::where('company_id', $channel->company_id)->first();
            if (!$settings || !$settings->ai_auto_response || !$settings->ai_whatsapp_active) {
                return;
            }

            // Assignment Check
            // Rule:
            // 1. If assigned to a human agent ($conversation->assigned_user_id exists):
            //    Check when the agent last manually replied.
            //    If human agent replied within the last 5 minutes, AI stays silent.
            //    If 5+ minutes have passed without a human reply, AI steps in to help.
            // 2. If unassigned:
            //    AI responds immediately to every customer message.
            if ($conversation->assigned_user_id) {
                $lastHumanMsg = $conversation->messages()
                    ->where('from', 'agent')
                    ->whereNotNull('agent_id')
                    ->latest()
                    ->first();

                // If human agent replied less than 5 minutes ago, let human handle it
                if ($lastHumanMsg && now()->diffInMinutes($lastHumanMsg->created_at) < 5) {
                    Log::info("WhatsApp AI: Skipped AI reply because conversation is assigned to agent (User #{$conversation->assigned_user_id}) and last human reply was within 5 min.");
                    return;
                }
            }

            $aiSetting = \App\Models\AiSetting::where('company_id', $channel->company_id)->first();
            if (!$aiSetting || (!$aiSetting->gemini_status && !$aiSetting->openai_status)) {
                Log::warning("WhatsApp AI: AI enabled but no AI provider configured.");
                return;
            }

            // Company/Shop info
            $siteSetting = \App\Models\SiteSetting::where('company_id', $channel->company_id)->first();
            $shopName = $siteSetting->shop_name ?? ($channel->company->name ?? 'Our Shop');
            $shopPhone = $siteSetting->phone ?? ($siteSetting->hotline ?? null);
            $shopAddress = $siteSetting->address ?? null;

            // Smart Product Search in Database based on Customer Query (Title, Description, Slug, Keywords)
            $targetedProducts = collect();
            $cleanKeywords = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $customerText);
            $words = array_filter(explode(' ', $cleanKeywords), fn($w) => mb_strlen($w) >= 3);

            if (!empty($words)) {
                $searchQuery = \App\Models\Product::where('company_id', $channel->company_id)
                    ->where('status', \App\Enums\Status::Active->value)
                    ->where(function ($q) use ($words) {
                        foreach ($words as $w) {
                            $q->orWhere('title', 'like', "%{$w}%")
                              ->orWhere('slug', 'like', "%{$w}%")
                              ->orWhere('short_description', 'like', "%{$w}%");
                        }
                    });
                $targetedProducts = $searchQuery->limit(10)->get();
            }

            // Also check for direct URL or slug
            if (preg_match('/(?:https?:\/\/[^\/]+\/)?([^\s\?\#]+)/', $customerText, $urlMatches)) {
                $possibleSlug = trim($urlMatches[1], '/');
                $slugProduct = \App\Models\Product::where('company_id', $channel->company_id)
                    ->where('slug', $possibleSlug)
                    ->first();
                if ($slugProduct) {
                    $targetedProducts->push($slugProduct);
                }
            }

            // General active products
            $generalProducts = \App\Models\Product::where('company_id', $channel->company_id)
                ->where('status', \App\Enums\Status::Active->value)
                ->latest()
                ->limit(35)
                ->get();

            // Merge search results first, then general products (unique by id)
            $products = $targetedProducts->merge($generalProducts)->unique('id');

            $productSummary = $products->map(function ($p) {
                $pTitle = $p->title ?? $p->name ?? 'Product';
                $slug = $p->slug ?? '';
                $regPrice = (float)($p->regular_price ?? 0);
                $salePrice = (float)($p->sale_price ?? $regPrice);
                $hasDiscount = $p->discount > 0 && $salePrice < $regPrice;

                $priceText = "Price: ৳{$salePrice}";
                if ($hasDiscount) {
                    $discountStr = $p->discount_type === 'percent' ? "{$p->discount}% off" : "৳{$p->discount} off";
                    $priceText = "Regular Price: ৳{$regPrice}, Special Offer/Discount Price: ৳{$salePrice} ({$discountStr})";
                }

                // Stock status evaluation: if manage_stock is off or stock_status is in_stock, it is available
                $isAvailable = true;
                if ($p->manage_stock) {
                    $isAvailable = ($p->stock_status === 'in_stock' && ($p->stock_quantity > 0 || $p->stock_quantity === null));
                } else {
                    $isAvailable = ($p->stock_status !== 'out_of_stock');
                }

                $stockText = $isAvailable ? 'In Stock (Available)' : 'Out of Stock';
                return "- {$pTitle} (Slug/Link: {$slug}) | {$priceText} | Stock: {$stockText}";
            })->implode("\n");

            // Fetch recent conversation history so AI remembers the context (e.g. what product customer asked for)
            $recentMessages = $conversation->messages()
                ->latest()
                ->take(6)
                ->get()
                ->reverse()
                ->map(function ($m) {
                    $sender = $m->from === 'customer' ? 'Customer' : 'Assistant';
                    return "{$sender}: {$m->text}";
                })
                ->implode("\n");

            $detectLanguage = preg_match('/[\x{0980}-\x{09FF}]/u', $customerText) ? 'bn' : 'en';

            $promptParams = [
                'type' => 'omni_chat',
                'language' => $detectLanguage,
                'customer_message' => $customerText,
                'recent_chat_history' => $recentMessages,
                'shop_name' => $shopName,
                'contact_info' => $shopPhone ? "Phone: {$shopPhone}, Address: {$shopAddress}" : null,
                'available_products' => $productSummary,
            ];

            if (!empty($settings->ai_custom_instructions)) {
                $promptParams['special_company_instructions'] = $settings->ai_custom_instructions;
            }

            if (!empty($settings->ai_training_data)) {
                $promptParams['company_knowledge_base_training'] = $settings->ai_training_data;
            }

            $aiReply = \App\Services\AI\AiService::generate($channel->company_id, $promptParams);

            if (!empty($aiReply)) {
                $sessionId = "comp_{$channel->company_id}";
                $cleanReply = trim($aiReply) . "\n\n— 🤖 AI Assistant";

                // Send to WhatsApp Gateway
                $postData = [
                    'phone' => $customer->phone,
                    'text' => $cleanReply,
                ];

                $gwResponse = Http::timeout(10)->post("{$this->gatewayUrl}/send/{$sessionId}", $postData);

                if ($gwResponse->successful()) {
                    // Record in conversation messages (agent_id null indicates AI bot)
                    $conversation->messages()->create([
                        'from' => 'agent',
                        'type' => 'text',
                        'text' => $cleanReply,
                        'agent_id' => null,
                        'is_read' => true,
                    ]);
                    $conversation->update(['last_activity_at' => now()]);
                    Log::info("WhatsApp AI: Successfully sent auto-reply to {$customer->phone}");

                    // Check if AI reply or customer message confirmed an order -> Auto Tag 'Pending Order'
                    $lowerReply = mb_strtolower($cleanReply);
                    if (str_contains($lowerReply, 'অর্ডারটি সফলভাবে কনফার্ম') || str_contains($lowerReply, 'অর্ডার কনফার্ম') || str_contains($lowerReply, 'order confirmed') || str_contains($lowerReply, 'order has been placed')) {
                        $label = \App\Models\Label::firstOrCreate(
                            ['company_id' => $channel->company_id, 'name' => 'Pending Order'],
                            ['color' => '#EAB308', 'status' => \App\Enums\Status::Active->value] // Yellow amber badge
                        );
                        $customer->labels()->syncWithoutDetaching([$label->id]);
                        Log::info("WhatsApp AI: Tagged Party #{$customer->id} with 'Pending Order' label.");
                    }
                } else {
                    Log::error("WhatsApp AI: Failed sending to gateway: " . $gwResponse->body());
                }
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp AI Auto-Response Error: " . $e->getMessage());
        }
    }
}

