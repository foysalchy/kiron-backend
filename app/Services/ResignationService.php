<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Resignation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class ResignationService
{
    /**
     * Get all resignations with optional pagination and filters
     */
    public function getAllResignations(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Resignation::with(['employee:id,first_name']);

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (isset($filters['search'])) {
                $query->whereHas('employee', function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%");
                })->orWhere('reason', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching resignations: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch resignations');
        }
    }
    /**
     * Get Resignation by ID
     */
    public function getResignationById(int $id): Resignation
    {
        $resignation = Resignation::find($id);

        if (!$resignation) {
            throw ApiException::notFound('Resignation');
        }

        return $resignation;
    }
    /**
     * Create a new Resignation
     */
    public function createResignation(array $data): Resignation
    {
        DB::beginTransaction();
        try {
            // Handle Letter Upload (Image or PDF)
            if (isset($data['letter'])) {
                $data['letter'] = FileUploadHelper::uploadImage(
                    $data['letter'],
                    'resignations/letters',
                    'public'
                );
            }

            $resignation = Resignation::create($data);

            LogHelper::created('resignation', $resignation->id, $resignation->company_id,$resignation->type);

            DB::commit();
            Log::info('Resignation created successfully', ['id' => $resignation->id]);

            return $resignation->load(['employee'])->setAppends(['resign_rules']);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['letter'])) {
                FileUploadHelper::delete($data['letter']);
            }
            Log::error('Resignation creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create resignation');
        }
    }

    /**
     * Toggle Status (Active/Inactive)
     */
    public function toggleStatus(int $id): Resignation
    {
        DB::beginTransaction();
        try {
            $resignation = $this->getResignationById($id);
            $currentStatus = Status::from($resignation->status);

            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $resignation->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('resignation', $resignation->id, $resignation->company_id, $resignation->type . ' new status ' . $newStatus->label());
            DB::commit();
            Log::info('Resignation status toggled', ['resignation_id' => $id, 'new_status' => $newStatus->value]);
            return $resignation;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resignation status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
