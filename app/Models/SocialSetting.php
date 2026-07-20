<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\HasGlobalLayoutCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class SocialSetting extends Model
{
    use SoftDeletes, CompanyScoped, HasGlobalLayoutCache;

    protected $fillable = [
        'company_id',
        'icon_name',
        'icon_image',
        'link',
        'hover_bg',
        'icon_class',
        'status',
    ];
    public static function globalLayoutSections(): array
    {
        return ['social_links'];
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function getimageAttribute(): ?string
    {
        return $this->icon_image
            ? Storage::disk('r2')->url($this->icon_image)
            : null;
    }
}
