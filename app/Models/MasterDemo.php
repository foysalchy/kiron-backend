<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\HasSaasCache;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MasterDemo extends Model
{
    use HasFactory, SoftDeletes,HasSaasCache;

    protected $fillable = [
        'title',
        'image',
        'link',
        'slug',
        'type',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'description',
    ];

    protected $hidden = ['deleted_at'];

    protected $appends = ['image_url'];

    protected $casts = [
        'status' => Status::class,
    ];
    public static function saasCacheKeys(): array
    {
        return ['saas_home_demos'];
    }
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'assets/') || str_starts_with($this->image, '/assets/') || str_starts_with($this->image, 'images/')) {
            return asset($this->image);
        }

        return Storage::disk('r2')->url($this->image);
    }
}
