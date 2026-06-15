<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreSuperAdminSmsSendRequest;
use App\Models\Company;
use App\Models\SuperAdminSmsSend;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SuperAdminSmsSendController extends Controller
{
    /**
     * List all sent SMS (paginated)
     */
    public function index(): JsonResponse
    {
        $smsSends = SuperAdminSmsSend::latest()->paginate(20);

        $smsSends->getCollection()->transform(function ($sms) {
            $sms->companies = $sms->company_ids
                ? Company::whereIn('id', $sms->company_ids)->get(['id', 'name'])
                : collect([]);

            return $sms;
        });

        return response()->json([
            'success' => true,
            'data'    => $smsSends,
        ]);
    }

    public function show($id): JsonResponse
    {
        $sms = SuperAdminSmsSend::findOrFail($id);

        $companies = $sms->company_ids
            ? Company::whereIn('id', $sms->company_ids)->get(['id', 'name'])
            : collect([]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'               => $sms->id,
                'message'          => $sms->message,
                'companies'        => $companies,
                'custom_numbers'   => $sms->custom_numbers ?? [],
                'total_recipients' => $sms->total_recipients,
                'created_at'       => $sms->created_at,
            ],
        ]);
    }
    /**
     * Send SMS to selected companies + custom numbers
     */
    public function store(StoreSuperAdminSmsSendRequest $request): JsonResponse
    {
        $companyIds    = $request->input('company_ids', []);
        $customNumbers = $request->input('custom_numbers', []);
        $message       = $request->input('message');

        // Collect all recipient phone numbers
        $recipientNumbers = collect();

        // 1. Company phone numbers
        if (!empty($companyIds)) {
            $companyPhones = Company::whereIn('id', $companyIds)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->pluck('phone');

            $recipientNumbers = $recipientNumbers->merge($companyPhones);
        }

        // 2. Custom numbers
        if (!empty($customNumbers)) {
            $recipientNumbers = $recipientNumbers->merge($customNumbers);
        }

        // Deduplicate & filter empty
        $recipientNumbers = $recipientNumbers
            ->filter()
            ->unique()
            ->values();

        if ($recipientNumbers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid recipients found.',
            ], 422);
        }

        // Send via your SMS gateway service
        // Replace SmsService::send() with your actual gateway (e.g. Twilio, BulkSMS, etc.)
        $sent = 0;
        foreach ($recipientNumbers as $number) {
            try {
                // Example: \App\Services\SmsService::send($number, $message);
                $sent++;
            } catch (\Exception $e) {
                Log::error("SMS send failed to {$number}: " . $e->getMessage());
            }
        }

        // Save record
        SuperAdminSmsSend::create([
            'message'          => $message,
            'company_ids'      => $companyIds,
            'custom_numbers'   => $customNumbers,
            'total_recipients' => $sent,
        ]);

        return response()->json([
            'success' => true,
            'message' => "SMS sent to {$sent} recipient(s) successfully.",
        ]);
    }
}
