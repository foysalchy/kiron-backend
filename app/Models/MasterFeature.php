<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MasterFeature extends Model
{
    use SoftDeletes;
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

    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? Storage::disk('r2')->url($this->image)
            : null;
    }
}
