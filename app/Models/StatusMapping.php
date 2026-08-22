<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'kiron_status',
        'mappings',
    ];

    protected $casts = [
        'mappings' => 'array',
    ];
}
