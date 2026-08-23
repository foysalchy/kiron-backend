<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Market;
use App\Jobs\SendFacebookCapiEvent;
use App\Jobs\SendTiktokCapiEvent;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        try {
            $market = Market::where('company_id', $order->company_id)->first();
            
            if (!$market) {
                return;
            }
            
            // Common User Data
            $ip = request()->ip();
            $userAgent = request()->userAgent();
            $email = $order->billing_email ?? $order->customer->email ?? null;
            $phone = $order->billing_phone ?? $order->customer->phone ?? null;
            
            // Format phone to international if needed, hash email & phone for FB
            $hashedEmail = $email ? hash('sha256', strtolower(trim($email))) : null;
            $hashedPhone = $phone ? hash('sha256', ltrim(trim($phone), '0+')) : null; // simplified
            
            $fbp = request()->cookie('_fbp');
            $fbc = request()->cookie('_fbc');
            $ttp = request()->cookie('_ttp');
            
            $eventId = 'ORDER_' . $order->id;
            $value = $order->grand_total ?? $order->total_amount ?? 0;
            $currency = config('app.currency', 'BDT'); // Default to BDT if not set
            $url = request()->headers->get('referer') ?? config('app.url');

            // Dispatch Facebook CAPI
            if (!empty($market->facebook_pixel_id) && !empty($market->meta_access_token)) {
                $fbUserData = array_filter([
                    'client_ip_address' => $ip,
                    'client_user_agent' => $userAgent,
                    'em' => $hashedEmail ? [$hashedEmail] : null,
                    'ph' => $hashedPhone ? [$hashedPhone] : null,
                    'fbp' => $fbp,
                    'fbc' => $fbc,
                ]);

                $fbCustomData = [
                    'value' => $value,
                    'currency' => $currency,
                    'content_type' => 'product',
                    'content_ids' => [$order->id],
                ];

                SendFacebookCapiEvent::dispatch(
                    $market->facebook_pixel_id,
                    $market->meta_access_token,
                    'Purchase',
                    ['event_time' => time()],
                    $fbUserData,
                    $fbCustomData,
                    $eventId,
                    $url
                );
            }

            // Dispatch TikTok CAPI
            if (!empty($market->tiktok_pixel_id) && !empty($market->tiktok_access_token)) {
                $ttUserData = array_filter([
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'email' => $hashedEmail,
                    'phone_number' => $hashedPhone,
                    'ttp' => $ttp,
                ]);

                $ttCustomData = [
                    'value' => $value,
                    'currency' => $currency,
                    'contents' => [
                        [
                            'content_id' => (string)$order->id,
                            'content_type' => 'product',
                            'quantity' => 1,
                            'price' => $value
                        ]
                    ]
                ];

                SendTiktokCapiEvent::dispatch(
                    $market->tiktok_pixel_id,
                    $market->tiktok_access_token,
                    'CompletePayment', // PlaceAnOrder is also valid, but CompletePayment/Purchase is standard
                    ['event_time' => time()],
                    $ttUserData,
                    $ttCustomData,
                    $eventId,
                    $url
                );
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OrderObserver CAPI Error: ' . $e->getMessage());
        }
    }
}
