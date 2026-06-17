<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdgradePackageRequest extends Model
{
    use HasFactory,CompanyScoped;
    protected $table = 'updgrade_package_requests'; 
    protected $fillable = [
        'company_id',
        'pricing_package_id',
        'billing_cycle',
        'account_holder_name',
        'payment_method',
        'number',
        'transaction_id',
        'document_path',
        'amount_paid',
        'request_date',
        'status',
    ];



    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function pricingPackage(): BelongsTo
    {
        return $this->belongsTo(PricingPackage::class, 'pricing_package_id');
    }
}