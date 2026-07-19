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
        $verifyToken = config('services.meta.webhook_verify_token');

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
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $appSecret);

        if (!$signature || !hash_equals($expectedSignature, $signature)) {
            Log::warning('Meta Webhook: Invalid signature. Possible spoofed request.');
            return response('Forbidden', 403);
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
}