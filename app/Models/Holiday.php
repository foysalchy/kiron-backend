<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'number_of_days',
        'from_date',
        'to_date',
        'theme_color',
        'status'
    ];
    protected $hidden = ['deleted_at'];
    /**
     * Casts for data types
     */
    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
        'number_of_days' => 'integer',
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
