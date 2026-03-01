<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetCategory extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'status'
    ];
    protected $hidden = ['deleted_at'];

     //company scope
    public function scopeInactive($query)
    {
        return $query->where('status', Status::Inactive->value);
    }    public function scopeActive($query)
    {
        return $query->where('status', Status::Active->value);
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function asset(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

}
