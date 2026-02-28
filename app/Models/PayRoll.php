<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayRoll extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'name',
        'period_type_id',
        'payment_type',
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
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
    public function paySlipManagers(): HasMany
    {
        return $this->hasMany(PaySlipManager::class);
    }

    public function periods(): BelongsToMany
    {
        return $this->belongsToMany(Period::class, 'payroll_periods');
    }
    public function periodType(): BelongsTo
    {
        return $this->belongsTo(PeriodType::class)->select('id', 'type');
    }

    public function payRollPayHeads(): HasMany
    {
        return $this->hasMany(PayRollPayHead::class, 'pay_roll_id');
    }
}
