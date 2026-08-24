<?php

namespace App\Services;

use App\Models\EmailSetting;
use Illuminate\Support\Facades\Config;

class TenantMailConfigurator
{
    public static function apply(): void
    {
        $settings = EmailSetting::first(); // web request context, global scope
        self::override($settings);
    }

    public static function applyForCompany(int $companyId): void
    {
        $settings = EmailSetting::where('company_id', $companyId)->first();
        self::override($settings);
    }

    protected static function override(?EmailSetting $settings): void
    {
        if (!$settings || !$settings->is_verified) {
            return; // .env default e fall back
        }

        Config::set('mail.mailers.smtp', [
            'transport'  => 'smtp',
            'host'       => $settings->mail_host,
            'port'       => $settings->mail_port,
            'encryption' => $settings->mail_encryption,
            'username'   => $settings->mail_username,
            'password'   => $settings->mail_password,
        ]);

        Config::set('mail.from', [
            'address' => $settings->mail_from_address,
            'name'    => $settings->mail_from_name,
        ]);
    }
}
