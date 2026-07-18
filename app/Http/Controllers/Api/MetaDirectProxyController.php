<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\Channel; // আমাদের নতুন চাইল্ড মডেল ইম্পোর্ট করা হলো
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MetaDirectProxyController extends Controller
{

    public function getConversations($channelId)
    {
        Log::info("Meta Proxy: Initializing conversations fetch for Channel ID: {$channelId}");

        try {
            $channel = Channel::findOrFail($channelId);
            Log::info("Meta Proxy: Retrieved Channel from DB. Page ID: {$channel->page_id}");

            $pageToken = $channel->page_token;
            $pageId = $channel->page_id;

            $response = Http::get("https://graph.facebook.com/v20.0/{$pageId}/conversations", [
                'access_token' => $pageToken,
                'fields' => 'id,updated_time,unread_count,participants,messages.limit(1){message}'
            ]);

            if ($response->failed()) {
                Log::error("Meta Proxy Error [Conversations]: Graph API call failed for Page ID: {$pageId}. Raw Response: " . $response->body());
                return response()->json(['error' => 'Failed to fetch conversations from Meta.'], 500);
            }

            $data = $response->json('data', []);
            Log::info("Meta Proxy: Successfully retrieved " . count($data) . " conversations for Page ID: {$pageId}");

            $formatted = array_map(function ($convo) use ($channel) {
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
                            'type'         => 2, // Customer
                            'name'         => $customerName,
                            'phone'        => 'N/A', // FB doesn't provide phone; placeholder unless column made nullable
                            'fb_psid'      => $customerPsid,
                            'status'       => Status::Active->value,
                        ]);

                        Log::info("Meta Proxy: New party created for PSID: {$customerPsid}");
                    }
                }
                // -------------------------------------------------------------

                return [
                    'id' => $convo['id'],
                    'customer_name' => $customerName,
                    'customer_id' => $customerPsid,
                    'last_message' => $convo['messages']['data'][0]['message'] ?? 'No text',
                    'unread_count' => $convo['unread_count'] ?? 0,
                    'last_activity' => $convo['updated_time']
                ];
            }, $data);

            return response()->json($formatted);
        } catch (\Exception $e) {
            Log::critical("Meta Proxy Critical [Conversations]: Exception occurred for Channel ID: {$channelId}. Message: " . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }
    /**
     * নির্দিষ্ট চ্যাট থ্রেডের সম্পূর্ণ মেসেজ হিস্ট্রি সরাসরি মেটা থেকে লোড করা (ডিবাগ লগসহ)
     */
    public function getMessages($channelId, $threadId)
    {

        try {
            $channel = Channel::findOrFail($channelId);
            $pageToken = $channel->page_token;

            // মেটা মেসেজেস গ্রাফ এপিআই কল
            $response = Http::get("https://graph.facebook.com/v20.0/{$threadId}/messages", [
                'access_token' => $pageToken,
                'fields' => 'id,message,created_time,from,to,attachments{media_type,picture}'
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

                if (isset($msg['attachments']['data'][0]['id'])) {
                    $attachmentId = $msg['attachments']['data'][0]['id'];

                    $attResponse = Http::get("https://graph.facebook.com/v20.0/{$attachmentId}", [
                        'access_token' => $pageToken,
                        'fields' => 'media_type,image_data,file_url,name,mime_type'
                    ]);

                    // ---- ADD THIS LOG ----
                    Log::info("Meta Proxy: Attachment resolve response for {$attachmentId}. Status: {$attResponse->status()}, Body: " . $attResponse->body());
                    // -----------------------

                    if ($attResponse->successful()) {
                        $attData = $attResponse->json();
                        $filePath = $attData['image_data']['url'] ?? $attData['file_url'] ?? null;
                        $type = ($attData['media_type'] ?? 'image') === 'image' ? 'image' : 'document';
                    } else {
                        Log::warning("Meta Proxy: Failed to resolve attachment {$attachmentId}. Body: " . $attResponse->body());
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
            Log::info($request);
            $data = $request->validate([
                'text' => 'nullable|string',
                'recipient_psid' => 'required|string',
                'file' => 'nullable|file|max:25600', // 25MB
            ]);

            if (empty($data['text']) && !$request->hasFile('file')) {
                return response()->json(['error' => 'Either text or file is required.'], 422);
            }

            $messagePayload = [];

            if ($request->hasFile('file')) {
                $uploadedFile = $request->file('file');
                $mime = $uploadedFile->getMimeType();

                $attachmentType = str_starts_with($mime, 'image/') ? 'image'
                    : (str_starts_with($mime, 'video/') ? 'video'
                        : (str_starts_with($mime, 'audio/') ? 'audio' : 'file'));

                // ---- Use existing helper (generic upload, not uploadImage, since we may get video/audio/docs too) ----
                $path = FileUploadHelper::upload($uploadedFile, 'meta-attachments', 'r2');
                $fileUrl = Storage::disk('r2')->url($path);
                // -------------------------------------------------------------------------------------------------

                Log::info("Meta Proxy: File uploaded via helper. Path: {$path}, URL: {$fileUrl}, Type: {$attachmentType}");

                $messagePayload['attachment'] = [
                    'type' => $attachmentType,
                    'payload' => [
                        'url' => $fileUrl,
                        'is_reusable' => true
                    ]
                ];
            } else {
                $messagePayload['text'] = $data['text'];
            }

            $payload = [
                'access_token' => $pageToken,
                'recipient' => ['id' => $data['recipient_psid']],
                'message' => $messagePayload,
                'messaging_type' => 'RESPONSE',
            ];

            Log::info("Meta Proxy: Sending to Graph API. Message payload: " . json_encode($messagePayload));

            $response = Http::post("https://graph.facebook.com/v20.0/me/messages", $payload);

            Log::info("Meta Proxy: Graph API responded. Status: {$response->status()}, Body: " . $response->body());

            if ($response->failed()) {
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

            return response()->json($response->json(), 201);
        } catch (ApiException $e) {
            // FileUploadHelper throws ApiException on validation/upload failure
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
}
