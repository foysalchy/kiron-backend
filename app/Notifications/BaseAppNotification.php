<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

abstract class BaseAppNotification extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['database'];
        // future: return ['database', 'broadcast'];
    }

    abstract public function toArray($notifiable): array;
}
