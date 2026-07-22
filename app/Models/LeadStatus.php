<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadStatus extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'name',
        'color_code',
        'is_default',
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
    protected static function booted()
    {
        static::deleting(function ($leadStatus) {
            if ($leadStatus->is_default == 1) {
                throw new Exception('Default status cannot be deleted.');
            }
        });
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
