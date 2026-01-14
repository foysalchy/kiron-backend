<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'alternative_phone',
        'logo',
        'address',
        'business_type',
        'status',
    ];



    protected $hidden = [
        'deleted_at',
    ];
    public function parties()
    {
        return $this->hasMany(Party::class);
    }
    protected static function booted()
    {
        static::deleting(function ($company) {

            // soft delete
            if (! $company->isForceDeleting()) {
                $company->parties()->delete();
            }

            // force delete
            if ($company->isForceDeleting()) {
                $company->parties()->forceDelete();
            }
        });

        static::restoring(function ($company) {
            $company->parties()->withTrashed()->restore();
        });
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function scopeByBusinessType($query, string $type)
    {
        return $query->where('business_type', $type);
    }

    // Accessors
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }
}
