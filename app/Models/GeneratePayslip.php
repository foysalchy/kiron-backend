<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneratePayslip extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'employee_id',
        'pay_roll_id',
        'pay_slip_id',
        'pay_roll_pay_head_id',
        'period_id',
        'generated_date',
        'gross_salary',
        'total_deduction',
        'net_salary',
        'status',
    ];
    protected $casts = [
        'gross_salary'    => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'net_salary'      => 'decimal:2',
        'generated_date'  => 'date',
    ];
    // Scopes
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }
    public function scopePaid($query)
    {
        return $query->where('status', 9);
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function paySlipManager(): BelongsTo
    {
        return $this->belongsTo(PaySlipManager::class);
    }
    public function payRollPayHeads(): HasMany
    {
        return $this->hasMany(PayRollPayHead::class, 'pay_roll_id', 'pay_roll_id');
    }
    public function period(): BelongsTo {
        return $this->belongsTo(Period::class);
    }

    public function payRoll(): BelongsTo {
        return $this->belongsTo(PayRoll::class);
    }

}
