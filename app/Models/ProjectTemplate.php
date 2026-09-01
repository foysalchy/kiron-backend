<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class ProjectTemplate extends Model
{
    use CompanyScoped;

    protected $guarded = ['id'];

    protected $casts = [
        'structure_json' => 'array',
        'is_default' => 'boolean',
    ];
}
