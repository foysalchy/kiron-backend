<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsSend extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'customer_ids',
        'supplier_ids',
        'custom_numbers',
        'total_recipients',
        'sms_count',
        'rate_per_sms',
        'message',
        'status',
    ];
    protected $casts = [
        'customer_ids' => 'array',
        'supplier_ids' => 'array',
        'custom_numbers' => 'array',
    ];

    protected $hidden = ['deleted_at'];

    //company scope
    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
