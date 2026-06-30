<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class MasterBrand extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'logo',
        'link',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo
            ? Storage::disk('r2')->url($this->logo)
            : null;
    }
}
