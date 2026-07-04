<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderLog extends Model
{
    protected $fillable = ['company_subscription_id', 'reminder_setting_id', 'sent_at'];

    protected $casts = ['sent_at' => 'datetime'];

    public function subscription()
    {
        return $this->belongsTo(CompanySubscription::class, 'company_subscription_id');
    }

    public function reminderSetting()
    {
        return $this->belongsTo(ReminderSetting::class);
    }
}