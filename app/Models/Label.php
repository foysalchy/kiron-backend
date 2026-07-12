<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'name',
        'color',
        'status',
    ];
}
