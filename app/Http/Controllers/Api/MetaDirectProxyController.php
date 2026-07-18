<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Channel; // আমাদের নতুন চাইল্ড মডেল ইম্পোর্ট করা হলো
use App\Models\MetaConversationState;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MetaDirectProxyController extends Controller
{

    public function getConversations($channelId, Request $request)
    {
        Log::info("Meta Proxy: Initializing conversations fetch for Channel ID: {$channelId}");

        try {
            $channel = Channel::findOrFail($channelId);

            $pageToken = $channel->page_token;
            $pageId = $channel->page_id;

            $limit = $request->get('limit', 15);
            $after = $request->get('after');

            $params = [
                'access_token' => $pageToken,
                'fields' => 'id,updated_time,unread_count,participants,messages.limit(1){message}',
                'limit' => $limit,
            ];

            if ($after) {
                $params['after'] = $after;
            }

            $response = Http::get("https://graph.facebook.com/v20.0/{$pageId}/conversations", $params);

            if ($response->failed()) {
                Log::error("Meta Proxy Error [Conversations]: Graph API call failed for Page ID: {$pageId}. Raw Response: " . $response->body());
                return response()->json(['error' => 'Failed to fetch conversations from Meta.'], 500);
            }

            $data = $response->json('data', []);
            $paging = $response->json('paging', []);

            Log::info("Meta Proxy: Successfully retrieved " . count($data) . " conversations for Page ID: {$pageId}");

            $states = \App\Models\MetaConversationState::where('channel_id', $channel->id)
                ->with('assignedUsers:id,name')
                ->get()
                ->keyBy('thread_id');

            $formatted = array_map(function ($convo) use ($channel, $states) {
                $customer = $convo['participants']['data'][0] ?? null;

                $customerName = $customer['name'] ?? 'Facebook User';
                $customerPsid = $customer['id'] ?? null;

                if ($customerPsid) {
                    $exists = Party::where('company_id', $channel->company_id)
                        ->where('fb_psid', $customerPsid)
                        ->exists();

                    if (!$exists) {
                        Party::create([
                            'company_id'   => $channel->company_id,
                            'type'         => 2,
                            'name'         => $customerName,
                            'phone'        => 'N/A',
                            'fb_psid'      => $customerPsid,
                            'status'       => Status::Active->value,
                        ]);
                    }
                }

                $state = $states->get($convo['id']);

                return [
                    'id' => $convo['id'],
                    'customer_name' => $customerName,
                    'customer_id' => $customerPsid,
                    'last_message' => $convo['messages']['data'][0]['message'] ?? 'No text',
                    'unread_count' => ($state->is_read ?? false) ? 0 : ($convo['unread_count'] ?? 0),
                    'last_activity' => $convo['updated_time'],
                    'assigned_users' => $state?->assignedUsers->map(fn($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                    ])->values() ?? [],
                ];
            }, $data);

            // ---- NEW: pagination cursor frontend-ke ferot dewa ----
            return response()->json([
                'data' => $formatted,
                'next_cursor' => $paging['cursors']['after'] ?? null,
                'has_more' => isset($paging['next']),
            ]);
        } catch (\Exception $e) {
            Log::critical("Meta Proxy Critical [Conversations]: Exception occurred for Channel ID: {$channelId}. Message: " . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
    public function markSeen(Request $request, $channelId, $threadId)
    {
        try {
            $channel = Channel::findOrFail($channelId);
            $pageToken = $channel->page_token;

            $data = $request->validate([
                'recipient_psid' => 'required|string',
            ]);

            $response = Http::post("https://graph.facebook.com/v20.0/me/messages", [
                'access_token' => $pageToken,
                'recipient' => ['id' => $data['recipient_psid']],
                'sender_action' => 'mark_seen',
            ]);

            Log::info("Meta Proxy: mark_seen response. Status: {$response->status()}, Body: " . $response->body());

            if ($response->failed()) {
                Log::error("Meta Proxy Error [MarkSeen]: " . $response->body());
                return response()->json(['error' => 'Failed to mark as seen.'], 500);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::critical("Meta Proxy Critical [MarkSeen]: " . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    public function assignUser(Request $request, $channelId, $threadId)
    {
        $data = $request->validate(['user_id' => 'required|exists:users,id']);

        $state = MetaConversationState::firstOrCreate(
            ['thread_id' => $threadId],
            ['channel_id' => $channelId]
        );

        $state->assignedUsers()->syncWithoutDetaching([$data['user_id']]);

        return response()->json(['success' => true, 'assigned_users' => $state->assignedUsers]);
    }

    public function unassignUser(Request $request, $channelId, $threadId)
    {
        $data = $request->validate(['user_id' => 'required|exists:users,id']);

        $state = MetaConversationState::where('thread_id', $threadId)->first();
        $state?->assignedUsers()->detach($data['user_id']);

        return response()->json(['success' => true]);
    }
    /**
     * নির্দিষ্ট চ্যাট থ্রেডের সম্পূর্ণ মেসেজ হিস্ট্রি সরাসরি মেটা থেকে লোড করা (ডিবাগ লগসহ)
     */
    public function getMessages($channelId, $threadId)
    {

        try {
            $channel = Channel::findOrFail($channelId);
            $pageToken = $channel->page_token;

            $response = Http::get("https://graph.facebook.com/v20.0/{$threadId}/messages", [
                'access_token' => $pageToken,
                'fields' => 'id,message,created_time,from,to,attachments{mime_type,name,file_url,image_data,video_data}'
            ]);

            if ($response->failed()) {
                Log::error("Meta Proxy Error [Messages]: Failed to load messages for Thread ID: {$threadId}. Raw Response: " . $response->body());
                return response()->json(['error' => 'Failed to load live messages.'], 500);
            }

            $data = $response->json('data', []);
            Log::info("Meta Proxy: Successfully processed " . count($data) . " messages from Meta for Thread ID: {$threadId}");

            $messages = array_map(function ($msg) use ($channel, $pageToken) {
                $senderId = $msg['from']['id'] ?? null;
                $isAgent = $senderId === $channel->page_id;

                $filePath = null;
                $type = 'text';

                if (isset($msg['attachments']['data'][0])) {

                    $attachment = $msg['attachments']['data'][0];

                    if (isset($attachment['image_data']['url'])) {
                        $type = 'image';
                        $filePath = $attachment['image_data']['url'];
                    } elseif (isset($attachment['video_data']['url'])) {
                        $type = 'video';
                        $filePath = $attachment['video_data']['url'];
                    } elseif (isset($attachment['file_url'])) {
                        $type = 'document';
                        $filePath = $attachment['file_url'];
                    }
                }

                return [
                    'id' => $msg['id'],
                    'from' => $isAgent ? 'agent' : 'customer',
                    'type' => $type,
                    'text' => $msg['message'] ?? '',
                    'file_path' => $filePath,
                    'created_at' => $msg['created_time']
                ];
            }, $data);

            return response()->json(array_reverse($messages));
        } catch (\Exception $e) {
            Log::critical("Meta Proxy Critical [Messages]: Exception occurred for Thread ID: {$threadId}. Message: " . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }


    public function sendMessage(Request $request, $channelId, $threadId)
    {
        Log::info("Meta Proxy: sendMessage called. Channel ID: {$channelId}, Thread ID: {$threadId}");

        try {
            $channel = Channel::findOrFail($channelId);
            $pageToken = $channel->page_token;

            if (empty($pageToken)) {
                Log::error("Meta Proxy Error [SendMessage]: No page_token found for Channel ID: {$channelId}");
                return response()->json(['error' => 'This channel has no valid Facebook token. Please reconnect.'], 401);
            }

            $data = $request->validate([
                'text' => 'nullable|string',
                'recipient_psid' => 'required|string',
                'file' => 'nullable|file|max:25600', // 25MB
            ]);

            if (empty($data['text']) && !$request->hasFile('file')) {
                return response()->json(['error' => 'Either text or file is required.'], 422);
            }

            $results = []; // multiple Meta responses store korbo (image + text alada)
            $fileUrl = null;
            $attachmentType = null;

            // ---- Step 1: File thakle age seta pathai ----
            if ($request->hasFile('file')) {
                $uploadedFile = $request->file('file');
                $mime = $uploadedFile->getMimeType();

                $attachmentType = str_starts_with($mime, 'image/') ? 'image'
                    : (str_starts_with($mime, 'video/') ? 'video'
                        : (str_starts_with($mime, 'audio/') ? 'audio' : 'file'));

                $path = FileUploadHelper::upload($uploadedFile, 'meta-attachments', 'r2');
                $fileUrl = Storage::disk('r2')->url($path);

                Log::info("Meta Proxy: File uploaded via helper. Path: {$path}, URL: {$fileUrl}, Type: {$attachmentType}");

                $imagePayload = [
                    'access_token' => $pageToken,
                    'recipient' => ['id' => $data['recipient_psid']],
                    'message' => [
                        'attachment' => [
                            'type' => $attachmentType,
                            'payload' => [
                                'url' => $fileUrl,
                                'is_reusable' => true
                            ]
                        ]
                    ],
                    'messaging_type' => 'RESPONSE',
                ];

                $imageResponse = Http::post("https://graph.facebook.com/v20.0/me/messages", $imagePayload);

                Log::info("Meta Proxy: Image send response. Status: {$imageResponse->status()}, Body: " . $imageResponse->body());

                if ($imageResponse->failed()) {
                    return $this->handleMetaError($imageResponse);
                }

                $results['image'] = $imageResponse->json();
            }

            // ---- Step 2: Text thakle seta alada message hisebe pathai ----
            if (!empty($data['text'])) {
                $textPayload = [
                    'access_token' => $pageToken,
                    'recipient' => ['id' => $data['recipient_psid']],
                    'message' => ['text' => $data['text']],
                    'messaging_type' => 'RESPONSE',
                ];

                $textResponse = Http::post("https://graph.facebook.com/v20.0/me/messages", $textPayload);

                Log::info("Meta Proxy: Text send response. Status: {$textResponse->status()}, Body: " . $textResponse->body());

                if ($textResponse->failed()) {
                    return $this->handleMetaError($textResponse);
                }

                $results['text'] = $textResponse->json();
            }



            return response()->json($results, 201);
        } catch (ApiException $e) {
            Log::warning("Meta Proxy Upload Error [SendMessage]: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode() ?? 400);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Meta Proxy Validation [SendMessage]: " . json_encode($e->errors()));
            return response()->json(['error' => 'Validation failed.', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::critical("Meta Proxy Critical [SendMessage]: " . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    private function handleMetaError($response)
    {
        $errorBody = $response->json('error', []);
        $code = $errorBody['code'] ?? null;
        $subcode = $errorBody['error_subcode'] ?? null;

        Log::error("Meta Proxy Error [SendMessage]: Code: {$code}, Subcode: {$subcode}, Message: " . ($errorBody['message'] ?? 'Unknown'));

        if ($code === 10 && $subcode === 2018278) {
            return response()->json([
                'error' => 'This customer has not messaged in the last 24 hours.',
                'code' => 'OUTSIDE_WINDOW',
            ], 403);
        }

        if (in_array($subcode, [463, 460, 467])) {
            return response()->json([
                'error' => 'Facebook connection expired. Please reconnect this channel.',
                'code' => 'TOKEN_EXPIRED',
            ], 401);
        }

        return response()->json([
            'error' => 'Message delivery failed via Meta API.',
            'meta_error' => $errorBody['message'] ?? null,
        ], 500);
    }
}
