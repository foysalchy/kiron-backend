<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Mail\SendEmail;
use App\Models\EmailSend;
use App\Models\Party;
use Illuminate\Support\Facades\{DB, Log, Mail};

class EmailSendService
{
    /**
     * Get all Email logs
     */
    public function getAllEmailLogs(array $filters)
    {
        try {
            return EmailSend::query()
                ->when(!empty($filters['search']), function ($q) use ($filters) {
                    $q->where('subject', 'like', "%{$filters['search']}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($filters['per_page'] ?? 15);
        } catch (\Exception $e) {
            Log::error('Error fetching Email logs: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch Email logs');
        }
    }

    /**
     * Store record and Send Email
     */
    public function createEmailSend(array $data): EmailSend
    {
        return DB::transaction(function () use ($data) {
            try {
                // 1. Fetch customer emails
                $customers = Party::whereIn('id', $data['customer_ids'])
                    ->whereNotNull('email')
                    ->where('email', '!=', '')
                    ->get(['id', 'email']); // fetch id too for better logging

                if ($customers->isEmpty()) {
                    throw ApiException::validationError('Selected customers do not have valid email addresses.');
                }

                $emailSend = EmailSend::create($data);

                $successCount = 0;
                $failedRecipients = [];

                foreach ($customers as $index => $customer) {
                    try {
                        Mail::to($customer->email)->later(
                            now()->addSeconds($index * 3),
                            new SendEmail($data['subject'], $data['body'])
                        );

                        $successCount++;

                        Log::info('Email queued successfully', [
                            'email_send_id' => $emailSend->id,
                            'company_id'    => $emailSend->company_id,
                            'recipient'     => $customer->email,
                            'customer_id'   => $customer->id,
                            'delay_seconds' => $index * 3,
                            'queued_at'     => now()->toDateTimeString(),
                        ]);
                    } catch (\Exception $e) {
                        $failedRecipients[] = [
                            'customer_id' => $customer->id,
                            'email'       => $customer->email,
                            'reason'      => $e->getMessage(),
                        ];

                        Log::error('Failed to queue email for recipient', [
                            'email_send_id' => $emailSend->id,
                            'company_id'    => $emailSend->company_id,
                            'recipient'     => $customer->email,
                            'customer_id'   => $customer->id,
                            'error'         => $e->getMessage(),
                        ]);
                    }
                }

                // Summary log
                if (!empty($failedRecipients)) {
                    Log::warning('Email batch completed with some failures', [
                        'email_send_id'    => $emailSend->id,
                        'company_id'       => $emailSend->company_id,
                        'total_recipients' => $customers->count(),
                        'success_count'    => $successCount,
                        'failed_count'     => count($failedRecipients),
                        'failed_recipients' => $failedRecipients,
                    ]);
                } else {
                    Log::info('Email batch queued successfully — all recipients', [
                        'email_send_id'    => $emailSend->id,
                        'company_id'       => $emailSend->company_id,
                        'total_recipients' => $customers->count(),
                        'success_count'    => $successCount,
                    ]);
                }

                LogHelper::created(
                    'email_send',
                    $emailSend->id,
                    $emailSend->company_id,
                    "Email queued: {$successCount}/{$customers->count()} recipients succeeded"
                        . (!empty($failedRecipients) ? ' | ' . count($failedRecipients) . ' failed' : '')
                );

                return $emailSend;
            } catch (\Exception $e) {
                Log::error('Email processing failed entirely', [
                    'error'      => $e->getMessage(),
                    'company_id' => $data['company_id'] ?? null,
                    'subject'    => $data['subject'] ?? null,
                    'trace'      => $e->getTraceAsString(),
                ]);

                throw ApiException::serverError('Failed to process and send emails: ' . $e->getMessage());
            }
        });
    }

    public function getEmailLogById(int $id): EmailSend
    {
        $log = EmailSend::find($id);
        if (!$log) throw ApiException::notFound('Email record');
        $log->customers = Party::whereIn('id', $log->customer_ids ?? [])->select('id', 'name')->get();

        return $log;
    }
}
