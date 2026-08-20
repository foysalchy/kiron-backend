<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsTemplate extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'is_default',
        'slug',
        'status',
    ];
    protected $hidden = ['deleted_at'];
    protected static function booted()
    {
        static::deleting(function ($smsTemplate) {
            if ($smsTemplate->is_default == 1) {
                throw new Exception('Default SMS template cannot be deleted.');
            }
        });
    }

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
