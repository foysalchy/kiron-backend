<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuperAdminSmsSend extends Model
{
    protected $fillable = [
        'message',
        'company_ids',
        'custom_numbers',
        'total_recipients',
    ];

    protected $casts = [
        'company_ids'    => 'array',
        'custom_numbers' => 'array',
    ];
    public function company(): BelongsTo    
    {
        return $this->belongsTo(Company::class);
    }
}
