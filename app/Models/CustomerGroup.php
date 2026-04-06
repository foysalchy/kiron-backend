<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerGroup extends Model
{
    use HasFactory, SoftDeletes,CompanyScoped;

   protected $fillable = [
        'company_id',
        'name',
        'filter_type',
        'filter_parameters', 
        'customer_ids',
        'status',
    ];

    protected $casts = [
        'customer_ids' => 'array',
        'filter_parameters' => 'array',
    ];
}
