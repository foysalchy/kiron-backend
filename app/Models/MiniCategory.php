<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MiniCategory extends Model
{
    use  SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'mega_category_id',
        'sub_category_id',
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
    ];
    protected $hidden = ['deleted_at'];



    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function megaCategory(): BelongsTo
    {
        return $this->belongsTo(MegaCategory::class);
    }

    public function extraCategories(): HasMany
    {
        return $this->hasMany(ExtraCategory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeBySubCategory($query, int $subId)
    {
        return $query->where('sub_category_id', $subId);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
