<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\ChannelConnection;
use App\Services\MetaIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class MetaConnectController extends Controller
{
    public function __construct(protected MetaIntegrationService $meta) {}

    // STEP 1: frontend calls this to get the FB login URL
    public function startLogin(Request $request, string $type)
    {
        $state = Str::uuid()->toString();

        // remember which company + which flow (facebook/instagram/whatsapp) triggered this
        Cache::put("meta_oauth_state:{$state}", [
            'company_id' => $request->user()->company_id,
            'type' => $type, // facebook | instagram | whatsapp
        ], now()->addMinutes(15));

        $scopes = match ($type) {
            'whatsapp' => ['business_management', 'whatsapp_business_management', 'whatsapp_business_messaging'],
            default => [
                'pages_show_list',
                'pages_messaging',
                'pages_manage_metadata',
                'instagram_basic',
                'instagram_manage_messages',
                'business_management'
            ],
        };

        return response()->json([
            'url' => $this->meta->loginUrl($state, $scopes),
        ]);
    }

    // STEP 2: Meta redirects here after user grants permission
    public function callback(Request $request)
    {
        $state = $request->query('state');
        $code = $request->query('code');
        $ctx = Cache::pull("meta_oauth_state:{$state}");

        abort_if(!$ctx || !$code, 400, 'Invalid or expired OAuth session');

        $token = $this->meta->exchangeCodeForToken($code);

        // store the user token temporarily so the frontend can fetch pages/WABAs next
        $sessionId = Str::uuid()->toString();
        Cache::put("meta_session:{$sessionId}", [
            'company_id' => $ctx['company_id'],
            'type' => $ctx['type'],
            'user_token' => $token['access_token'],
        ], now()->addMinutes(20));

        // redirect back to frontend with the session id, frontend then continues the picker steps
        $frontendUrl = config('app.frontend_url') . "/settings/connections?meta_session={$sessionId}&type={$ctx['type']}";
        return redirect($frontendUrl);
    }

    // STEP 3a (facebook/instagram): list Business Managers
    public function businesses(Request $request, string $sessionId)
    {
        $session = Cache::get("meta_session:{$sessionId}");
        abort_unless($session, 400, 'Session expired');

        return response()->json($this->meta->getBusinesses($session['user_token']));
    }

    // STEP 3b: list Pages (optionally scoped to a Business Manager)
    public function pages(Request $request, string $sessionId)
    {
        $session = Cache::get("meta_session:{$sessionId}");
        abort_unless($session, 400, 'Session expired');

        $businessId = $request->query('business_id');

        return response()->json($this->meta->getPages($session['user_token'], $businessId));
    }

    // STEP 3c: finalize Facebook + Instagram connection with chosen page
    public function connectPages(Request $request, string $sessionId)
    {
        $session = Cache::get("meta_session:{$sessionId}");
        abort_unless($session, 400, 'Session expired');

        $data = $request->validate([
            'pages' => 'required|array',
            'pages.*.page_id' => 'required|string',
            'pages.*.page_name' => 'required|string',
            'pages.*.page_access_token' => 'required|string',
            'pages.*.ig_id' => 'nullable|string',
            'pages.*.ig_username' => 'nullable|string',
        ]);

        foreach ($data['pages'] as $pageData) {
            // ১. মেটা সিস্টেমে পেজের ওয়েব হুক সাবস্ক্রাইব করা হচ্ছে
            // $this->meta->subscribePageToWebhooks($pageData['page_id'], $pageData['page_access_token']);

            ChannelConnection::updateOrCreate(
                ['type' => 'facebook', 'page_id' => $pageData['page_id']],
                [
                    'page_name' => $pageData['page_name'],
                    'access_token' => $pageData['page_access_token'],
                    'connected_at' => now(),
                    'is_active' => true,
                ]
            );

            Channel::updateOrCreate(
                ['slug' => 'fb-' . $pageData['page_id']],
                [
                    'name' => $pageData['page_name'] . ' (Facebook)',
                    'type' => 'facebook',
                    'color' => '#1877F2',
                ]
            );

            // ৪. ফেসবুক পেজের সাথে ইনস্টাগ্রাম লিংকড থাকলে সেটিও অটো কানেক্ট হয়ে যাবে
            if (!empty($pageData['ig_id'])) {
                ChannelConnection::updateOrCreate(
                    ['type' => 'instagram', 'ig_id' => $pageData['ig_id']],
                    [
                        'page_id' => $pageData['page_id'],
                        'ig_username' => $pageData['ig_username'],
                        'access_token' => $pageData['page_access_token'],
                        'connected_at' => now(),
                        'is_active' => true,
                    ]
                );

                Channel::updateOrCreate(
                    ['slug' => 'ig-' . $pageData['ig_id']],
                    [
                        'name' => '@' . $pageData['ig_username'] . ' (Instagram)',
                        'type' => 'instagram',
                        'color' => '#C13584',
                    ]
                );
            }
        }

        Cache::forget("meta_session:{$sessionId}");
        return response()->json(['message' => 'Selected Facebook and Instagram accounts successfully connected.']);
    }

    // STEP 3d (whatsapp): list WABAs + phone numbers
    public function whatsappAccounts(Request $request, string $sessionId)
    {
        $session = Cache::get("meta_session:{$sessionId}");
        abort_unless($session, 400, 'Session expired');

        return response()->json($this->meta->getWhatsappBusinessAccounts($session['user_token']));
    }

    // STEP 3e: finalize WhatsApp connection with chosen number
    public function connectWhatsappNumbers(Request $request, string $sessionId)
    {
        $session = Cache::get("meta_session:{$sessionId}");
        abort_unless($session, 400, 'Session expired');

        $data = $request->validate([
            'waba_id' => 'required|string',
            'numbers' => 'required|array',
            'numbers.*.phone_number_id' => 'required|string',
            'numbers.*.phone_number' => 'required|string',
            'numbers.*.verified_name' => 'nullable|string',
        ]);

        foreach ($data['numbers'] as $num) {
            ChannelConnection::updateOrCreate(
                ['type' => 'whatsapp', 'phone_number_id' => $num['phone_number_id']],
                [
                    'waba_id' => $data['waba_id'],
                    'phone_number' => $num['phone_number'],
                    'business_name' => $num['verified_name'] ?? 'WhatsApp Number',
                    'access_token' => $session['user_token'],
                    'connected_at' => now(),
                    'is_active' => true,
                ]
            );


            Channel::updateOrCreate(
                ['slug' => 'wa-' . $num['phone_number_id']],
                [
                    'name' => ($num['verified_name'] ?? $num['phone_number']) . ' (WhatsApp)',
                    'type' => 'whatsapp',
                    'color' => '#22C35E',
                ]
            );
        }

        Cache::forget("meta_session:{$sessionId}");
        return response()->json(['message' => 'Selected WhatsApp phone numbers successfully connected.']);
    }

    public function disconnect(Request $request, ChannelConnection $connection)
    {
        abort_unless($connection->company_id === $request->user()->company_id, 403);
        $connection->update(['is_active' => false, 'access_token' => '']);
        return response()->json(['message' => 'Disconnected']);
    }
    // app/Http/Controllers/Api/V1/MetaConnectController.php

    public function directConnect(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:facebook,instagram,whatsapp',

  
            'page_id' => 'nullable|required_if:type,facebook,instagram|string',
            'page_name' => 'nullable|required_if:type,facebook,instagram|string',
            'page_access_token' => 'nullable|required_if:type,facebook,instagram|string',
            'ig_id' => 'nullable|string',
            'ig_username' => 'nullable|string',

            'waba_id' => 'nullable|required_if:type,whatsapp|string',
            'phone_number_id' => 'nullable|required_if:type,whatsapp|string',
            'phone_number' => 'nullable|required_if:type,whatsapp|string',
            'business_name' => 'nullable|required_if:type,whatsapp|string',
            'whatsapp_access_token' => 'nullable|required_if:type,whatsapp|string',
        ]);

        if ($data['type'] === 'facebook' || $data['type'] === 'instagram') {
            // ১. পেজ কানেকশন তৈরি
            ChannelConnection::updateOrCreate(
                ['type' => 'facebook', 'page_id' => $data['page_id']],
                [
                    'page_name' => $data['page_name'],
                    'access_token' => $data['page_access_token'],
                    'connected_at' => now(),
                    'is_active' => true,
                ]
            );

            Channel::updateOrCreate(
                ['slug' => 'fb-' . $data['page_id']],
                [
                    'name' => $data['page_name'] . ' (FB)',
                    'type' => 'facebook',
                    'color' => '#1877F2',
                ]
            );

            if (!empty($data['ig_id'])) {
                ChannelConnection::updateOrCreate(
                    ['type' => 'instagram', 'ig_id' => $data['ig_id']],
                    [
                        'page_id' => $data['page_id'],
                        'ig_username' => $data['ig_username'],
                        'access_token' => $data['page_access_token'],
                        'connected_at' => now(),
                        'is_active' => true,
                    ]
                );

                Channel::updateOrCreate(
                    ['slug' => 'ig-' . $data['ig_id']],
                    [
                        'name' => '@' . $data['ig_username'] . ' (IG)',
                        'type' => 'instagram',
                        'color' => '#C13584',
                    ]
                );
            }
        } elseif ($data['type'] === 'whatsapp') {
            ChannelConnection::updateOrCreate(
                ['type' => 'whatsapp', 'phone_number_id' => $data['phone_number_id']],
                [
                    'waba_id' => $data['waba_id'],
                    'phone_number' => $data['phone_number'],
                    'business_name' => $data['business_name'],
                    'access_token' => $data['whatsapp_access_token'],
                    'connected_at' => now(),
                    'is_active' => true,
                ]
            );

            Channel::updateOrCreate(
                ['slug' => 'wa-' . $data['phone_number_id']],
                [
                    'name' => $data['business_name'] . ' (WA)',
                    'type' => 'whatsapp',
                    'color' => '#22C35E',
                ]
            );
        }

        return response()->json(['message' => 'Channel successfully registered via direct credentials.']);
    }
}
