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
            $logs = EmailSend::query()
                ->when(!empty($filters['search']), function ($q) use ($filters) {
                    $q->where('subject', 'like', "%{$filters['search']}%");
                })
                ->orderBy('created_at', 'desc')
                ->paginate($filters['per_page'] ?? 15);

            // 🔥 Collect all party IDs from all logs
            $allIds = [];

            foreach ($logs as $log) {
                $allIds = array_merge(
                    $allIds,
                    $log->customer_ids ?? [],
                    $log->supplier_ids ?? []
                );
            }

            $allIds = array_unique($allIds);

            // 🔥 Get all parties in one query
            $parties = Party::whereIn('id', $allIds)
                ->select('id', 'name', 'type')
                ->get()
                ->groupBy('id');

            // 🔥 Map customers & suppliers to each log
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
                // Merge customer_ids and supplier_ids uniquely
                $partyIds = array_unique(array_merge(
                    $data['customer_ids'] ?? [],
                    $data['supplier_ids'] ?? []
                ));

                $customEmails = $data['custom_emails'] ?? [];
                $recipients = [];

                // 1. Fetch System parties (Customers & Suppliers) — name সহ
                if (!empty($partyIds)) {
                    $parties = Party::whereIn('id', $partyIds)
                        ->whereNotNull('email')
                        ->where('email', '!=', '')
                        ->get(['id', 'name', 'email']);

                    foreach ($parties as $party) {
                        $recipients[] = [
                            'id'    => $party->id,
                            'name'  => $party->name,
                            'email' => $party->email,
                            'type'  => 'system_party'
                        ];
                    }
                }

                // 2. Format Custom Emails (নাম নেই, তাই null থাকবে)
                foreach ($customEmails as $email) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $alreadyExists = collect($recipients)->contains('email', $email);
                        if (!$alreadyExists) {
                            $recipients[] = [
                                'id'    => null,
                                'name'  => null,
                                'email' => $email,
                                'type'  => 'custom'
                            ];
                        }
                    }
                }

                if (empty($recipients)) {
                    throw ApiException::badRequest('No valid email addresses found among the selected recipients.');
                }

                // raw template (placeholder সহ) DB-তে save হচ্ছে
                $emailSend = EmailSend::create($data);

                $successCount = 0;
                $failedRecipients = [];

                // 3. Queue emails — personalized subject/body per recipient
                foreach ($recipients as $index => $recipient) {
                    try {
                        $personalizedSubject = $this->renderTemplate($data['subject'], $recipient['name']);
                        $personalizedBody    = $this->renderTemplate($data['body'], $recipient['name']);

                        Mail::to($recipient['email'])->later(
                            now()->addSeconds($index * 3),
                            new SendEmail($personalizedSubject, $personalizedBody)
                        );

                        $successCount++;

                     
                    } catch (\Exception $e) {
                        $failedRecipients[] = [
                            'customer_id' => $recipient['id'],
                            'email'       => $recipient['email'],
                            'reason'      => $e->getMessage(),
                        ];

                        Log::error('Failed to queue email for recipient', [
                            'email_send_id' => $emailSend->id,
                            'company_id'    => $emailSend->company_id,
                            'recipient'     => $recipient['email'],
                            'customer_id'   => $recipient['id'],
                            'error'         => $e->getMessage(),
                        ]);
                    }
                }

                if (!empty($failedRecipients)) {
                    Log::warning('Email batch completed with some failures', [
                        'email_send_id'     => $emailSend->id,
                        'company_id'        => $emailSend->company_id,
                        'total_recipients'  => count($recipients),
                        'success_count'     => $successCount,
                        'failed_count'      => count($failedRecipients),
                        'failed_recipients' => $failedRecipients,
                    ]);
                } else {
                    Log::info('Email batch queued successfully — all recipients', [
                        'email_send_id'    => $emailSend->id,
                        'company_id'       => $emailSend->company_id,
                        'total_recipients' => count($recipients),
                        'success_count'    => $successCount,
                    ]);
                }

                LogHelper::created(
                    'email_send',
                    $emailSend->id,
                    $emailSend->company_id,
                    "Email queued: {$successCount}/" . count($recipients) . " recipients succeeded"
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

    private function renderTemplate(string $template, ?string $customerName): string
    {
        return str_replace(
            '{{customer_name}}',
            $customerName ?? 'Customer',
            $template
        );
    }

    public function getEmailLogById(int $id): EmailSend
    {
        $log = EmailSend::find($id);
        if (!$log) throw ApiException::notFound('Email record');

        $allIds = array_merge($log->customer_ids ?? [], $log->supplier_ids ?? []);
        $parties = Party::whereIn('id', $allIds)->select('id', 'name', 'type')->get();

        $log->customers = $parties->where('type', Party::TYPE_CUSTOMER)->values();
        $log->suppliers = $parties->where('type', Party::TYPE_SUPPLIER)->values();

        return $log;
    }
}
