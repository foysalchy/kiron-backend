<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterFeature extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'icon',
        'description',
        'placement',
        'status',
    ];

    protected $hidden = ['created_at', 'updated_at','deleted_at'];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
