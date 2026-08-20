<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VariationGallery extends Model
{
    protected $fillable = [
        'variation_id',
        'image',
    ];



    public function variations(): BelongsTo
    {
        return $this->belongsTo(ProductVariation::class);
    }

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('r2')->url($this->image);
    }
}
