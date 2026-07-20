<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\HasGlobalLayoutCache;
use App\Traits\HasSlugCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use SoftDeletes, CompanyScoped, HasSlugCache, HasGlobalLayoutCache;


    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'description',
        'image',
        'status',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];
    protected $casts = [
        'id'         => 'integer',
        'meta_keywords' => 'array',
    ];
    public static function globalLayoutSections(): array
    {
        return ['footer_pages'];
    }
    protected $hidden = ['deleted_at'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    // Accessors
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
