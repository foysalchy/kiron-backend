<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'department_id',
        'employee_type_id',
        'job_title_id',
        'office_location_id',
        'first_name',
        'last_name',
        'nick_name',
        'phone',
        'email',
        'gender',
        'dob',
        'image',
        'joining_date',
        'payslip_generation_date',
        'confirmation_date',
        'present_address',
        'permanent_address',
        'is_same_address',
        'in_time',
        'out_time',
        'allow_flexible_time',
        'status',
    ];
    protected $hidden = ['deleted_at'];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
        'payslip_generation_date' => 'date',
        'confirmation_date' => 'date',
        'is_same_address' => 'boolean',
        'allow_flexible_time' => 'boolean',
        'status' => 'integer',
    ];
    // --- Relationships ---

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employeeType(): BelongsTo
    {
        return $this->belongsTo(EmployeeType::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(OfficeLocation::class);
    }
    // --- Scopes ---

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
      // Accessors
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
