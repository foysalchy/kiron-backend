<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\EmailSetting;
use Illuminate\Support\Facades\{DB, Log};

class EmailSettingService
{
    public function getEmailSettings(): ?EmailSetting
    {
        try {
            return EmailSetting::first();
        } catch (\Exception $e) {
            Log::error('Error fetching email settings: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch email settings');
        }
    }

    /**
     * Save or Update email configuration for the current company
     */
    public function saveEmailSettings(array $data): EmailSetting
    {
        DB::beginTransaction();
        try {
            $emailSetting = EmailSetting::updateOrCreate(
                [],
                [
                    'mail_mailer'       => $data['mail_mailer'] ?? 'smtp',
                    'mail_host'         => $data['mail_host'],
                    'mail_port'         => $data['mail_port'],
                    'mail_username'     => $data['mail_username'],
                    'mail_password'     => $data['mail_password'], // encrypted cast
                    'mail_encryption'   => $data['mail_encryption'] ?? null,
                    'mail_from_address' => $data['mail_from_address'],
                    'mail_from_name'    => $data['mail_from_name'],
                    'is_verified'       => $data['is_verified'] ?? false,
                ]
            );

            $action = $emailSetting->wasRecentlyCreated ? 'created' : 'updated';
            LogHelper::$action('email_setting', $emailSetting->id, $emailSetting->company_id, 'SMTP Configuration ' . $action);

            Log::info("Email settings {$action} successfully for company ID: " . $emailSetting->company_id);

            DB::commit();
            return $emailSetting->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email settings save failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to save email settings');
        }
    }
}