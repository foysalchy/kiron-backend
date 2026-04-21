<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use App\Traits\HasPackageLimits;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use CompanyScoped, HasPackageLimits;
    public string $limitKey = 'domain';

    protected $fillable = [
        'company_id',
        'domain',
        'status',
    ];
}
