<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationAssignment extends Model
{
    protected $fillable = ['conversation_state_id', 'user_id', 'assigned_at'];
}
