<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class QuikReply extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'group_name',
        'type',
        'shortcut',
        'text',
        'image_path',
        'image_paths',
    ];

    protected $casts = [
        'image_paths' => 'array',
    ];
}
