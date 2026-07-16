<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmNote extends Model
{
    protected $fillable = [
        'party_id',
        'agent_id',
        'text',

    ];
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function party()
    {
        return $this->belongsTo(Party::class);
    }
}
