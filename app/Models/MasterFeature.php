<?php

namespace App\Models;

use App\Traits\HasSaasCache;
use App\Traits\HasSlugCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MasterFeature extends Model
{
    use SoftDeletes, HasSaasCache, HasSlugCache;
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'icon',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'description',
        'placement',
        'status',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    public static function saasCacheKeys(): array
    {
        return ['saas_home_top_features', 'saas_home_why_choose_us'];
    }
    public static function clearPaginatedCache()
    {
        self::clearPaginatedSaasCache('saas_features');
    }
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? Storage::disk('r2')->url($this->image)
            : null;
    }
}
