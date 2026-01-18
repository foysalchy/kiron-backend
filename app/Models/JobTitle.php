<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobTitle extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'status',
    ];
    protected $hidden = ['deleted_at'];

    // Relationships
    /**
     * Get the company that owns the job title.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    // Scopes
    /**
     * Scope a query to only include active job titles.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include job titles of a specific company.
     */
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
