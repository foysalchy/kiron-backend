<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
        use SoftDeletes,CompanyScoped;


    protected $fillable = [
        'company_id',
        'title',
        'name',
        'short',
        'body',
        'body_2',
        'body_3',
        'images',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
    ];
    protected $casts = [
        'images' => 'array',
    ];


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
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->images && is_array($this->images) && count($this->images) > 0) {
            return asset('storage/' . $this->images[0]);
        }
        return null;
    }
}
