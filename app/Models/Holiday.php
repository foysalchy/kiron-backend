<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
    use SoftDeletes,CompanyScoped;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'number_of_days',
        'theme_color',
        'status'
    ];
    protected $hidden = ['deleted_at'];
}
