<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use CompanyScoped;
    protected $fillable = ['company_id', 'name', 'slug', 'type', 'color', 'is_active', 'meta'];
    protected $casts = ['meta' => 'array'];

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
