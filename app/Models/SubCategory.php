<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubCategory extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'mega_category_id',
        'name',
        'slug',
        'image',
        'status',
    ];



    protected $hidden = ['deleted_at'];


    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function megaCategory(): BelongsTo
    {
        return $this->belongsTo(MegaCategory::class);
    }

    public function miniCategories(): HasMany
    {
        return $this->hasMany(MiniCategory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByMegaCategory($query, int $megaId)
    {
        return $query->where('mega_category_id', $megaId);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
