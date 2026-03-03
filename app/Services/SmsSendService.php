<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Party;
use App\Models\SmsSend;
use Illuminate\Support\Facades\{DB,Log};

class SmsSendService
{
    /**
     * Get all SMS logs with filters.
     */
    public function getAllSmsSends(array $filters)
    {
        try {
            return SmsSend::query()
                ->when(!empty($filters['search']), function ($q) use ($filters) {
                    $q->where('message', 'like', "%{$filters['search']}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($filters['per_page'] ?? 15);
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
                // 1. Fetch phone numbers of selected customers
                $phoneNumbers = Party::whereIn('id', $data['customer_ids'])
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->pluck('phone')
                    ->toArray();

                if (empty($phoneNumbers)) {
                    throw ApiException::validationError('Selected customers do not have valid phone numbers.');
                }

                // Store the record in your database
                $smsSend = SmsSend::create($data);

                // Send to actual Mobile Gateway
                $this->sendToGateway($phoneNumbers, $data['message']);

                // Log the success
                LogHelper::created('sms_send', $smsSend->id, $smsSend->company_id, 'SMS sent to ' . count($phoneNumbers) . ' customers');

                return $smsSend;

            } catch (\Exception $e) {
                Log::error("SMS Sending failed: " . $e->getMessage());
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
        $smsSend = SmsSend::find($id);
        if (!$smsSend) throw ApiException::notFound('SMS record');
        return $smsSend;
    }
}
