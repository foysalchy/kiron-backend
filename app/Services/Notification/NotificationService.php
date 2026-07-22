<?php

namespace App\Services\Notification;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;

class NotificationService
{
    public static function notify(Collection|array $recipients, Notification $notification): void
    {
        $recipients = collect($recipients)->filter()->unique('id');

        if ($recipients->isEmpty()) {
            return;
        }

        \Illuminate\Support\Facades\Notification::send($recipients, $notification);
    }
}
