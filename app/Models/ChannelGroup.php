<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class ChannelGroup extends Model
{
    use CompanyScoped;
    protected $fillable = ['platform', 'profile_name', 'profile_image', 'personal_token'];

    public function channels()
    {
        return $this->hasMany(Channel::class);
    }
}
