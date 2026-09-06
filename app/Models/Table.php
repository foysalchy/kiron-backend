<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'table_number',
        'capacity',
        'location',
        'is_active',
    ];

    protected $hidden = ['deleted_at'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    
  
    
    //company scope
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public static function clearHomepageCache($companyId)
    {
        if ($companyId) {
            \Illuminate\Support\Facades\Cache::forget("home_tables_{$companyId}");
        }
    }

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_table')->withTimestamps();
    }
}

