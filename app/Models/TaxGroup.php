<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxGroup extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'tax_rate_ids',
        'name',
        'total_rate',
        'status',
    ];
    protected $casts = [
        'tax_rate_ids' => 'array',
        'total_rate' => 'float',
    ];
    protected $hidden = ['deleted_at'];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function taxRates(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }
}
