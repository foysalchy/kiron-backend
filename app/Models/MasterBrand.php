<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBrand extends Model
{
    protected $fillable = [
        'name',
        'logo',
        'link',
        'status',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}
