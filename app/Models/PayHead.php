<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayHead extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'type',// addition / deduction
        'description',
        'status',
    ];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'status' => 'integer',
    ];
    const TYPE_ADDITION = 'addition';
    const TYPE_DEDUCTION = 'deduction';
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

}
