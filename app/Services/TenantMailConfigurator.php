<?php

namespace App\Services;

use App\Models\EmailSetting;
use Illuminate\Support\Facades\Config;

class TenantMailConfigurator
{
    public static function apply(): void
    {
        $settings = EmailSetting::first(); // global scope already filters by company

        if (!$settings || !$settings->is_verified) {
            return; // .env default e fall back hobe automatically
        }

        Config::set('mail.mailers.smtp', [
            'transport'  => 'smtp',
            'host'       => $settings->mail_host,
            'port'       => $settings->mail_port,
            'encryption' => $settings->mail_encryption,
            'username'   => $settings->mail_username,
            'password'   => $settings->mail_password, // decrypted automatically via cast
        ]);

        Config::set('mail.from', [
            'address' => $settings->mail_from_address,
            'name'    => $settings->mail_from_name,
        ]);
    }
}