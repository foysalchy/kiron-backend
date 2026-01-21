<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function payRoll(): BelongsTo
    {
        return $this->belongsTo(PayRoll::class);
    }
    // public function supervisor(): BelongsTo
    // {
    //     return $this->belongsTo(Employee::class, 'supervisor_id');
    // }
}
