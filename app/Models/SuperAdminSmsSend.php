<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperAdminSmsSend extends Model
{
    protected $fillable = [
        'message',
        'company_ids',
        'custom_numbers',
        'total_recipients',
    ];
 
    protected $casts = [
        'company_ids'    => 'array',
        'custom_numbers' => 'array',
    ];
}
