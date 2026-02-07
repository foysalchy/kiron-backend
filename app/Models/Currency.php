<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'symbol',
        'status',
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
}
