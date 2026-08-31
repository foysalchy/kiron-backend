<?php

namespace App\Services\Production;

use App\Models\ProductionStage;
use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ProductionStageService
{
    public function getAll(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = ProductionStage::with(['workCenter']);

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['work_center_id'])) {
                $query->where('work_center_id', $filters['work_center_id']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where('name', 'like', "%{$search}%");
            }

            $query->orderBy('sequence', 'asc');

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching production stages: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch production stages');
        }
    }

    public function getById(int $id): ProductionStage
    {
        $stage = ProductionStage::with(['workCenter'])->find($id);
        if (!$stage) {
            throw ApiException::notFound('Production Stage');
        }
        return $stage;
    }

    public function create(array $data): ProductionStage
    {
        try {
            if (empty($data['sequence'])) {
                $maxSeq = ProductionStage::max('sequence') ?? 0;
                $data['sequence'] = $maxSeq + 1;
            }
            return ProductionStage::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating production stage: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create production stage');
        }
    }

    public function update(int $id, array $data): ProductionStage
    {
        $stage = $this->getById($id);
        try {
            $stage->update($data);
            return $stage->fresh(['workCenter']);
        } catch (\Exception $e) {
            Log::error('Error updating production stage: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update production stage');
        }
    }

    public function delete(int $id): void
    {
        $stage = $this->getById($id);
        $stage->delete();
    }

    public function reorder(array $orderList): void
    {
        foreach ($orderList as $item) {
            ProductionStage::where('id', $item['id'])->update(['sequence' => $item['sequence']]);
        }
    }
}
