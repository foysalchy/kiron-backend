<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class QuikReply extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'shortcut',
        'text',
    ];
}
