<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
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
    public function connectPage(Request $request, string $sessionId)
    {
        $session = Cache::get("meta_session:{$sessionId}");
        abort_unless($session, 400, 'Session expired');

        $data = $request->validate([
            'page_id' => 'required|string',
            'page_name' => 'required|string',
            'page_access_token' => 'required|string',
            'ig_id' => 'nullable|string',
            'ig_username' => 'nullable|string',
        ]);

        $this->meta->subscribePageToWebhooks($data['page_id'], $data['page_access_token']);

        $fb = ChannelConnection::updateOrCreate(
            ['company_id' => $session['company_id'], 'type' => 'facebook'],
            [
                'page_id' => $data['page_id'],
                'page_name' => $data['page_name'],
                'access_token' => $data['page_access_token'],
                'connected_at' => now(),
                'is_active' => true,
            ]
        );

        if (!empty($data['ig_id'])) {
            ChannelConnection::updateOrCreate(
                ['company_id' => $session['company_id'], 'type' => 'instagram'],
                [
                    'page_id' => $data['page_id'],
                    'ig_id' => $data['ig_id'],
                    'ig_username' => $data['ig_username'] ?? null,
                    'access_token' => $data['page_access_token'],
                    'connected_at' => now(),
                    'is_active' => true,
                ]
            );
        }

        Cache::forget("meta_session:{$sessionId}");

        return response()->json(['message' => 'Connected', 'facebook' => $fb]);
    }

    // STEP 3d (whatsapp): list WABAs + phone numbers
    public function whatsappAccounts(Request $request, string $sessionId)
    {
        $session = Cache::get("meta_session:{$sessionId}");
        abort_unless($session, 400, 'Session expired');

        return response()->json($this->meta->getWhatsappBusinessAccounts($session['user_token']));
    }

    // STEP 3e: finalize WhatsApp connection with chosen number
    public function connectWhatsapp(Request $request, string $sessionId)
    {
        $session = Cache::get("meta_session:{$sessionId}");
        abort_unless($session, 400, 'Session expired');

        $data = $request->validate([
            'waba_id' => 'required|string',
            'phone_number_id' => 'required|string',
            'phone_number' => 'required|string',
            'business_name' => 'nullable|string',
        ]);

        $wa = ChannelConnection::updateOrCreate(
            ['company_id' => $session['company_id'], 'type' => 'whatsapp'],
            [
                'waba_id' => $data['waba_id'],
                'phone_number_id' => $data['phone_number_id'],
                'phone_number' => $data['phone_number'],
                'business_name' => $data['business_name'] ?? null,
                'access_token' => $session['user_token'],
                'connected_at' => now(),
                'is_active' => true,
            ]
        );

        Cache::forget("meta_session:{$sessionId}");

        return response()->json(['message' => 'Connected', 'whatsapp' => $wa]);
    }

    public function disconnect(Request $request, ChannelConnection $connection)
    {
        abort_unless($connection->company_id === $request->user()->company_id, 403);
        $connection->update(['is_active' => false, 'access_token' => '']);
        return response()->json(['message' => 'Disconnected']);
    }
}
