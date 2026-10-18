<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasHomepageCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Notice extends Model
{
    use SoftDeletes, CompanyScoped,HasHomepageCache;


    public static function homepageCacheKeys(): array
    {
        return ['home_notices'];
    }

    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'description',
        'image',
        'status',
    ];

    protected $hidden = ['deleted_at'];
    protected $appends = ['image_url'];

    // Accessors
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? Storage::disk('r2')->url($this->image)
            : null;
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
