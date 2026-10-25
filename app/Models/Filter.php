<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasCachedOptions;
use App\Traits\HasHomepageCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Filter extends Model
{
    use SoftDeletes, CompanyScoped, HasCachedOptions,HasHomepageCache;

    protected $fillable = [
        'company_id',
        'name',
        'mega_category_ids',
        'status',
    ];

    protected $casts = [
        'mega_category_ids' => 'array',
        'status' => 'integer',
    ];

    protected $hidden = [
        'deleted_at',
    ];
public static function homepageCacheKeys(): array
    {
        return ['shop_filters'];
    }
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(FilterValue::class, 'filter_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }
}
