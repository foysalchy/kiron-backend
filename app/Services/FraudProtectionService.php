<?php

namespace App\Services;

use App\Helpers\NetworkHelper;
use App\Models\IpDirectory;
use App\Models\IpSetting;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FraudProtectionService
{
    /**
     * Validate an order submission against Honeypot, Blocked IPs, BD Phone Format, and Rate Limits.
     *
     * @param Request $request
     * @param int|null $companyId
     * @return array ['allowed' => bool, 'message' => string|null, 'clean_phone' => string]
     */
    public function validateOrderSubmission(Request $request, ?int $companyId = null): array
    {
        $clientIp = NetworkHelper::getClientIp();
        $companyId = $companyId ?: (getCurrentCompany()->company_id ?? null);

        IpSetting::ensureColumnsExist();
        $ipSetting = $companyId
            ? IpSetting::withoutGlobalScopes()->where('company_id', $companyId)->first()
            : null;

        // 1. Honeypot Filter
        $honeypotEnabled = $ipSetting ? (bool) $ipSetting->honeypot_filter_enabled : true;
        if ($honeypotEnabled && $request->filled('_hp_security_check')) {
            Log::warning("Fraud protection: Honeypot trap triggered by bot", [
                'ip'         => $clientIp,
                'company_id' => $companyId,
                'trap_value' => $request->input('_hp_security_check'),
            ]);
            return [
                'allowed' => false,
                'message' => 'দুঃখিত, স্প্যাম সনাক্ত হয়েছে (Spam detected).',
                'clean_phone' => '',
            ];
        }

        // 2. Blocked IP Check
        if ($companyId) {
            $isBlocked = IpDirectory::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('ip_address', $clientIp)
                ->where('status', 0) // 0 = Blocked
                ->exists();

            if ($isBlocked) {
                Log::warning("Fraud protection: Blocked IP tried to submit order", [
                    'ip'         => $clientIp,
                    'company_id' => $companyId,
                ]);
                return [
                    'allowed' => false,
                    'message' => 'দুঃখিত, আপনার নেটওয়ার্ক থেকে অর্ডার সাময়িকভাবে স্থগিত রয়েছে। অনুগ্রহ করে আমাদের হেল্পলাইনে যোগাযোগ করুন।',
                    'clean_phone' => '',
                ];
            }
        }

        // 3. Bangladeshi Mobile Number Validation
        $rawPhone = (string) ($request->phone ?? '');
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($cleanPhone, '880') && strlen($cleanPhone) === 13) {
            $cleanPhone = '0' . substr($cleanPhone, 3);
        }

        if (!preg_match('/^01[3-9]\d{8}$/', $cleanPhone)) {
            return [
                'allowed' => false,
                'message' => 'অনুগ্রহ করে সঠিক ১১ ডিজিটের মোবাইল নাম্বার প্রদান করুন (যেমন: 01712345678)।',
                'clean_phone' => $cleanPhone,
            ];
        }

        // 4. Auto-block Threshold Check (frequency_minute & order_limit)
        if ($companyId && $ipSetting && (int)$ipSetting->frequency_minute > 0 && (int)$ipSetting->order_limit > 0) {
            $freqMin = (int) $ipSetting->frequency_minute;
            $orderLimit = (int) $ipSetting->order_limit;

            $recentIpOrders = Order::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('created_at', '>=', now()->subMinutes($freqMin))
                ->where(function ($query) use ($clientIp) {
                    $query->whereJsonContains('source_info->ip', $clientIp)
                          ->orWhereJsonContains('pixel_source_info->ip', $clientIp);
                })
                ->count();

            if ($recentIpOrders >= $orderLimit) {
                // Auto-block IP in directory
                try {
                    IpDirectory::withoutGlobalScopes()->updateOrCreate(
                        ['company_id' => $companyId, 'ip_address' => $clientIp],
                        ['status' => 0]
                    );
                    Log::warning("Fraud protection: IP {$clientIp} auto-blocked due to {$recentIpOrders} orders in {$freqMin} mins for company {$companyId}");
                } catch (\Throwable $e) {
                    Log::error("Failed to auto-block IP: " . $e->getMessage());
                }

                return [
                    'allowed' => false,
                    'message' => 'দুঃখিত, আপনার নেটওয়ার্ক থেকে অতিরিক্ত অর্ডার করার কারণে চেকআউট স্থগিত করা হয়েছে। অনুগ্রহ করে হেল্পলাইনে যোগাযোগ করুন।',
                    'clean_phone' => $cleanPhone,
                ];
            }
        }

        // 5. Order Limit / Cooldown per Phone & IP
        $orderLimitMinutes = (int) ($ipSetting?->order_limit_minutes ?: 5);
        $orderLimitCount   = (int) ($ipSetting?->order_limit_count ?: 1);

        if ($companyId && $orderLimitMinutes > 0 && $orderLimitCount > 0) {
            $phoneKey = "fraud_order_limit_phone_{$companyId}_{$cleanPhone}";
            $ipKey    = "fraud_order_limit_ip_{$companyId}_{$clientIp}";

            $phoneOrderCount = (int) Cache::get($phoneKey, 0);
            $ipOrderCount    = (int) Cache::get($ipKey, 0);

            if ($phoneOrderCount >= $orderLimitCount || $ipOrderCount >= max(3, $orderLimitCount * 3)) {
                $recentOrderId = Cache::get("fraud_last_order_id_phone_{$companyId}_{$cleanPhone}")
                    ?: Cache::get("fraud_last_order_id_ip_{$companyId}_{$clientIp}")
                    ?: session('last_placed_order_id');

                if (!$recentOrderId) {
                    $recentOrder = Order::withoutGlobalScopes()
                        ->where('company_id', $companyId)
                        ->where(function ($q) use ($cleanPhone, $clientIp) {
                            $q->where('shipping_address->phone', 'like', "%{$cleanPhone}%")
                              ->orWhereJsonContains('source_info->ip', $clientIp)
                              ->orWhereJsonContains('pixel_source_info->ip', $clientIp);
                        })
                        ->latest('id')
                        ->first();
                    $recentOrderId = $recentOrder?->id;
                }

                if ($phoneOrderCount >= $orderLimitCount) {
                    Log::warning("Fraud protection: Phone {$cleanPhone} exceeded order limit ({$phoneOrderCount}/{$orderLimitCount} in {$orderLimitMinutes} mins)");
                    return [
                        'allowed'          => false,
                        'already_placed'   => true,
                        'recent_order_id'  => $recentOrderId,
                        'order_limit_mins' => $orderLimitMinutes,
                        'message'          => "আপনি ইতিমধ্যে সম্প্রতি একটি অর্ডার সম্পন্ন করেছেন।",
                        'clean_phone'      => $cleanPhone,
                    ];
                }

                $maxIpLimit = max(3, $orderLimitCount * 3);
                if ($ipOrderCount >= $maxIpLimit) {
                    Log::warning("Fraud protection: IP {$clientIp} exceeded order limit ({$ipOrderCount}/{$maxIpLimit} in {$orderLimitMinutes} mins)");
                    return [
                        'allowed'          => false,
                        'already_placed'   => true,
                        'recent_order_id'  => $recentOrderId,
                        'order_limit_mins' => $orderLimitMinutes,
                        'message'          => "আপনার নেটওয়ার্ক থেকে ইতিমধ্যে সম্প্রতি অর্ডার সম্পন্ন হয়েছে।",
                        'clean_phone'      => $cleanPhone,
                    ];
                }
            }
        }

        return [
            'allowed'     => true,
            'message'     => null,
            'clean_phone' => $cleanPhone,
        ];
    }

    /**
     * Record a newly placed order in rate-limiter cache.
     *
     * @param int|null $companyId
     * @param string $phone
     * @param string|null $clientIp
     * @param int|null $orderId
     * @return void
     */
    public function recordOrderPlaced(?int $companyId = null, string $phone = '', ?string $clientIp = null, ?int $orderId = null): void
    {
        if (!$companyId) {
            return;
        }

        $clientIp = $clientIp ?: NetworkHelper::getClientIp();
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '880') && strlen($cleanPhone) === 13) {
            $cleanPhone = '0' . substr($cleanPhone, 3);
        }

        $ipSetting = IpSetting::withoutGlobalScopes()->where('company_id', $companyId)->first();
        $orderLimitMinutes = (int) ($ipSetting?->order_limit_minutes ?: 5);

        $ttl = now()->addMinutes(max(1, $orderLimitMinutes));

        if ($cleanPhone) {
            $phoneKey = "fraud_order_limit_phone_{$companyId}_{$cleanPhone}";
            $count = (int) Cache::get($phoneKey, 0);
            Cache::put($phoneKey, $count + 1, $ttl);

            if ($orderId) {
                Cache::put("fraud_last_order_id_phone_{$companyId}_{$cleanPhone}", $orderId, $ttl);
            }
        }

        if ($clientIp) {
            $ipKey = "fraud_order_limit_ip_{$companyId}_{$clientIp}";
            $count = (int) Cache::get($ipKey, 0);
            Cache::put($ipKey, $count + 1, $ttl);

            if ($orderId) {
                Cache::put("fraud_last_order_id_ip_{$companyId}_{$clientIp}", $orderId, $ttl);
            }
        }

        if ($orderId) {
            session()->put('last_placed_order_id', $orderId);
        }
    }
}
