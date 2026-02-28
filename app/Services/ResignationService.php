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
        $record = Resignation::with(['employee'])->find($id);

        if (!$record) {
            throw ApiException::notFound('Resignation Record');
        }

        // force accessor to appear in response
        $record->resign_rules;

        return $record;
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

            LogHelper::created('resignation', $resignation->id, $resignation->company_id, $resignation->type);

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
     * Update Resignation (Only allowed if status is Pending)
     */
    public function updateResignation(int $id, array $data): Resignation
    {
        DB::beginTransaction();

        try {
            $resignation = $this->getResignationById($id);

            // 1. Strict Validation: Cannot update if already Approved or Rejected
            if ($resignation->status != Status::Pending->value) {
                throw ApiException::badRequest('Only pending resignation/termination records can be updated.');
            }

            // 2. File Upload Handling (Replace old file if new one is provided)
            if (isset($data['letter']) && $data['letter'] instanceof \Illuminate\Http\UploadedFile) {
                if ($resignation->letter) {
                    FileUploadHelper::delete($resignation->letter);
                }
                $data['letter'] = FileUploadHelper::uploadImage($data['letter'], 'resignations', 'public', 2048);
            }

            // 3. Update core data
            $resignation->update($data);



            LogHelper::updated('resignation', $resignation->id, $resignation->company_id, "Resignation/Termination record updated.");

            DB::commit();
            return $resignation;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resignation update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update resignation record.');
        }
    }
    /**
     * Update Status (Pending / Approved / Rejected)
     */
    public function updateStatus(int $id, int $newStatusValue): Resignation
    {
        DB::beginTransaction();
        try {
            $resignation = $this->getResignationById($id);

            $resignation->update(['status' => $newStatusValue]);

            LogHelper::statusChanged('resignation', $resignation->id, $resignation->company_id, "Status changed to: {$newStatusValue}");

            DB::commit();
            return $resignation;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resignation status update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change status');
        }
    }
}
