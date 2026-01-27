<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Period extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'period_type_id',
        'period_name',
        'start_date',
        'end_date',
        'issue_date',
        'status',
        'company_id',
    ];
    protected $hidden = ['deleted_at'];
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
    public function scopeInactive($query)
    {
        return $query->where('status', false);
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
    public function periodType(): BelongsTo
    {
        return $this->belongsTo(PeriodType::class);
    }
     public function payroll(): HasMany
    {
        return $this->hasMany(PayRoll::class);
    }
}
