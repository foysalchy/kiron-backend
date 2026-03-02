<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes,CompanyScoped;
    protected $fillable = [
        'company_id',
        'asset_category_id',
        'manager_id',
        'image',
        'name',
        'asset_tag',
        'serial_number',
        'model_number',
        'asset_location',
        'description',
        'status',
    ];
    protected $hidden = ['deleted_at'];

    //company scope
    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }
    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }
    public function scopeDisposed($query)
    {
        return $query->where('status', Status::Disposed->value);
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class,'asset_category_id');
    }
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
    public function assetPurchase(): HasMany
    {
        return $this->hasMany(AssetPurchase::class);
    }
    public function assetDepreciation(): HasMany
    {
        return $this->hasMany(AssetDepreciation::class);
    }
    // Accessors
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
