<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartyActivity extends Model
{

    protected $fillable = [
        'party_id',
        'agent_id',
        'type',
        'label',
        'meta',
    ];
}
