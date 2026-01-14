<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait CompanyScoped
{
    /**
     * Boot the trait
     */
    protected static function bootCompanyScoped()
    {
        // Auto-assign company_id when creating
        static::creating(function ($model) {
            if (Auth::check()) {
                $user = Auth::user();

                // If company_id is NOT already set
                if (!$model->company_id) {
                    // Super admin can create for any company, so don't auto-assign
                    if (!self::isSuperAdmin($user)) {
                        // Regular user - auto-assign their company_id
                        $model->company_id = $user->company_id;
                    }
                }
                // If company_id IS already set (super admin specified it), leave it as is
            }
        });

        // Apply global scope for filtering
        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

                // Super admin can see all data
                if (!self::isSuperAdmin($user)) {
                    $builder->where('company_id', $user->company_id);
                }
            }
        });
    }

    /**
     * Check if user is super admin
     */
    protected static function isSuperAdmin($user): bool
    {
        // Option 1: Check role column
        return $user->role === 'super_admin';

        // Option 2: Check company_id = 0
        // return $user->company_id === 0;

        // Option 3: Check is_super_admin flag
        // return $user->is_super_admin === true;
    }

    /**
     * Scope to get all data (bypass company filter)
     * Usage: Model::withoutCompanyScope()->get()
     */
    public function scopeWithoutCompanyScope(Builder $query)
    {
        return $query->withoutGlobalScope('company');
    }

    /**
     * Scope to filter by specific company
     * Usage: Model::forCompany($companyId)->get()
     */
    public function scopeForCompany(Builder $query, int $companyId)
    {
        return $query->withoutGlobalScope('company')
            ->where('company_id', $companyId);
    }
}
