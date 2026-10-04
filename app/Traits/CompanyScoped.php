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
            }
        });

        // Apply global scope for filtering
  // Apply global scope for filtering
static::addGlobalScope('company', function (Builder $builder) {

    try {
        if (Auth::check()) {
            $user = Auth::user();

            if (self::isSuperAdmin($user)) {
                // Super admin: only fetch records where company_id is null
                $builder->whereNull($builder->getModel()->getTable() . '.company_id');
            } else {
                // Regular user: fetch their company's records
                $builder->where($builder->getModel()->getTable() . '.company_id', $user->company_id);
            }
            return;
        }

        $company = getCurrentCompany();
        if ($company && isset($company->company_id)) {
            $builder->where($builder->getModel()->getTable() . '.company_id', $company->company_id);
        }

    } catch (\Exception $e) {
        // Silent fail — boot time এ error হলে ignore
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
