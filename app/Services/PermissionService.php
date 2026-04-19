<?php

namespace App\Services;

use App\Constants\FeatureMap;
use App\Models\Permission;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class PermissionService
{
    public function getAllPermissions(array $filters = []): Collection
    {
        try {
            $user  = auth()->user();
            $query = Permission::query();

            if ($user->is_super_admin) {
                // ✅ Super admin — only superadmin type permissions
                $query->where('type', 'superadmin');
            } else {
                // ✅ Company user — only company type + package feature matched
                $featureKeys = [];
                $company = $user->company;
                if ($company && $company->pricingPackage) {
                    $featureKeys = $company->pricingPackage->features ?? [];
                }

                if (empty($featureKeys)) {
                    return collect();
                }
         
                $query->where('type', 'company')
                    ->whereIn('feature_dependency', $featureKeys);
            }

            // 🔍 Search
            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('group_name', 'like', "%{$filters['search']}%");
                });
            }

            // 🔃 Sorting
            $sortBy    = $filters['sort_by']    ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching permissions: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch permissions');
        }
    }
    // Standard create, update, delete methods follow the exact same pattern as BrandService...
}
