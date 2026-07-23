<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use App\Traits\HasHomepageCache;
use App\Traits\HasSaasCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KnowledgeBase extends Model
{
    use SoftDeletes, CompanyScoped, HasHomepageCache, HasSaasCache;

    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'category',
        'content',
        'status',
    ];

    protected $hidden = ['deleted_at'];
    public static function homepageCacheKeys(): array
    {
        return ['home_faqs', 'support_categories', 'support_categories'];
    }
    public static function saasCacheKeys(): array
    {
        return ['saas_home_faqs', 'saas_faqs_list'];
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
