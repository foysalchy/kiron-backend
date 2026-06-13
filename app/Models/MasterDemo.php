<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterDemo extends Model
{
     use HasFactory, SoftDeletes;

    protected $fillable = ['title', 'image', 'link', 'status'];

    protected $hidden = ['deleted_at'];

    protected $appends = ['image_url'];

    protected $casts = [
        'status' => Status::class,
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
