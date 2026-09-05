<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Party;
use App\Models\SmsSend;
use App\Models\SmsTemplate;
use App\Enums\Status;
use Illuminate\Support\Facades\{DB, Log, Http};

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
     * Create Log and Send SMS to Gateway (Bulk SMS flow — from UI)
     * Supports {{customer_name}} personalization per recipient.
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
                        'name'  => null,
                    ])
                )->unique('phone')->values();

                if ($recipients->isEmpty()) {
                    throw ApiException::badRequest('Selected recipients do not have valid phone numbers.');
                }

                $messageBody = $data['body'] ?? $data['message'] ?? '';

                if (empty($messageBody)) {
                    throw ApiException::badRequest('SMS message cannot be empty.');
                }

                $companyName = auth()->user()?->company?->name
                    ?? \App\Models\Company::find($data['company_id'] ?? null)?->name
                    ?? 'dorja.io';
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
                    $personalizedMessage = $this->replacePlaceholder(
                        $messageBody,
                        'customer_name',
                        $recipient['name'] ?? 'Customer'
                    );

                    $personalizedMessage = $this->replacePlaceholder(
                        $personalizedMessage,
                        'company_name',
                        $companyName
                    );

                    Log::info('Sending SMS', [
                        'phone'   => $recipient['phone'],
                        'name'    => $recipient['name'] ?? 'N/A',
                        'message' => $personalizedMessage,
                    ]);

                    //   $this->sendToGateway([$recipient['phone']], $personalizedMessage);
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

    /**
     * Replace a single {{placeholder}} in a template string.
     */
    private function replacePlaceholder(string $template, string $key, ?string $value): string
    {
        return str_replace('{{' . $key . '}}', $value ?? '', $template);
    }

    private function calculateSmsCount(string $message, int $recipientCount): int
    {
        $messageLength = mb_strlen($message);

        $isUnicode = $this->isUnicode($message);

        if ($isUnicode) {
            $singleLimit = 70;
            $multiLimit  = 67;
        } else {
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
        $gatewayUrl = config('services.sms_gateway.url');

        if (empty($gatewayUrl)) {
            Log::warning('SMS Gateway URL is not configured in .env. Skipping HTTP request.', [
                'numbers' => $recipientString,
                'message' => $message,
            ]);
            return ['response_code' => 202, 'status' => 'skipped_no_config'];
        }

        $response = Http::asForm()
            ->timeout(30)
            ->post($gatewayUrl, [
                'api_key'  => config('services.sms_gateway.api_key'),
                'senderid' => config('services.sms_gateway.sender_id'),
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
     * Order status change হলে automated SMS পাঠানো হয়।
     * এখানে Order-এর সব data দিয়ে template render করা হয়, তারপর
     * সেই fully-rendered message createSmsSend()-এ পাঠানো হয়
     * (single recipient হিসেবে, order->customer_id দিয়ে)।
     */
    public function sendStatusBasedSms($order)
    {
        $slugMap = [
            Status::Pending->value   => 'order-place',
            Status::Confirmed->value => 'order-confirm',
            Status::Shipped->value   => 'order-shipped',
            Status::Delivered->value => 'order-delivered',
            Status::Cancelled->value => 'cancel-order',
        ];

        // ⚠️ NOTE: $order->status যদি raw string/int হয় (enum cast না থাকে),
        // তাহলে ->value ব্যবহার করলে error দিবে। raw value হলে সরাসরি $order->status ব্যবহার করুন:
        // $statusValue = $order->status instanceof Status ? $order->status->value : $order->status;
        $statusValue = $order->status instanceof Status ? $order->status->value : $order->status;

        $slug = $slugMap[$statusValue] ?? null;
        if (!$slug) return;

        $template = SmsTemplate::where('company_id', $order->company_id)
            ->where('slug', $slug)
            ->where('status', Status::Active->value)
            ->first();

        if (!$template) return;

        if (empty($order->customer?->phone)) {
            Log::warning("Order {$order->order_no}: customer has no phone, status SMS skipped");
            return;
        }

        // পুরো order data দিয়ে সব placeholder replace করে দাও (একবারেই)
        $messageBody = $this->renderOrderTemplate($template->description, $order);

        return $this->createSmsSend([
            'company_id'   => $order->company_id,
            'customer_ids' => [$order->customer_id],
            'body'         => $messageBody,
            'message'      => $messageBody,
        ]);
    }

    /**
     * Order object থেকে template-এর সব {{placeholder}} replace করে।
     * ORDER_PARAMETERS (frontend)-এর সাথে key মিলিয়ে রাখা হয়েছে।
     */
    private function renderOrderTemplate(string $template, $order): string
    {


        $replacements = [
            '{{customer_name}}' => $order->customer?->name ?? 'Customer',
            '{{company_name}}'  => $order->company?->name ?? '',
            '{{order_no}}'      => $order->order_no,
            '{{order_date}}'    => $order->order_date
                ? \Carbon\Carbon::parse($order->order_date)->format('d M Y')
                : '',
            '{{total_amount}}'  => number_format((float) $order->grand_total, 2),
            '{{due_amount}}'    => number_format(
                (float) ($order->grand_total - $order->payment_amount),
                2
            ),
            '{{paid_amount}}'   => number_format((float) $order->payment_amount, 2),
            '{{tracking_no}}'   => $this->getTrackingNo($order),
            '{{invoice_id}}'    => $order->order_no ?? $order->id,
            '{{invoice_link}}'  => route('order.invoice', $order->id),
        ];

        return strtr($template, $replacements);
    }
    private function getTrackingNo($order): string
    {
        $courierInfo = $order->courier_info;



        return $courierInfo['tracking_code'] ?? '-';
    }
}
