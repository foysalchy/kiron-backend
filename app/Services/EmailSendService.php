<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Mail\SendEmail;
use App\Models\EmailSend;
use App\Models\Party;
use Illuminate\Support\Facades\{DB,Log, Mail};

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
                    ->pluck('email')
                    ->toArray();

                if (empty($customers)) {
                    throw ApiException::validationError('Selected customers do not have valid email addresses.');
                }

            // foreach ($customers as $recipient) {
            //     // Mail::to($recipient)->send(new SendEmail($data['subject'], $data['body']));
            //     Mail::to($recipient)->queue(new SendEmail($data['subject'], $data['body']));
            // }

            $emailSend = EmailSend::create($data);

            // Use $index to space out the emails for Mailtrap
            foreach ($customers as $index => $recipient) {
                Mail::to($recipient)->later(
                    now()->addSeconds($index * 3), // Spreads emails 3 seconds apart
                    new SendEmail($data['subject'], $data['body'])
                );
            }

            LogHelper::created('email_send', $emailSend->id, $emailSend->company_id, 'Email queued with delay for ' . count($customers) . ' recipients');

                LogHelper::created('email_send', $emailSend->id, $emailSend->company_id, 'Email queued for ' . count($customers) . ' recipients');

                return $emailSend;
            } catch (\Exception $e) {
                Log::error('Email processing failed: ' . $e->getMessage());
                throw ApiException::serverError('Failed to process and send emails: ' . $e->getMessage());
            }
        });
    }

    public function getEmailLogById(int $id): EmailSend
    {
        $log = EmailSend::find($id);
        if (!$log) throw ApiException::notFound('Email record');
        return $log;
    }
}
