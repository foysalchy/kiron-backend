<?php

namespace App\Services\Production;

use App\Models\WorkCenter;
use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class WorkCenterService
{
    public function getAll(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = WorkCenter::with(['responsiblePerson']);

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('machine_name', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'name';
            $sortOrder = $filters['sort_order'] ?? 'asc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching work centers: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch work centers');
        }
    }

    public function getById(int $id): WorkCenter
    {
        $center = WorkCenter::with(['responsiblePerson', 'stages'])->find($id);
        if (!$center) {
            throw ApiException::notFound('Work Center');
        }
        return $center;
    }

    public function create(array $data): WorkCenter
    {
        try {
            return WorkCenter::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating work center: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create work center');
        }
    }

    public function update(int $id, array $data): WorkCenter
    {
        $center = $this->getById($id);
        try {
            $center->update($data);
            return $center->fresh(['responsiblePerson', 'stages']);
        } catch (\Exception $e) {
            Log::error('Error updating work center: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update work center');
        }
    }

    public function delete(int $id): void
    {
        $center = $this->getById($id);
        $center->delete();
    }
}
