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
            
            // Extract from shipping address or fallback to customer
            $shipping = $order->shipping_address ?? [];
            $email = $shipping['email'] ?? $order->billing_email ?? $order->customer->email ?? null;
            $phone = $shipping['phone'] ?? $order->billing_phone ?? $order->customer->phone ?? null;
            $name = $shipping['name'] ?? $order->customer->name ?? '';
            $city = $shipping['district'] ?? $order->customer->district ?? '';
            $state = $shipping['division'] ?? $order->customer->division ?? '';
            $zip = $shipping['post_code'] ?? $order->customer->post_code ?? '';
            
            $setup = \App\Models\SiteSetting::where('company_id', $order->company_id)->first();
            $country = strtolower(trim($setup->country ?? 'bd'));

            // Helper to clean names and cities (lowercase, remove punctuation/spaces)
            $cleanStr = function($str) {
                return strtolower(preg_replace('/[^a-z0-9]/i', '', $str));
            };

            // Split name into First and Last
            $nameParts = explode(' ', trim($name));
            $firstName = $nameParts[0] ?? '';
            $lastName = count($nameParts) > 1 ? end($nameParts) : $firstName;

            // Format phone to international (assumes BD numbers mostly, add 88 if missing)
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($cleanPhone) == 11 && str_starts_with($cleanPhone, '01')) {
                $cleanPhone = '88' . $cleanPhone;
            }

            // Hash user data
            $hashedEmail = $email ? hash('sha256', strtolower(trim($email))) : null;
            $hashedPhone = $cleanPhone ? hash('sha256', $cleanPhone) : null;
            $hashedFn = $firstName ? hash('sha256', $cleanStr($firstName)) : null;
            $hashedLn = $lastName ? hash('sha256', $cleanStr($lastName)) : null;
            $hashedCt = $city ? hash('sha256', $cleanStr($city)) : null;
            $hashedSt = $state ? hash('sha256', $cleanStr($state)) : null;
            $hashedZip = $zip ? hash('sha256', $cleanStr($zip)) : null;
            $hashedCountry = hash('sha256', $country);

            // Laravel EncryptCookies middleware strips _fbp and _fbc, so read them natively
            $fbp = $_COOKIE['_fbp'] ?? request()->cookie('_fbp') ?? null;
            $fbc = $_COOKIE['_fbc'] ?? request()->cookie('_fbc') ?? null;
            
            $eventId = 'ORDER_' . $order->id;
            $value = $order->grand_total ?? $order->total_amount ?? 0;
            
            $sysCurrency = config('app.currency', 'BDT');
            $currencyMap = [
                '$' => 'USD',
                '৳' => 'BDT',
                '€' => 'EUR',
                '﷼' => 'SAR',
                '(د.إ' => 'AED',
                '£' => 'GBP'
            ];
            $currency = $currencyMap[$sysCurrency] ?? (strlen($sysCurrency) === 3 ? $sysCurrency : 'BDT');
            
            $url = request()->headers->get('referer') ?? config('app.url');

            // Dispatch Facebook CAPI
            if (!empty($market->facebook_pixel_id) && !empty($market->meta_access_token)) {
                $fbUserData = array_filter([
                    'client_ip_address' => $ip,
                    'client_user_agent' => $userAgent,
                    'em' => $hashedEmail ? [$hashedEmail] : null,
                    'ph' => $hashedPhone ? [$hashedPhone] : null,
                    'fn' => $hashedFn ? [$hashedFn] : null,
                    'ln' => $hashedLn ? [$hashedLn] : null,
                    'ct' => $hashedCt ? [$hashedCt] : null,
                    'st' => $hashedSt ? [$hashedSt] : null,
                    'zp' => $hashedZip ? [$hashedZip] : null,
                    'country' => [$hashedCountry],
                    'external_id' => $hashedPhone ? [$hashedPhone] : null,
                    'fbp' => $fbp,
                    'fbc' => $fbc,
                ]);

                $fbCustomData = [
                    'value' => $value,
                    'currency' => $currency,
                    'content_type' => 'product',
                    'content_ids' => [$order->id],
                ];

                // Only send Purchase instantly if instant_purchase_event is true
                if ($market->instant_purchase_event !== false) {
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
                    
                    // Mark as fired without triggering updated event
                    Order::withoutEvents(function () use ($order) {
                        $order->update(['fb_purchase_event_fired' => true]);
                    });
                }
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

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            $market = Market::where('company_id', $order->company_id)->first();
            if (!$market || empty($market->facebook_pixel_id) || empty($market->meta_access_token)) {
                return;
            }

            // Current status name based on Enum
            $statusValue = $order->status instanceof \App\Enums\Status ? $order->status->value : (int)$order->status;
            
            // Map integer status back to string if needed for comparison, but frontend sends integer value
            // Actually, the settings dropdown will save the integer value of the status.
            $targetStatus = $market->purchase_event_status;
            $isCancelled = $statusValue === \App\Enums\Status::Cancelled->value;

            // Fire Purchase if not instant and matches target status
            if ($market->instant_purchase_event === false && $targetStatus !== null && (int)$statusValue === (int)$targetStatus && !$order->fb_purchase_event_fired) {
                $this->fireCustomEvent($order, $market, 'Purchase');
                Order::withoutEvents(function () use ($order) {
                    $order->update(['fb_purchase_event_fired' => true]);
                });
            }

            // Fire Cancel event
            if ($market->fire_cancel_event && $isCancelled && !$order->fb_cancel_event_fired && $order->fb_purchase_event_fired) {
                $this->fireCustomEvent($order, $market, 'Cancel'); // Standard FB event name or custom
                Order::withoutEvents(function () use ($order) {
                    $order->update(['fb_cancel_event_fired' => true]);
                });
            }
        }
    }

    private function fireCustomEvent(Order $order, Market $market, string $eventName): void
    {
        try {
            $ip = request()->ip();
            $userAgent = request()->userAgent();
            
            $shipping = $order->shipping_address ?? [];
            $email = $shipping['email'] ?? $order->billing_email ?? $order->customer->email ?? null;
            $phone = $shipping['phone'] ?? $order->billing_phone ?? $order->customer->phone ?? null;
            $name = $shipping['name'] ?? $order->customer->name ?? '';
            $city = $shipping['district'] ?? $order->customer->district ?? '';
            $state = $shipping['division'] ?? $order->customer->division ?? '';
            $zip = $shipping['post_code'] ?? $order->customer->post_code ?? '';
            
            $setup = \App\Models\SiteSetting::where('company_id', $order->company_id)->first();
            $country = strtolower(trim($setup->country ?? 'bd'));

            $cleanStr = function($str) {
                return strtolower(preg_replace('/[^a-z0-9]/i', '', $str));
            };

            $nameParts = explode(' ', trim($name));
            $firstName = $nameParts[0] ?? '';
            $lastName = count($nameParts) > 1 ? end($nameParts) : $firstName;

            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($cleanPhone) == 11 && str_starts_with($cleanPhone, '01')) {
                $cleanPhone = '88' . $cleanPhone;
            }

            $hashedEmail = $email ? hash('sha256', strtolower(trim($email))) : null;
            $hashedPhone = $cleanPhone ? hash('sha256', $cleanPhone) : null;
            $hashedFn = $firstName ? hash('sha256', $cleanStr($firstName)) : null;
            $hashedLn = $lastName ? hash('sha256', $cleanStr($lastName)) : null;
            $hashedCt = $city ? hash('sha256', $cleanStr($city)) : null;
            $hashedSt = $state ? hash('sha256', $cleanStr($state)) : null;
            $hashedZip = $zip ? hash('sha256', $cleanStr($zip)) : null;
            $hashedCountry = hash('sha256', $country);

            $fbp = $_COOKIE['_fbp'] ?? request()->cookie('_fbp') ?? null;
            $fbc = $_COOKIE['_fbc'] ?? request()->cookie('_fbc') ?? null;
            
            $eventId = strtoupper($eventName) . '_' . $order->id;
            $value = $order->grand_total ?? $order->total_amount ?? 0;
            
            $sysCurrency = config('app.currency', 'BDT');
            $currencyMap = ['$' => 'USD', '৳' => 'BDT', '€' => 'EUR', '﷼' => 'SAR', '(د.إ' => 'AED', '£' => 'GBP'];
            $currency = $currencyMap[$sysCurrency] ?? (strlen($sysCurrency) === 3 ? $sysCurrency : 'BDT');
            
            $url = request()->headers->get('referer') ?? config('app.url');

            $fbUserData = array_filter([
                'client_ip_address' => $ip,
                'client_user_agent' => $userAgent,
                'em' => $hashedEmail ? [$hashedEmail] : null,
                'ph' => $hashedPhone ? [$hashedPhone] : null,
                'fn' => $hashedFn ? [$hashedFn] : null,
                'ln' => $hashedLn ? [$hashedLn] : null,
                'ct' => $hashedCt ? [$hashedCt] : null,
                'st' => $hashedSt ? [$hashedSt] : null,
                'zp' => $hashedZip ? [$hashedZip] : null,
                'country' => [$hashedCountry],
                'external_id' => $hashedPhone ? [$hashedPhone] : null,
                'fbp' => $fbp,
                'fbc' => $fbc,
            ]);

            $fbCustomData = [
                'value' => $value,
                'currency' => $currency,
                'content_type' => 'product',
                'content_ids' => [$order->id],
            ];

            \App\Jobs\SendFacebookCapiEvent::dispatch(
                $market->facebook_pixel_id,
                $market->meta_access_token,
                $eventName,
                ['event_time' => time()],
                $fbUserData,
                $fbCustomData,
                $eventId,
                $url
            );

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OrderObserver fireCustomEvent Error: ' . $e->getMessage());
        }
    }
}
