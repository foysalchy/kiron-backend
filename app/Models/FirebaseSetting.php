<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FirebaseSetting extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'email',
        'api_key',
        'auth_domain',
        'project_id',
        'storage_bucket',
        'messaging_sender_id',
        'app_id',
        'measurement_id',
        'google_auth',
        'facebook_auth',
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
