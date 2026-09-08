<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasGlobalLayoutCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FooterCode extends Model
{
    use CompanyScoped, HasGlobalLayoutCache;

    protected $fillable = [
        'company_id',
        'code',
        'status',
    ];
    /**
     * Scopes
     */

    public static function globalLayoutSections(): array
    {
        return ['footer_codes'];
    }
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
