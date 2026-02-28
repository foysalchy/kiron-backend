<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use SoftDeletes,CompanyScoped;
    protected $fillable = [
        'company_id',
        'pay_roll_id',
        'name',
        'type',
        'head_count',
        'supervisor_id',
        'status',
    ];
    protected $hidden = ['deleted_at'];
    protected $appends = ['head_count_left'];
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

    public function payRoll(): BelongsTo
    {
        return $this->belongsTo(PayRoll::class);
    }
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }
   public function getHeadCountLeftAttribute(): int
    {
        return (int)($this->head_count - ($this->employees_count ?? 0));
    }
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'position_id');
    }
    public function paySlipManagers(): HasMany
    {
        return $this->hasMany(PaySlipManager::class);
    }
   
}
