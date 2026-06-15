<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperAdminEmailSend extends Model
{
    protected $fillable = [
        'subject',
        'body',
        'company_ids',
        'custom_emails',
        'total_recipients',
    ];

    protected $casts = [
        'company_ids'   => 'array',
        'custom_emails' => 'array',
    ];
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
