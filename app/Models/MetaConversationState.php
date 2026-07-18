<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaConversationState extends Model
{
    protected $fillable = ['thread_id', 'channel_id', 'is_read', 'last_read_at'];

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'conversation_assignments', 'conversation_state_id', 'user_id')
            ->withPivot('assigned_at');
    }
}
