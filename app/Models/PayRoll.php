<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayRoll extends Model
{
    use SoftDeletes,CompanyScoped;
    protected $fillable = [
        'company_id',
        'name',
        'payroll_type',
        'payment_type',
        'status',
    ];
    protected $hidden = ['deleted_at'];
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'pay_roll_id');
    }

}
