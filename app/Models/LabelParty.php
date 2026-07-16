<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabelParty extends Model
{
    protected $fillable = [
        'party_id',
        'label_id',
    ];
}
