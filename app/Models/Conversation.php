<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use CompanyScoped;
    protected $fillable = ['company_id', 'channel_id', 'customer_id', 'assigned_user_id', 'status', 'last_activity_at'];
    protected $casts = ['last_activity_at' => 'datetime'];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
    public function customer()
    {
        return $this->belongsTo(Party::class);
    }
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
