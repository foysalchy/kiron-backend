<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ExtraCategory extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'mini_category_id',
        'sub_category_id',
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

    public function miniCategory(): BelongsTo
    {
        return $this->belongsTo(MiniCategory::class);
    }
       public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }
    public function megaCategory(): BelongsTo
    {
        return $this->belongsTo(MegaCategory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByMiniCategory($query, int $miniId)
    {
        return $query->where('mini_category_id', $miniId);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
