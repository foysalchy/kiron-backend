<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\JobTitle;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobTitleService
{
    /**
     * Get all job with optional pagination
     */
    public function getAllJobTitles(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = JobTitle::query();

            // Status Filter
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }
            // Search by Title or Description
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching job titles: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch job titles');
        }
    }

    /**
     * Get area by ID
     */
    public function getJobTitleById(int $id): JobTitle
    {
        $jobTitle = JobTitle::find($id);
        if (!$jobTitle) {
            throw ApiException::notFound('job');
        }
        return $jobTitle;
    }

    /**
     * Create a new area
     */
    public function createJobTitle(array $data): JobTitle
    {
        DB::beginTransaction();
        try {
            $jobTitle = JobTitle::create($data);
            LogHelper::created('job_title', $jobTitle->id, $jobTitle->company_id, $jobTitle->title);
            DB::commit();
            Log::info('Job Title created successfully', ['id' => $jobTitle->id]);
            return $jobTitle;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Title creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create job title');
        }
    }


    /**
     * Update area
     */
    public function updateJobTitle(int $id, array $data): JobTitle
    {
        DB::beginTransaction();
        try {
            $jobTitle = $this->getJobTitleById($id);
            $jobTitle->update($data);
            LogHelper::updated('job_title', $id, $jobTitle->company_id, $jobTitle->title);
            DB::commit();
            Log::info('Job Title updated successfully', ['id' => $id]);
            return $jobTitle->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Title update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update job title');
        }
    }

    public function deleteJobTitle(int $id): bool
    {
        DB::beginTransaction();
        try {
            $jobTitle = $this->getJobTitleById($id);
            $jobTitle->delete();
            LogHelper::deleted('job_title', $id, $jobTitle->company_id, $jobTitle->title);
            Log::info('Job Title deleted (Soft)', ['id' => $id]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete job title');
        }
    }

    public function restoreJobTitle(int $id): JobTitle
    {
        DB::beginTransaction();
        try {
            $jobTitle = JobTitle::withTrashed()->find($id);
            if (!$jobTitle) throw ApiException::notFound('Job Title');
            $jobTitle->restore();
            LogHelper::restored('job_title', $id, $jobTitle->company_id, $jobTitle->title);
            DB::commit();
            return $jobTitle;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore');
        }
    }

    public function forceDeleteJobTitle(int $id): bool
    {
        DB::beginTransaction();
        try {
            $jobTitle = JobTitle::withTrashed()->find($id);
            if (!$jobTitle) throw ApiException::notFound('Job Title');
            $jobTitle->forceDelete();
            LogHelper::forceDeleted('job_title', $id, $jobTitle->company_id, $jobTitle->title);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Title permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete');
        }
    }

    public function toggleStatus(int $id): JobTitle
    {
        DB::beginTransaction();
        try {
            $jobTitle = $this->getJobTitleById($id);
            $currentStatus = Status::from($jobTitle->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $jobTitle->update([
                'status' => $newStatus->value
            ]);
            LogHelper::statusChanged('job_title', $id, $jobTitle->company_id, $jobTitle->title .' new status '. $newStatus->label());
            DB::commit();
            return $jobTitle;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
