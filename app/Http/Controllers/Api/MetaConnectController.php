<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Channel;
use App\Models\ChannelGroup;
use App\Services\MetaIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MetaConnectController extends Controller
{
    public function __construct(protected MetaIntegrationService $meta) {}
    public function startLogin(Request $request, string $type)
    {
        $state = Str::uuid()->toString();

        Cache::put("meta_oauth_state:{$state}", [
            'type' => $type, // 'facebook', 'instagram', 'whatsapp'
        ], now()->addMinutes(15));

        $scopes = match ($type) {
            'whatsapp' => ['business_management', 'whatsapp_business_management', 'whatsapp_business_messaging'],
            default => [
                'pages_show_list',
                'pages_messaging',
                'pages_read_engagement',
                'pages_manage_metadata',
                'instagram_basic',
                'instagram_manage_messages',
                'business_management'
            ],
        };

        // মেটা ইন্টিগ্রেশন সার্ভিস থেকে রিডাইরেক্ট ইউআরএল জেনারেট করে রিটার্ন করা হচ্ছে
        return response()->json([
            'url' => $this->meta->loginUrl($state, $scopes),
        ]);
    }

    public function callback(Request $request)
    {
        $state = $request->query('state');
        $code = $request->query('code');
        $ctx = Cache::pull("meta_oauth_state:{$state}");

        abort_if(!$ctx || !$code, 400, 'Invalid or expired OAuth session');

        // ১. ফেসবুক থেকে পার্সোনাল ইউজার অ্যাক্সেস টোকেন এক্সচেঞ্জ
        $tokenData = $this->meta->exchangeCodeForToken($code);
        $userToken = $tokenData['access_token'];

        // ২. ইউজারের পার্সোনাল ফেসবুক প্রোফাইল ডেটা (নাম ও ছবি) ফেচ করা
        $userProfile = Http::get("https://graph.facebook.com/v20.0/me", [
            'access_token' => $userToken,
            'fields' => 'id,name,picture.type(large)'
        ])->json();

        $profileName = $userProfile['name'] ?? 'FB User';
        $profileImage = $userProfile['picture']['data']['url'] ?? null;

        // ৩. চ্যানেল গ্রুপ (প্যারেন্ট) তৈরি
        $channelGroup = ChannelGroup::create([
            'platform' => 'facebook',
            'profile_name' => $profileName,
            'profile_image' => $profileImage,
            'personal_token' => $userToken,
        ]);

        // ৪. ইউজারের আন্ডারে থাকা ফেসবুক পেজগুলোর তালিকা (নাম, পেজ টোকেন ও ছবি) নিয়ে আসা
        $pagesData = Http::get("https://graph.facebook.com/v20.0/me/accounts", [
            'access_token' => $userToken,
            'fields' => 'id,name,access_token,picture.type(large)'
        ])->json('data', []);

        // পেজের তালিকা সাময়িকভাবে ক্যাশে সেভ রাখা যাতে ফ্রন্টএন্ড চেকবক্স লিস্টে রেন্ডার করতে পারে
        $sessionId = Str::uuid()->toString();
        Cache::put("meta_session:{$sessionId}", [
            'group_id' => $channelGroup->id,
            'pages' => $pagesData
        ], now()->addMinutes(20));

        // ৫. ওথ থেকে রিঅ্যাক্টের ক্রিয়েশন পেজে রিডাইরেক্ট
        $frontendUrl = env('APP_FRONTEND_URL', 'http://localhost:5173') . "/settings/connections?meta_session={$sessionId}&type=facebook";
        return redirect($frontendUrl);
    }

    /**
     * ধাপ ৩: ফ্রন্টএন্ড চেকবক্স থেকে সিলেক্ট করা পেজগুলোকে ওম্নি চ্যানেল হিসেবে ডাটাবেজে সেভ করা
     */
    public function connectChannels(Request $request, string $sessionId)
    {
        $session = Cache::get("meta_session:{$sessionId}");
        abort_unless($session, 400, 'Session expired');

        $data = $request->validate([
            'selected_page_ids' => 'required|array',
            'selected_page_ids.*' => 'required|string',
        ]);

        $groupId = $session['group_id'];
        $cachedPages = $session['pages'];

        // শুধুমাত্র ফ্রন্টএন্ড থেকে সিলেক্ট করা পেজগুলো ফিল্টার করা
        $pagesToConnect = collect($cachedPages)->filter(function ($p) use ($data) {
            return in_array($p['id'], $data['selected_page_ids']);
        });

        foreach ($pagesToConnect as $pageData) {
            $pageId = $pageData['id'];
            $pageName = $pageData['name'];
            $pageToken = $pageData['access_token'];
            $pageImage = $pageData['picture']['data']['url'] ?? null;

            // মেটা ওয়েব হুকে সাবস্ক্রাইব করা (অপশনাল বা এপিআই কানেক্টর অনুযায়ী)
            // $this->meta->subscribePageToWebhooks($pageId, $pageToken);

            // চাইল্ড 'Channel' মডেলে পেজটি তৈরি করা হলো
            Channel::updateOrCreate(
                ['page_id' => $pageId],
                [
                    'channel_group_id' => $groupId,
                    'name' => $pageName,
                    'type' => 'facebook',
                    'slug' => 'fb-' . $pageId,
                    'profile_image' => $pageImage,
                    'page_token' => $pageToken,
                    'color' => '#1877F2'
                ]
            );
        }

        Cache::forget("meta_session:{$sessionId}");

        return response()->json(['message' => 'Selected pages successfully connected.']);
    }
}
