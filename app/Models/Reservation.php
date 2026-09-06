<?php

namespace App\Models;

use App\Traits\CompanyScoped;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes, CompanyScoped;

    protected $fillable = [
        'company_id',
        'table_id',
        'guest_name',
        'guest_phone',
        'guest_count',
        'reservation_date',
        'start_time',
        'end_time',
        'status',
        'confirmation_token',
        'notes',
    ];

    protected $hidden = ['deleted_at'];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    //company scope
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

    public function tables()
    {
        return $this->belongsToMany(Table::class, 'reservation_table')->withTimestamps();
    }
}

