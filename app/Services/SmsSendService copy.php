<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Party;
use App\Models\SmsSend;
use Illuminate\Support\Facades\{DB, Log};
use Http;

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
                // 1. Fetch system parties with id + name + phone (personalization এর জন্য)
                $partyIds = array_unique(array_merge(
                    $data['customer_ids'] ?? [],
                    $data['supplier_ids'] ?? []
                ));

                $systemParties = collect();
                if (!empty($partyIds)) {
                    $systemParties = Party::whereIn('id', $partyIds)
                        ->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->get(['id', 'name', 'phone']);
                }

                // 2. Custom numbers (এদের নাম নেই, তাই placeholder replace হবে না — raw number)
                $customNumbers = collect($data['custom_numbers'] ?? [])
                    ->map(fn($n) => trim($n))
                    ->filter()
                    ->unique();

                // 3. Recipients list বানাও: [ ['phone' => ..., 'name' => ...], ... ]
                $recipients = $systemParties->map(fn($party) => [
                    'phone' => $party->phone,
                    'name'  => $party->name,
                ])->concat(
                    $customNumbers->map(fn($number) => [
                        'phone' => $number,
                        'name'  => null, // custom number-এ customer_name বসবে না
                    ])
                )->unique('phone')->values();

                if ($recipients->isEmpty()) {
                    throw ApiException::badRequest('Selected recipients do not have valid phone numbers.');
                }

                $messageBody = $data['body'] ?? $data['message'] ?? '';

                if (empty($messageBody)) {
                    throw ApiException::badRequest('SMS message cannot be empty.');
                }

                $recipientCount = $recipients->count();
                $smsCount       = $this->calculateSmsCount($messageBody, $recipientCount);

                // Balance check
                $wallet = $this->smsWalletService->getOrCreate();
                if (!$wallet->hasSufficientBalance($smsCount)) {
                    throw ApiException::badRequest(
                        "Insufficient SMS balance. Required: {$smsCount}, Available: {$wallet->sms_count}"
                    );
                }

                // Store record (raw template body-ই save হবে, replaced version না)
                $smsSend = SmsSend::create(array_merge($data, [
                    'total_recipients' => $smsCount,
                    'sms_count'        => $smsCount,
                    'rate_per_sms'     => $wallet->currentRate(),
                ]));

                // 4. Send to gateway — প্রতিটা recipient-এর জন্য personalized message
                foreach ($recipients as $recipient) {
                    $personalizedMessage = $this->renderTemplate($messageBody, $recipient['name']);
                    Log::info('Sending SMS', [
                        'phone'   => $recipient['phone'],
                        'name'    => $recipient['name'] ?? 'N/A',
                        'message' => $personalizedMessage,
                    ]);
                    $this->sendToGateway([$recipient['phone']], $personalizedMessage);
                }

                // Deduct balance
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

    private function renderTemplate(string $template, ?string $customerName): string
    {
        return str_replace(
            '{{customer_name}}',
            $customerName ?? 'Customer',
            $template
        );
    }
    private function calculateSmsCount(string $message, int $recipientCount): int
    {
        $messageLength = mb_strlen($message);

        // Unicode detect (Bengali, Arabic etc)
        $isUnicode = $this->isUnicode($message);

        if ($isUnicode) {
            // Unicode: 1st SMS = 70 chars, subsequent = 67 chars
            $singleLimit = 70;
            $multiLimit  = 67;
        } else {
            // ASCII: 1st SMS = 160 chars, subsequent = 153 chars
            $singleLimit = 160;
            $multiLimit  = 153;
        }

        if ($messageLength <= $singleLimit) {
            $smsPerRecipient = 1;
        } else {
            $smsPerRecipient = (int) ceil(($messageLength - $singleLimit) / $multiLimit) + 1;
        }

        return $smsPerRecipient * $recipientCount;
    }

    private function isUnicode(string $message): bool
    {
        return mb_strlen($message) !== strlen($message)
            || preg_match('/[^\x00-\x7F]/', $message);
    }
    /**
     * Logic to communicate with SMS Provider API
     */
    public function sendToGateway(array $numbers, string $message): array
    {
        $recipientString = implode(',', array_unique($numbers));

        $response = Http::asForm()
            ->timeout(30)
            ->post('http://bulksmsbd.net/api/smsapi', [
                'api_key'  => 'my api key',
                'senderid' => 'my sener api',
                'number'   => $recipientString,
                'message'  => $message,
            ]);

        if (!$response->successful()) {
            Log::error('SMS Gateway HTTP Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw ApiException::serverError('SMS gateway connection failed.');
        }





        // Optional: adjust according to provider response
        $data = $response->json();
        Log::info('SMS Gateway Response', [
            'response' => $data,
        ]);
        if (($data['response_code'] ?? 0) != 202) {
            throw ApiException::serverError(
                $data['error_message'] ?? 'SMS sending failed.'
            );
        }

        return [
            'success'  => true,
            'response' => $data,
        ];
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
    /**
     *send sms
     */
    public function sendStatusBasedSms($order)
    {
        // ১. স্ট্যাটাস এবং স্লাগের ম্যাপিং
        $slugMap = [
            \App\Enums\Status::Pending->value   => 'order-place',
            \App\Enums\Status::Confirmed->value => 'order-confirm',
            \App\Enums\Status::Shipped->value   => 'order-shipped',
            \App\Enums\Status::Delivered->value => 'order-delivered',
            \App\Enums\Status::Cancelled->value => 'cancel-order',
        ];


        $slug = $slugMap[$order->status->value] ?? null;
        if (!$slug) return;


        $template = \App\Models\SmsTemplate::where('company_id', $order->company_id)
            ->where('slug', $slug)
            ->where('status', \App\Enums\Status::Active->value)
            ->first();

        if (!$template) return;

        $replaceData = [
            '{customer_name}' => $order->customer->name ?? 'Customer',
            '{company_name}'  => $order->company->name ?? '-',
            '{invoice_id}'    => $order->order_no ?? $order->id,
            '{invoice_link}'  => route('order.invoice', $order->id),
        ];

        $messageBody = str_replace(array_keys($replaceData), array_values($replaceData), $template->description);

        return $this->createSmsSend([
            'company_id'   => $order->company_id,
            'customer_ids' => [$order->customer_id],
            'body'         => $messageBody,
            'message'      => $messageBody,
        ]);
    }
}
