<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;

class CompanyMonthlyUsage extends Model
{
    use CompanyScoped;
    protected $fillable = ['company_id', 'year_month', 'order_count'];
}
