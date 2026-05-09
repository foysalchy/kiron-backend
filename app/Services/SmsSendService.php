<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Party;
use App\Models\SmsSend;
use Illuminate\Support\Facades\{DB, Log};

class SmsSendService
{
    /**
     * Get all SMS logs with filters.
     */
    public function __construct(
        private SmsWalletService $smsWalletService
    ) {}
    public function getAllSmsSends(array $filters)
    {
        try {
            $logs = SmsSend::query()
                ->when(!empty($filters['search']), function ($q) use ($filters) {
                    $q->where('message', 'like', "%{$filters['search']}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($filters['per_page'] ?? 15);

            $allIds = [];

            foreach ($logs as $log) {
                $allIds = array_merge(
                    $allIds,
                    $log->customer_ids ?? [],
                    $log->supplier_ids ?? []
                );
            }

            $allIds = array_unique($allIds);

            $parties = Party::whereIn('id', $allIds)
                ->select('id', 'name', 'type')
                ->get()
                ->groupBy('id');

            foreach ($logs as $log) {
                $customerIds = $log->customer_ids ?? [];
                $supplierIds = $log->supplier_ids ?? [];

                $log->customers = collect($customerIds)
                    ->map(fn($id) => $parties[$id][0] ?? null)
                    ->filter()
                    ->values();

                $log->suppliers = collect($supplierIds)
                    ->map(fn($id) => $parties[$id][0] ?? null)
                    ->filter()
                    ->values();
            }

            return $logs;
        } catch (\Exception $e) {
            Log::error("Error fetching SMS logs: " . $e->getMessage());
            throw ApiException::serverError('Failed to fetch SMS logs');
        }
    }

    /**
     * Create Log and Send SMS to Gateway
     */
    public function createSmsSend(array $data): SmsSend
    {
        return DB::transaction(function () use ($data) {
            try {
                // 1. Merge System Party IDs
                $partyIds = array_unique(array_merge(
                    $data['customer_ids'] ?? [],
                    $data['supplier_ids'] ?? []
                ));

                $customNumbers = $data['custom_numbers'] ?? [];
                $phoneNumbers = [];

                // 2. Fetch phone numbers for system parties
                if (!empty($partyIds)) {
                    $systemPhones = Party::whereIn('id', $partyIds)
                        ->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->pluck('phone')
                        ->toArray();

                    $phoneNumbers = array_merge($phoneNumbers, $systemPhones);
                }

                // 3. Add Custom Numbers
                foreach ($customNumbers as $number) {
                    $cleanNumber = trim($number);
                    if (!empty($cleanNumber)) {
                        $phoneNumbers[] = $cleanNumber;
                    }
                }

                // 4. Keep only unique phone numbers
                $phoneNumbers = array_unique($phoneNumbers);

                if (empty($phoneNumbers)) {
                    throw ApiException::badRequest('Selected recipients do not have valid phone numbers.');
                }

                $messageBody = $data['body'] ?? $data['message'] ?? '';

                if (empty($messageBody)) {
                    throw ApiException::badRequest('SMS message cannot be empty.');
                }

                // ✅ 5. Balance check
                $smsCount = count($phoneNumbers);
                $wallet   = $this->smsWalletService->getOrCreate();

                if (!$wallet->hasSufficientBalance($smsCount)) {
                    throw ApiException::badRequest(
                        "Insufficient SMS balance. Required: {$smsCount}, Available: {$wallet->sms_count}"
                    );
                }

                // 6. Store record
                $smsSend = SmsSend::create(array_merge($data, [
                    'total_recipients' => $smsCount,
                    'sms_count'        => $smsCount,
                    'rate_per_sms'     => $wallet->currentRate(),
                ]));

                // 7. Send to gateway
                $this->sendToGateway($phoneNumbers, $messageBody);

                // ✅ 8. Deduct balance
                $this->smsWalletService->deductBalance(
                    $smsCount,
                    $smsSend->id,
                    $smsSend->rate_per_sms ?? 0
                );

                LogHelper::created(
                    'sms_send',
                    $smsSend->id,
                    $smsSend->company_id,
                    'SMS sent to ' . $smsCount . ' recipients'
                );

                return $smsSend;
            } catch (ApiException $e) {
                throw $e;
            } catch (\Exception $e) {
                Log::error("SMS Sending failed: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                throw ApiException::serverError('Failed to process SMS request');
            }
        });
    }

    /**
     * Logic to communicate with SMS Provider API
     */
    protected function sendToGateway(array $numbers, string $message)
    {
        $recipientString = implode(',', $numbers);

        // This is a placeholder for your actual Gateway API call
        /*
        $response = Http::get("https://api.sms-provider.com/send", [
            "api_key"   => config('services.sms.key'),
            "sender_id" => config('services.sms.sender_id'),
            "number"    => $recipientString,
            "message"   => $message
        ]);
        */

        Log::info("SMS Request sent to Gateway for: " . $recipientString);
    }

    public function getSmsSendById(int $id): SmsSend
    {
        $log = SmsSend::find($id);
        if (!$log) throw ApiException::notFound('SMS record');

        $allIds = array_merge($log->customer_ids ?? [], $log->supplier_ids ?? []);
        $parties = Party::whereIn('id', $allIds)->select('id', 'name', 'type')->get();

        $log->customers = $parties->where('type', Party::TYPE_CUSTOMER)->values();
        $log->suppliers = $parties->where('type', Party::TYPE_SUPPLIER)->values();

        return $log;
    }
}
