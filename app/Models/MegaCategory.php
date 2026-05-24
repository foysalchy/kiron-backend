<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MegaCategory extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'image',
        'description',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];

    protected $casts = [
        'id'         => 'integer',
        'meta_keywords' => 'array',
        'status' => 'integer',
    ];
    protected $hidden = ['deleted_at'];



    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(SubCategory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
