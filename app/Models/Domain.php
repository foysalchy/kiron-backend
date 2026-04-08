<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use CompanyScoped;
    protected $fillable = [
        'company_id',
        'domain',
        'status',
    ];
}
