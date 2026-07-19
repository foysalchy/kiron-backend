<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use CompanyScoped;

    protected $fillable = [
        'channel_group_id',
        'name',
        'type',
        'slug',
        'page_id',
        'profile_image',
        'page_token',
        'color'
    ];



    public function group()
    {
        return $this->belongsTo(ChannelGroup::class, 'channel_group_id');
    }
    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'channel_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
