<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'lead_source_id',
        'lead_status_id',
        'full_name',
        'email',
        'phone',
        'division',
        'district',
        'thana',
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
    public function leadSource(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class);
    }
    public function leadStatus(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class);
    }
    public function leadNote(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }
}
