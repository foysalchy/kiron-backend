<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courier extends Model
{
    use SoftDeletes, CompanyScoped;
    protected $fillable = [
        'company_id',
        'name',
        'method_details',
        'contact_name',
        'phone',
        'location',
        'status'
    ];
    protected $casts = [
        'method_details' => 'array',
    ];
    protected $hidden = ['deleted_at'];
    // app/Models/Courier.php

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }
    // Scopes
    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }
    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function method(): BelongsTo
    {
        return $this->belongsTo(CourierMethod::class, 'courier_method_id');
    }
}
