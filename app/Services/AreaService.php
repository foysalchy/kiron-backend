<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Area;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Log;

class AreaService
{
    /**
     * Get all area with optional pagination
     */
    public function getAllAreas(array $filters, bool $paginate = true)
    {
        $query = Area::with(['company','warehouse']);

        if (!empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== "") {
            $query->where('status', (int)$filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
    }

    /**
     * Get area by ID
     */
    public function getAreaById(int $id): Area
    {
        $area = Area::with(['company','warehouse'])->find($id);
        if (!$area) {
            throw ApiException::notFound('area');
        }
        return $area;
    }

    /**
     * Create a new area
     */
    public function createArea(array $data): Area
    {
        try {
            $area =Area::create($data);
            LogHelper::created('area', $area->id, $area->company_id);
            Log::info('Area created successfully', ['area_id' => $area->id]);

            return $area->load(['company','warehouse']);

        } catch (\Exception $e) {
            Log::error('Area creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create area');
        }
    }


    /**
     * Update area
     */


}
