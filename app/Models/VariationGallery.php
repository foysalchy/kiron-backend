<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
