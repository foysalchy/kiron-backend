<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Rejoin;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Log};

class RejoinService
{
    /**
     * Get all rejoin records with optional pagination and searching
     */
    public function getAllRejoins(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Rejoin::with(['employee:id,first_name']);

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (isset($filters['search'])) {
                $query->whereHas('employee', function ($q) use ($filters) {
                    $q->where('first_name', 'like', "%{$filters['search']}%");
                });
            }


            $sortBy = $filters['sort_by'] ?? 'rejoin_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching rejoins: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch rejoin records');
        }
    }

    /**
     * Get rejoin record by ID
     */
    public function getRejoinById(int $id): Rejoin
    {
        $rejoin = Rejoin::with(['employee'])->find($id);

        if (!$rejoin) {
            throw ApiException::notFound('Rejoin record');
        }

        return $rejoin;
    }
    /**
     * Create a new rejoin entry
     */
    public function createRejoin(array $data): Rejoin
    {
        DB::beginTransaction();

        try {
            // Handle appointment letter upload
            if (isset($data['appointment_letter']) && $data['appointment_letter']->isValid()) {
                $data['appointment_letter'] = FileUploadHelper::upload(
                    $data['appointment_letter'],
                    'rejoins/letters'
                );
            }

            $rejoin = Rejoin::create($data);
            LogHelper::created('rejoin', $rejoin->id, $rejoin->company_id);

            DB::commit();

            Log::info('Rejoin record created successfully', ['rejoin_id' => $rejoin->id]);

            return $rejoin->load(['employee']);
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['appointment_letter'])) {
                FileUploadHelper::delete($data['appointment_letter']);
            }

            Log::error('Rejoin creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create rejoin record');
        }
    }

    public function updateRejoin(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $rejoin = Rejoin::findOrFail($id);

            // File Upload Handling
            if (isset($data['appointment_letter']) && $data['appointment_letter'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old letter if exists
                if ($rejoin->appointment_letter) {
                    FileUploadHelper::delete($rejoin->appointment_letter);
                }
                // Upload new letter
                $data['appointment_letter'] = FileUploadHelper::upload(
                    $data['appointment_letter'],
                    'rejoins/appointment_letters'
                );
            }

            $rejoin->update($data);

            LogHelper::updated('rejoin', $rejoin->id, $rejoin->company_id, "Employee rejoin record updated.");

            DB::commit();
            return $rejoin->fresh('employee');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rejoin update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update rejoin record.');
        }
    }
}
