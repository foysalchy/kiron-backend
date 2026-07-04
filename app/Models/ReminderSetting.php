<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderSetting extends Model
{
    protected $fillable = ['type', 'days_before', 'status'];

    protected $casts = [
        'status'      => 'boolean',
        'days_before' => 'integer',
    ];

    public function logs()
    {
        return $this->hasMany(ReminderLog::class);
    }
}