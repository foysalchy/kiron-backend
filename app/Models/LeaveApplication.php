<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveApplication extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'employee_id',
        'leave_type_id',
        'assign_leave_id',
        'from_date',
        'to_date',
        'is_half_day',
        'duration',
        'reason',
        'documents',
        'status',
    ];
    protected $hidden = ['deleted_at'];

    protected $casts = [
        'documents' => 'array',
        'from_date' => 'date',
        'to_date'   => 'date',
        'duration'  => 'float',
    ];
    //scoped
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }
    public function scopePending($query)
    {
        return $query->where('status', 2);
    }
    public function scopeApproved($query)
    {
        return $query->where('status', 3);
    }
    public function scopeCancelled($query)
    {
        return $query->where('status', 10);
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
        return $this->belongsTo(Employee::class);
    }
    public function leave_type(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
    public function assign_leave(): BelongsTo
    {
        return $this->belongsTo(AssignLeaveType::class, 'assign_leave_id');
    }
    // Accessors
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->documents && is_array($this->documents) && count($this->documents) > 0) {
            return asset('storage/' . $this->documents[0]);
        }
        return null;
    }
    protected $appends = ['balance_summary'];

    public function getBalanceSummaryAttribute()
    {
        $assign = $this->assign_leave;

        $entitled = $assign ? $assign->total_days : 0;
        $available = $assign ? ($assign->total_days - $assign->used_days) : 0;

        return [
            'entitled_days' => (float) $entitled,
            'available_balance' => (float) $available,
            'currently_booked' => (float) $this->duration,
            'balance_after_booked' => (float) ($available - $this->duration),
        ];
    }
}
