<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailVerification extends Model
{
    protected $fillable = [
        'company_id',
        'type',
        'email',
        'otp',
        'verified_at',
        'expires_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}