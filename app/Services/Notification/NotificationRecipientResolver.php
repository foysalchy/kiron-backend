<?php

// app/Services/Notification/NotificationRecipientResolver.php
namespace App\Services\Notification;

use App\Models\User;
use Illuminate\Support\Collection;

class NotificationRecipientResolver
{
    public static function saasSuperAdmins(): Collection
    {
        return User::whereNull('company_id')
            ->where('is_super_admin', 1)
            ->get();
    }

    public static function companySuperAdmin(?int $companyId): Collection
    {
        if (is_null($companyId)) {
            return collect();
        }

        return User::where('company_id', $companyId)
            ->whereNull('role')
            ->get();
    }

    public static function companyUser(int $userId): ?User
    {
        return User::find($userId);
    }


    public static function companySuperAdminWithUser(int $companyId, int $userId): Collection
    {
        $recipients = self::companySuperAdmin($companyId);

        $user = self::companyUser($userId);
        if ($user && !$recipients->contains('id', $user->id)) {
            $recipients->push($user);
        }

        return $recipients;
    }
}
