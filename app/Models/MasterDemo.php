<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MasterDemo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'image',
        'link',
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

    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? Storage::disk('r2')->url($this->image)
            : null;
    }
}
