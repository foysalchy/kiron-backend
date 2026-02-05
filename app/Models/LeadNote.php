<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadNote extends Model
{
    use SoftDeletes,CompanyScoped;
        protected $fillable = [
        'company_id',
        'lead_id',
        'user_id',
        'note',
    ];
    protected $hidden = ['deleted_at'];
    /**
     * Scopes
     */

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
