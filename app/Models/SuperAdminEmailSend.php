<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperAdminEmailSend extends Model
{
     protected $fillable = [
        'subject',
        'body',
        'company_ids',
        'custom_emails',
        'total_recipients',
    ];
 
    protected $casts = [
        'company_ids'   => 'array',
        'custom_emails' => 'array',
    ];
}
