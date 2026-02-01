<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourierMethod extends Model
{ 
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'details',
        'status'
    ];
    protected $casts = [
        'details' => 'array', 
    ];
    protected $hidden = ['deleted_at'];

    // Scopes
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function couriers(): HasMany
    {
        return $this->hasMany(Courier::class);
    }
}
