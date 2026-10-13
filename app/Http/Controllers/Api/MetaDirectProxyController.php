<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\MessagePlaceholderHelper;
use App\Http\Controllers\Controller;
use App\Models\Channel; // আমাদের নতুন চাইল্ড মডেল ইম্পোর্ট করা হলো
use App\Models\ConversationAssignment;
use App\Models\MetaConversationState;
use App\Models\OmniSetting;
use App\Models\Party;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

            // ---- Sob conversation state (assign/read/archive) ekbar-e fetch kora ----
            $states = MetaConversationState::where('channel_id', $channel->id)
                ->with('assignedUsers:id,name')
                ->get()
                ->keyBy('thread_id');

            // ---- NEW: customer_psid diye-o lookup korার জন্য alada map (webhook-created state-er জন্য, jegular thread_id null) ----
            $statesByPsid = MetaConversationState::where('channel_id', $channel->id)
                ->whereNotNull('customer_psid')
                ->with('assignedUsers:id,name')
                ->get()
                ->keyBy('customer_psid');

            $formatted = array_map(function ($convo) use ($channel, $states, $statesByPsid) {
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

                // ---- thread_id diye state khoja ----
                $state = $states->get($convo['id']);

                // ---- NEW: thread_id diye na paওয়া গেলে, customer_psid diye khoja (webhook-created state) ----
                if (!$state && $customerPsid) {
                    $state = $statesByPsid->get($customerPsid);

                    // Pawa gele, ei state-er thread_id ekhon jana geche — DB-te sync kore rakha
                    if ($state && !$state->thread_id) {
                        $state->update(['thread_id' => $convo['id']]);
                    }
                }

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
                    'is_archived' => $state?->is_archived ?? false,
                ];
            }, $data);

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

            $data = $request->validate(['recipient_psid' => 'required|string']);

            // Meta-ke customer-seen notify kora
            $response = Http::post("https://graph.facebook.com/v20.0/me/messages", [
                'access_token' => $pageToken,
                'recipient' => ['id' => $data['recipient_psid']],
                'sender_action' => 'mark_seen',
            ]);

            MetaConversationState::updateOrCreate(
                ['thread_id' => $threadId],
                ['channel_id' => $channelId, 'is_read' => true, 'last_read_at' => now()]
            );
            // ------------------------------------------------

            if ($response->failed()) {
                Log::error("Meta Proxy Error [MarkSeen]: " . $response->body());
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::critical("Meta Proxy Critical [MarkSeen]: " . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred.'], 500);
        }
    }

    public function archive($threadId)
    {
        $state =  MetaConversationState::where('thread_id', $threadId)->first();
        $state->is_archived = $state->is_archived == 0 ? true : false;
        $state->update();
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
                'fields' => 'id,message,created_time,from,to,shares,attachments{mime_type,name,file_url,image_data,video_data,title,description}'
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
                $attachments = [];
                $adContext = null;

                // Check shares for ad info
                if (!empty($msg['shares']['data'])) {
                    $firstShare = $msg['shares']['data'][0];
                    $adContext = [
                        'title' => $firstShare['name'] ?? 'Facebook Ad',
                        'description' => $firstShare['description'] ?? null,
                        'link' => $firstShare['link'] ?? null,
                        'image' => null,
                    ];
                }

                if (!empty($msg['attachments']['data'])) {
                    foreach ($msg['attachments']['data'] as $att) {
                        $attType = 'document';
                        $attUrl = null;

                        if (isset($att['image_data']['url'])) {
                            $attType = 'image';
                            $attUrl = $att['image_data']['url'];
                        } elseif (isset($att['video_data']['url'])) {
                            $attType = 'video';
                            $attUrl = $att['video_data']['url'];
                        } elseif (isset($att['file_url'])) {
                            $attType = 'document';
                            $attUrl = $att['file_url'];
                        }

                        if ($attUrl) {
                            $attachments[] = [
                                'type' => $attType,
                                'url' => $attUrl,
                                'name' => $att['name'] ?? null,
                            ];
                        }

                        // Check if this attachment represents an ad / story
                        if (!empty($att['title']) || !empty($att['description'])) {
                            $adContext = [
                                'title' => $att['title'] ?? ($adContext['title'] ?? 'Sponsored Ad'),
                                'description' => $att['description'] ?? ($adContext['description'] ?? null),
                                'link' => $att['file_url'] ?? ($adContext['link'] ?? null),
                                'image' => $att['image_data']['url'] ?? ($adContext['image'] ?? null),
                            ];
                        }
                    }

                    if (!empty($attachments)) {
                        $type = $attachments[0]['type'];
                        $filePath = $attachments[0]['url'];
                    }
                }

                return [
                    'id' => $msg['id'],
                    'from' => $isAgent ? 'agent' : 'customer',
                    'type' => $type,
                    'text' => $msg['message'] ?? '',
                    'file_path' => $filePath,
                    'attachments' => $attachments,
                    'ad_context' => $adContext,
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
                'file_url' => 'nullable|string',
                'file_urls' => 'nullable|array',
                'file_urls.*' => 'string',
                'attachment_type' => 'nullable|string|in:image,video,audio,file,text',
            ]);

            if (empty($data['text']) && !$request->hasFile('file') && empty($data['file_url']) && empty($data['file_urls'])) {
                return response()->json(['error' => 'Either text, file, file_url or file_urls is required.'], 422);
            }

            $results = []; // multiple Meta responses store korbo (image + text alada)
            $fileUrls = [];
            $attachmentType = (!empty($data['attachment_type']) && $data['attachment_type'] !== 'text') ? $data['attachment_type'] : 'image';

            // ---- Step 1: Collect all files / URLs ----
            if ($request->hasFile('file')) {
                $uploadedFile = $request->file('file');
                $mime = $uploadedFile->getMimeType();

                $attachmentType = str_starts_with($mime, 'image/') ? 'image'
                    : (str_starts_with($mime, 'video/') ? 'video'
                        : (str_starts_with($mime, 'audio/') ? 'audio' : 'file'));

                $path = FileUploadHelper::upload($uploadedFile, 'meta-attachments', 'r2');
                $fileUrls[] = Storage::disk('r2')->url($path);
            }

            if (!empty($data['file_urls'])) {
                foreach ($data['file_urls'] as $fUrl) {
                    if (!str_starts_with($fUrl, 'http://') && !str_starts_with($fUrl, 'https://')) {
                        $fUrl = Storage::disk('r2')->url($fUrl);
                    }
                    $fileUrls[] = $fUrl;
                }
            } elseif (!empty($data['file_url'])) {
                $singleUrl = $data['file_url'];
                if (!str_starts_with($singleUrl, 'http://') && !str_starts_with($singleUrl, 'https://')) {
                    $singleUrl = Storage::disk('r2')->url($singleUrl);
                }
                $fileUrls[] = $singleUrl;
            }

            // Send all attachments to Meta
            if ($attachmentType === 'image' && count($fileUrls) > 1) {
                // Meta Messenger Platform: send multiple images using official image_grid template (2-6 images per grid)
                $chunks = array_chunk($fileUrls, 6);

                foreach ($chunks as $chunk) {
                    if (count($chunk) >= 2) {
                        $gridImages = array_map(function ($url) {
                            return ['url' => $url];
                        }, $chunk);

                        $imagePayload = [
                            'access_token' => $pageToken,
                            'recipient' => ['id' => $data['recipient_psid']],
                            'message' => [
                                'attachment' => [
                                    'type' => 'template',
                                    'payload' => [
                                        'template_type' => 'image_grid',
                                        'elements' => [
                                            [
                                                'images' => $gridImages,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'messaging_type' => 'RESPONSE',
                        ];

                        $imageResponse = Http::post("https://graph.facebook.com/v20.0/me/messages", $imagePayload);
                        Log::info("Meta Proxy: Image Grid send response. Status: {$imageResponse->status()}, Body: " . $imageResponse->body());

                        if ($imageResponse->failed()) {
                            Log::warning("Meta Proxy: Image Grid send failed, falling back to sequential single-attachment sends.");
                            foreach ($chunk as $url) {
                                $singlePayload = [
                                    'access_token' => $pageToken,
                                    'recipient' => ['id' => $data['recipient_psid']],
                                    'message' => [
                                        'attachment' => [
                                            'type' => 'image',
                                            'payload' => [
                                                'url' => $url,
                                                'is_reusable' => true,
                                            ],
                                        ],
                                    ],
                                    'messaging_type' => 'RESPONSE',
                                ];

                                $singleResponse = Http::post("https://graph.facebook.com/v20.0/me/messages", $singlePayload);
                                if ($singleResponse->failed()) {
                                    return $this->handleMetaError($singleResponse);
                                }
                                $results['attachments'][] = $singleResponse->json();
                            }
                        } else {
                            $results['attachments'][] = $imageResponse->json();
                        }
                    } else {
                        $singlePayload = [
                            'access_token' => $pageToken,
                            'recipient' => ['id' => $data['recipient_psid']],
                            'message' => [
                                'attachment' => [
                                    'type' => 'image',
                                    'payload' => [
                                        'url' => $chunk[0],
                                        'is_reusable' => true,
                                    ],
                                ],
                            ],
                            'messaging_type' => 'RESPONSE',
                        ];

                        $singleResponse = Http::post("https://graph.facebook.com/v20.0/me/messages", $singlePayload);
                        if ($singleResponse->failed()) {
                            return $this->handleMetaError($singleResponse);
                        }
                        $results['attachments'][] = $singleResponse->json();
                    }
                }
            } else {
                foreach ($fileUrls as $url) {
                    $imagePayload = [
                        'access_token' => $pageToken,
                        'recipient' => ['id' => $data['recipient_psid']],
                        'message' => [
                            'attachment' => [
                                'type' => $attachmentType,
                                'payload' => [
                                    'url' => $url,
                                    'is_reusable' => true,
                                ],
                            ],
                        ],
                        'messaging_type' => 'RESPONSE',
                    ];

                    $imageResponse = Http::post("https://graph.facebook.com/v20.0/me/messages", $imagePayload);
                    Log::info("Meta Proxy: Attachment send response. Status: {$imageResponse->status()}, Body: " . $imageResponse->body());

                    if ($imageResponse->failed()) {
                        return $this->handleMetaError($imageResponse);
                    }

                    $results['attachments'][] = $imageResponse->json();
                }
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

    public function bulkSendMessage(Request $request, $channelId)
    {
        Log::info("Meta Proxy: bulkSendMessage called. Channel ID: {$channelId}");

        try {
            $channel = Channel::findOrFail($channelId);
            $pageToken = $channel->page_token;

            if (empty($pageToken)) {
                return response()->json(['error' => 'This channel has no valid Facebook token.'], 401);
            }

            $data = $request->validate([
                'recipients' => 'required|array|min:1',
                'recipients.*.psid' => 'required|string',
                'text' => 'nullable|string',
                'file_url' => 'nullable|string',
                'attachment_type' => 'nullable|string|in:image,video,audio,file',
            ]);

            if (empty($data['text']) && empty($data['file_url'])) {
                return response()->json(['error' => 'Either text or file_url is required.'], 422);
            }

            $fileUrl = $data['file_url'] ?? null;
            if ($fileUrl && !str_starts_with($fileUrl, 'http://') && !str_starts_with($fileUrl, 'https://')) {
                $fileUrl = Storage::disk('r2')->url($fileUrl);
            }
            $attachmentType = $data['attachment_type'] ?? 'image';

            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($data['recipients'] as $recipient) {
                $psid = $recipient['psid'];
                try {
                    if ($fileUrl) {
                        Http::post("https://graph.facebook.com/v20.0/me/messages", [
                            'access_token' => $pageToken,
                            'recipient' => ['id' => $psid],
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
                        ]);
                    }

                    if (!empty($data['text'])) {
                        Http::post("https://graph.facebook.com/v20.0/me/messages", [
                            'access_token' => $pageToken,
                            'recipient' => ['id' => $psid],
                            'message' => ['text' => $data['text']],
                            'messaging_type' => 'RESPONSE',
                        ]);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Failed for PSID {$psid}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'total' => count($data['recipients']),
                'sent_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            Log::critical("Meta Proxy Critical [BulkSendMessage]: " . $e->getMessage());
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

    public function fetchLinkPreview(Request $request)
    {
        $data = $request->validate(['url' => 'required|url']);
        $url = $data['url'];

        $cacheKey = 'link_preview:' . md5($url);

        $preview = Cache::remember($cacheKey, now()->addDays(7), function () use ($url) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; LinkPreviewBot/1.0)'])
                    ->get($url);

                if ($response->failed()) {
                    return null;
                }

                $html = $response->body();

                // Simple OG tag extraction (regex-based, dependency chara)
                $title = $this->extractMeta($html, 'og:title') ?? $this->extractTitleTag($html);
                $description = $this->extractMeta($html, 'og:description');
                $image = $this->extractMeta($html, 'og:image');
                $siteName = $this->extractMeta($html, 'og:site_name');

                return [
                    'url' => $url,
                    'title' => $title,
                    'description' => $description,
                    'image' => $image,
                    'site_name' => $siteName,
                ];
            } catch (\Exception $e) {
                \Log::warning("Link preview fetch failed for {$url}: " . $e->getMessage());
                return null;
            }
        });

        if (!$preview) {
            return response()->json(['error' => 'Could not fetch preview.'], 404);
        }

        return response()->json($preview);
    }

    private function extractMeta(string $html, string $property): ?string
    {
        if (preg_match('/<meta[^>]+property=["\']' . preg_quote($property, '/') . '["\'][^>]+content=["\']([^"\']*)["\']/i', $html, $matches)) {
            return html_entity_decode($matches[1]);
        }
        if (preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+property=["\']' . preg_quote($property, '/') . '["\']/i', $html, $matches)) {
            return html_entity_decode($matches[1]);
        }
        return null;
    }

    private function extractTitleTag(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            return html_entity_decode(trim($matches[1]));
        }
        return null;
    }


    private function sendWelcomeOrAwayMessage($channel, $customerPsid, $customerName)
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

        if ($messageText) {
            Http::post("https://graph.facebook.com/v20.0/me/messages", [
                'access_token' => $channel->page_token,
                'recipient' => ['id' => $customerPsid],
                'message' => ['text' => $messageText],
                'messaging_type' => 'RESPONSE',
            ]);
        }
    }
    private function autoAssignConversation($channel, $threadId)
    {
        $settings = OmniSetting::where('company_id', $channel->company_id)->first();

        if (!$settings || !$settings->auto_assign) return;

        $agentIds = $settings->auto_assign_agents ?? [];
        if (empty($agentIds)) return;

        // Round-robin: last assigned agent-er porerta বেছে নেওয়া
        $lastAssignment = ConversationAssignment::whereIn('user_id', $agentIds)
            ->latest('assigned_at')
            ->first();

        $nextAgentId = $agentIds[0]; // default first

        if ($lastAssignment) {
            $lastIndex = array_search($lastAssignment->user_id, $agentIds);
            $nextIndex = ($lastIndex === false) ? 0 : ($lastIndex + 1) % count($agentIds);
            $nextAgentId = $agentIds[$nextIndex];
        }

        $state = MetaConversationState::firstOrCreate(
            ['thread_id' => $threadId],
            ['channel_id' => $channel->id]
        );

        $state->assignedUsers()->syncWithoutDetaching([$nextAgentId]);
    }
}
