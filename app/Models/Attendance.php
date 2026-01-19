<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'employee_id',
        'date',
        'in_time',
        'out_time',
        'grace_time',
        'late_time',
        'over_time',
        'working_hours',
        'status',
        'is_late',
        'is_early_out'
    ];
    //company scope
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
    public function employee(): BelongsTo
    {
        return $this->belongsTo(related: Employee::class);
    }
}
