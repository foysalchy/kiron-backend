<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayRollPayHead extends Model
{
    use SoftDeletes,CompanyScoped;
    protected $fillable = [
        'company_id',
        'pay_roll_id',
        'pay_head_id',
        'type',
        'amount',
    ];
    protected $hidden = ['deleted_at'];
        // Scopes
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
        // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function payRoll(): BelongsTo
    {
        return $this->belongsTo(PayRoll::class);
    }
    public function payHead(): BelongsTo
    {
        return $this->belongsTo(PayHead::class);
    }
}
