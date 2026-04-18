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
  public function getAllPermissions(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
{
    try {
        $query = Permission::query();

        // ✅ Get company features & resolve to permission keys
        $featureKeys = [];
        if (auth()->check()) {
            $company = auth()->user()->company;
            if ($company && $company->pricingPackage) {
                $featureLabels = $company->pricingPackage->features ?? [];
                $featureKeys   = FeatureMap::resolve($featureLabels);
            }
        }

        // ✅ Only return permissions matching the package features
        if (!empty($featureKeys)) {
            $query->whereIn('feature_dependency', $featureKeys);
        } else {
            return $paginate ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15) : collect();
        }

        // 🔍 Search
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('group_name', 'like', "%{$filters['search']}%");
            });
        }

        // 📦 Grouped (for React checkbox UI)
        if (!empty($filters['grouped'])) {
            return $query->get()->groupBy('group_name');
        }

        // 🔃 Sorting
        $sortBy    = $filters['sort_by']    ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $paginate
            ? $query->paginate($filters['per_page'] ?? 15)
            : $query->get();

    } catch (\Exception $e) {
        Log::error('Error fetching permissions: ' . $e->getMessage());
        throw ApiException::serverError('Failed to fetch permissions');
    }
}
    // Standard create, update, delete methods follow the exact same pattern as BrandService...
}
