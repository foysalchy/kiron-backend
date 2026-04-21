<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySubscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'pricing_package_id',
        'billing_cycle',
        'amount_paid',
        'currency',
        'payment_method',
        'payment_status',
        'transaction_id',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
        'amount_paid'   => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }


    public function pricingPackage()
    {
        return $this->belongsTo(PricingPackage::class);
    }

    public function isActive(): bool
    {
        return $this->status === Status::Active->value && $this->ends_at?->isFuture();
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }
}
