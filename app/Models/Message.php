<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'from', 'type', 'text', 'file_path', 'file_name', 'agent_id', 'is_read'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
