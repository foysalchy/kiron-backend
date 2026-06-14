<?php
namespace App\Services\Saas;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\MasterDemo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB, Log};

class MasterDemoService
{
    /**
     * Get all demos with optional filters
     */
    public function getAllDemos(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = MasterDemo::query();

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            }

            $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching master demos: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch demos');
        }
    }

    /**
     * Get demo by ID
     */
    public function getDemoById(int $id): MasterDemo
    {
        $demo = MasterDemo::find($id);
        if (!$demo) {
            throw ApiException::notFound('Demo');
        }
        return $demo;
    }

    /**
     * Create a new master demo
     */
    public function createDemo(array $data): MasterDemo
    {
        DB::beginTransaction();
        try {
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage($data['image'], 'saas/demos');
            }

            $demo = MasterDemo::create($data);

            LogHelper::created('master_demo', $demo->id, null, $demo->title);
            DB::commit();
            Log::info('Master Demo created successfully', ['demo_id' => $demo->id]);

            return $demo;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Demo creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create demo');
        }
    }

    /**
     * Update demo
     */
    public function updateDemo(int $id, array $data): MasterDemo
    {
        DB::beginTransaction();
        try {
            $demo = $this->getDemoById($id);

            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace($data['image'], $demo->image, 'saas/demos');
            }

            $demo->update($data);

            LogHelper::updated('master_demo', $demo->id, null, $demo->title);
            DB::commit();
            return $demo->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Master Demo update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update demo');
        }
    }

    /**
     * Soft delete
     */
    public function deleteDemo(int $id): bool
    {
        DB::beginTransaction();
        try {
            $demo = $this->getDemoById($id);
            $demo->delete();
            LogHelper::deleted('master_demo', $id, null, $demo->title);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete demo');
        }
    }

    /**
     * Restore
     */
    public function restoreDemo(int $id): MasterDemo
    {
        DB::beginTransaction();
        try {
            $demo = MasterDemo::onlyTrashed()->findOrFail($id);
            $demo->restore();
            LogHelper::restored('master_demo', $id, null, $demo->title);
            DB::commit();
            return $demo;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore demo');
        }
    }

    /**
     * Permanent delete
     */
    public function forceDeleteDemo(int $id): bool
    {
        DB::beginTransaction();
        try {
            $demo = MasterDemo::withTrashed()->findOrFail($id);
            if ($demo->image) {
                FileUploadHelper::delete($demo->image);
            }
            $demo->forceDelete();
            LogHelper::forceDeleted('master_demo', $id, null, $demo->title);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to permanently delete demo');
        }
    }

    /**
     * Toggle Status
     */
    public function toggleStatus(int $id): MasterDemo
    {
        DB::beginTransaction();
        try {
            $demo = $this->getDemoById($id);
            $newStatus = ($demo->status->value == Status::Active->value) ? Status::Inactive->value : Status::Active->value;
            $demo->update(['status' => $newStatus]);
            LogHelper::statusChanged('master_demo', $id, null, $demo->title . ' to ' . $newStatus);
            DB::commit();
            return $demo;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
