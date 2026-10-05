<?php
namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes, CompanyScoped;

    const STATUS_ABSENT    = 0;
    const STATUS_PRESENT   = 1;
    const STATUS_WEEKEND   = 2;
    const STATUS_LATE      = 3;
    const STATUS_EARLY_OUT = 4;
    const STATUS_HOLIDAY   = 5;
    const STATUS_LEAVE     = 6;
    protected $fillable    = [
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
        'is_early_out',
    ];
    protected $casts = [
        'date'         => 'date',
        'in_time'      => 'string',
        'out_time'     => 'string',
        'grace_time'   => 'integer',
        'status'       => 'integer',
        'is_late'      => 'boolean',
        'is_early_out' => 'boolean',
    ];
    protected $appends = [
        'status_text',
    ];
    protected $hidden = ['deleted_at'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employee(): BelongsTo
    {
        return $this->belongsTo(related: Employee::class);
    }
    //company scope
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    public function scopeByStatus($query, int $status)
    {
        return $query->where('status', $status);
    }
    public function scopePresent($query)
    {
        return $query->where('status', self::STATUS_PRESENT);
    }
    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }
    //Accessors for status text
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_WEEKEND => 'Weekend',
            self::STATUS_LATE => 'Late',
            self::STATUS_EARLY_OUT => 'Early Out',
            self::STATUS_HOLIDAY => 'Holiday',
            self::STATUS_LEAVE => 'Leave',
            default => 'Unknown',
        };
    }
    // Helper Methods
    public function isPresent(): bool
    {
        return $this->status === self::STATUS_PRESENT;
    }

    public function isAbsent(): bool
    {
        return $this->status === self::STATUS_ABSENT;
    }

}
