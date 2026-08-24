<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasSubdomainCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DomainSetup extends Model
{
    use CompanyScoped, HasSubdomainCache;

    protected $fillable = [
        'company_id',
        'custom_domain',
        'sub_domain',
        'template_name',
        'product_card_template',
        'is_review',
        'status',
        'prefix'
    ];
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
