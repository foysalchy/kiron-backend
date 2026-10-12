<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\MetaConversationState;
use App\Models\OmniSetting;
use App\Models\ConversationAssignment;
use App\Helpers\MessagePlaceholderHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $verifyToken = config('services.meta.webhook_verify_token') ?: 'kiron_omni_webhook_token';

        if (
            $request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === $verifyToken
        ) {
            Log::info('Meta Webhook: Verification successful.');
            return response($request->get('hub_challenge'), 200);
        }

        Log::warning('Meta Webhook: Verification failed. Token mismatch.');
        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        $signature = $request->header('X-Hub-Signature-256');
        $appSecret = config('services.meta.app_secret');

        // Verify HMAC signature only if appSecret is configured
        if ($appSecret && $signature) {
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Meta Webhook: Invalid signature. Possible spoofed request.');
                return response('Forbidden', 403);
            }
        }

        $payload = $request->all();
        Log::info('Meta Webhook received: ' . json_encode($payload));

        try {
            foreach ($payload['entry'] ?? [] as $entry) {
                $pageId = $entry['id'] ?? null;
                if (!$pageId) continue;

                foreach ($entry['messaging'] ?? [] as $event) {
                    $this->processMessagingEvent($pageId, $event);
                }
            }
        } catch (\Exception $e) {
            Log::error('Meta Webhook processing error: ' . $e->getMessage());
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function processMessagingEvent(string $pageId, array $event)
    {
        $senderId = $event['sender']['id'] ?? null;
        $recipientId = $event['recipient']['id'] ?? null;

        if (!$senderId || !$recipientId) return;

        $channel = Channel::where('page_id', $pageId)->first();
        if (!$channel) {
            Log::warning("Meta Webhook: No channel found for Page ID: {$pageId}");
            return;
        }

        // Page nijeই sender hole (agent-er নিজের পাঠানো message-er echo) — ignore
        if ($senderId === $pageId) {
            return;
        }

        $customerPsid = $senderId;

        // ---- customer_psid diye match — thread_id na, karon webhook-e Meta-r thread_id (t_xxxx) paoa jay na ----
        $state = MetaConversationState::where('channel_id', $channel->id)
            ->where('customer_psid', $customerPsid)
            ->first();

        $isNewConversation = false;

        if (!$state) {
            $state = MetaConversationState::create([
                'channel_id' => $channel->id,
                'customer_psid' => $customerPsid,
                'thread_id' => null, // pore getConversations call howar shomoy sync kore nite paren, na hole na-o lagte pare
                'is_read' => false,
            ]);
            $isNewConversation = true;
        } else {
            $state->update(['is_read' => false]);
        }

        if ($isNewConversation) {
            $this->autoAssignConversation($channel, $state);

            $customerName = $event['sender']['name'] ?? 'there'; // webhook payload-e name nao thakte pare
            $this->sendWelcomeOrAwayMessage($channel, $customerPsid, $customerName);
        }

        // ---- AI Auto-Response (Gemini/OpenAI) on incoming customer messages ----
        $messageText = $event['message']['text'] ?? null;
        $referral = $event['referral'] ?? ($event['message']['referral'] ?? null);

        if (!empty($messageText) && empty($event['message']['is_echo'])) {
            $this->handleAiAutoResponse($channel, $customerPsid, $messageText, $referral, $event['timestamp'] ?? null);
        }
    }

    private function autoAssignConversation(Channel $channel, MetaConversationState $state)
    {
        $settings = OmniSetting::where('company_id', $channel->company_id)->first();

        if (!$settings || !$settings->auto_assign) {
            return;
        }

        $agentIds = $settings->auto_assign_agents ?? [];
        if (empty($agentIds)) {
            return;
        }

        $lastAssignment = ConversationAssignment::whereIn('user_id', $agentIds)
            ->latest('assigned_at')
            ->first();

        $nextAgentId = $agentIds[0];

        if ($lastAssignment) {
            $lastIndex = array_search($lastAssignment->user_id, $agentIds);
            $nextIndex = ($lastIndex === false) ? 0 : ($lastIndex + 1) % count($agentIds);
            $nextAgentId = $agentIds[$nextIndex];
        }

        $state->assignedUsers()->syncWithoutDetaching([$nextAgentId]);

        Log::info("Meta Webhook: Auto-assigned customer {$state->customer_psid} to user {$nextAgentId}");
    }

    private function sendWelcomeOrAwayMessage(Channel $channel, string $customerPsid, string $customerName)
    {
        $settings = OmniSetting::where('company_id', $channel->company_id)->first();
        if (!$settings) return;

        $companyName = $channel->company->name ?? 'our team';

        $now = now();
        $isBusinessHours = $now->hour >= 10 && $now->hour < 22;

        $messageText = null;

        if (!$isBusinessHours && $settings->away_mode_active) {
            $messageText = MessagePlaceholderHelper::replace(
                $settings->away_message,
                $customerName,
                $companyName
            );
        } elseif ($settings->welcome_mode_active) {
            $messageText = MessagePlaceholderHelper::replace(
                $settings->welcome_message,
                $customerName,
                $companyName
            );
        }

        if (!$messageText) return;

        $response = Http::post("https://graph.facebook.com/v20.0/me/messages", [
            'access_token' => $channel->page_token,
            'recipient' => ['id' => $customerPsid],
            'message' => ['text' => $messageText],
            'messaging_type' => 'RESPONSE',
        ]);

        if ($response->failed()) {
            Log::error("Meta Webhook: Failed to send welcome/away message. Body: " . $response->body());
        } else {
            Log::info("Meta Webhook: Welcome/away message sent to {$customerPsid}");
        }
    }

    private function handleAiAutoResponse(Channel $channel, string $customerPsid, string $customerText, ?array $referral = null, ?int $eventTimestamp = null)
    {
        try {
            // 1. Safeguard: Ignore messages older than 2 minutes (prevent answering stale messages)
            if ($eventTimestamp) {
                $eventSeconds = (int) ($eventTimestamp > 10000000000 ? $eventTimestamp / 1000 : $eventTimestamp);
                if ((time() - $eventSeconds) > 120) {
                    Log::info("Meta Webhook AI: Skipped old message from {$customerPsid} (age > 2 min)");
                    return;
                }
            }

            $settings = OmniSetting::where('company_id', $channel->company_id)->first();
            if (!$settings || !$settings->ai_auto_response) {
                return;
            }

            // 2. Safeguard: Human Agent Cooldown
            // Check if human agent replied within last 15 minutes in this thread
            $state = MetaConversationState::where('channel_id', $channel->id)
                ->where('customer_psid', $customerPsid)
                ->first();

            if ($state && $state->last_read_at && now()->diffInMinutes($state->last_read_at) < 15) {
                Log::info("Meta Webhook AI: Skipped AI reply because a human agent was active within the last 15 minutes.");
                return;
            }

            // Check if AI service is configured
            $aiSetting = \App\Models\AiSetting::where('company_id', $channel->company_id)->first();
            if (!$aiSetting || (!$aiSetting->gemini_status && !$aiSetting->openai_status)) {
                Log::warning("Meta Webhook AI: AI auto-response enabled but AI provider is not active.");
                return;
            }

            // 3. Ad Context Lookup (From Ad Mapping table and Webhook Referral)
            $adContextText = null;
            $adId = $referral['ad_id'] ?? null;
            $adTitle = $referral['ads_context_data']['ad_title'] ?? null;

            if ($adId) {
                $mapping = \App\Models\OmniAdMapping::where('company_id', $channel->company_id)
                    ->where('ad_id', $adId)
                    ->where('is_active', true)
                    ->with('product')
                    ->first();

                if ($mapping) {
                    $adContextText = "Customer came from Facebook Ad (ID: {$adId}, Campaign: {$mapping->title}).\n";
                    if ($mapping->ad_info) {
                        $adContextText .= "Specific Ad Details/Offer/Pricing: {$mapping->ad_info}\n";
                    }
                    if ($mapping->product) {
                        $p = $mapping->product;
                        $pTitle = $p->title ?? $p->name ?? 'Product';
                        $pPrice = "৳" . ($p->regular_price ?? $p->selling_price ?? 0);
                        $stock = $p->stock_quantity ?? $p->current_stock ?? 'In Stock';
                        $desc = $p->short_description ?? $p->description ?? '';
                        $adContextText .= "Linked Product in Ad: {$pTitle} | Price: {$pPrice} | Stock: {$stock}\nDescription: {$desc}\n";
                    }
                } elseif ($adTitle) {
                    $adContextText = "Customer came from Facebook Ad titled: '{$adTitle}' (Ad ID: {$adId}).";
                }
            } elseif ($adTitle) {
                $adContextText = "Customer came from Facebook Ad titled: '{$adTitle}'.";
            }

            // Fetch company / shop details
            $siteSetting = \App\Models\SiteSetting::where('company_id', $channel->company_id)->first();
            $shopName = $siteSetting->shop_name ?? ($channel->company->name ?? 'Our Shop');
            $shopPhone = $siteSetting->phone ?? ($siteSetting->hotline ?? null);
            $shopAddress = $siteSetting->address ?? null;

            // Fetch recent / active products for catalog context
            $products = \App\Models\Product::where('company_id', $channel->company_id)
                ->where('status', \App\Enums\Status::Active->value)
                ->latest()
                ->limit(30)
                ->get(['id', 'title', 'regular_price', 'stock_quantity', 'short_description']);

            $productSummary = $products->map(function ($p) {
                $pTitle = $p->title ?? $p->name ?? 'Product';
                $price = "৳" . ($p->regular_price ?? $p->selling_price ?? 0);
                $stock = $p->stock_quantity ?? $p->current_stock ?? 'Available';
                return "- {$pTitle} | Price: {$price} | In Stock: {$stock}";
            })->implode("\n");

            $detectLanguage = preg_match('/[\x{0980}-\x{09FF}]/u', $customerText) ? 'bn' : 'en';

            $promptParams = [
                'type' => 'omni_chat',
                'language' => $detectLanguage,
                'customer_message' => $customerText,
                'shop_name' => $shopName,
                'contact_info' => $shopPhone ? "Phone: {$shopPhone}, Address: {$shopAddress}" : null,
                'available_products' => $productSummary,
            ];

            if ($adContextText) {
                $promptParams['ad_context_and_targeted_product'] = $adContextText;
            }

            if (!empty($settings->ai_custom_instructions)) {
                $promptParams['special_company_instructions'] = $settings->ai_custom_instructions;
            }

            $aiReply = \App\Services\AI\AiService::generate($channel->company_id, $promptParams);

            if (!empty($aiReply)) {
                $response = Http::post("https://graph.facebook.com/v20.0/me/messages", [
                    'access_token' => $channel->page_token,
                    'recipient' => ['id' => $customerPsid],
                    'message' => ['text' => trim($aiReply)],
                    'messaging_type' => 'RESPONSE',
                ]);

                if ($response->failed()) {
                    Log::error("Meta Webhook AI: Failed to send AI reply to {$customerPsid}. Body: " . $response->body());
                } else {
                    Log::info("Meta Webhook AI: Gemini AI auto-response sent to {$customerPsid}" . ($adId ? " for Ad ID: {$adId}" : ""));
                }
            }
        } catch (\Exception $e) {
            Log::error("Meta Webhook AI Auto-Response Error: " . $e->getMessage());
        }
    }
}