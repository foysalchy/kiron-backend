<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\EmailSetting;
use Illuminate\Support\Facades\{DB,Log};

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
     * Save or Update email configuration
     */
    public function saveEmailSettings(array $data): EmailSetting
    {
        DB::beginTransaction();
        try{
            $emailSetting = EmailSetting::updateOrCreate(
                [],
                [
                    'host_name'     => $data['host_name'],
                    'port_number'   => $data['port_number'],
                    'auth_user'     => $data['auth_user'],     // SMTP Username
                    'auth_password' => $data['auth_password'], // SMTP Password
                    'status'        => $data['status'], 
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
