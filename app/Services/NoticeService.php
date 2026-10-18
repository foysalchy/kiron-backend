<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Notice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NoticeService
{
    /**
     * Get all notices with optional pagination
     */
    public function getAllNotices(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Notice::query();
            if (!empty($filters['select'])) {
                $selectArray = is_string($filters['select']) ? explode(',', $filters['select']) : $filters['select'];
                $query->select($selectArray);
            }

            if (!empty($filters['with'])) {
                $query->with($filters['with']);
            }

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
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching notices: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch notices');
        }
    }

    /**
     * Get notice by ID
     */
    public function getNoticeById(int $id): Notice
    {
        $notice = Notice::find($id);
        if (!$notice) {
            throw ApiException::notFound('Notice');
        }
        return $notice;
    }

    /**
     * Create a new notice
     */
    public function createNotice(array $data): Notice
    {
        DB::beginTransaction();
        try {
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $customFileName = Str::slug($data['slug'] ?? 'notice') . '-' . time();
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'notices',
                    'r2',
                    2048,
                    $customFileName
                );
            }

            $notice = Notice::create($data);
            LogHelper::created('notice', $notice->id, $notice->company_id, $notice->title);
            DB::commit();

            Log::info('Notice created successfully', ['notice_id' => $notice->id]);

            return $notice;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Notice creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create notice');
        }
    }

    /**
     * Update notice
     */
    public function updateNotice(int $id, array $data): Notice
    {
        DB::beginTransaction();
        try {
            $notice = $this->getNoticeById($id);

            if (empty($data['slug']) && isset($data['title'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($notice->image) {
                    FileUploadHelper::deleteFile($notice->image, 'r2');
                }
                $customFileName = Str::slug($data['slug'] ?? $notice->slug ?? 'notice') . '-' . time();
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'notices',
                    'r2',
                    2048,
                    $customFileName
                );
            } elseif (!isset($data['image'])) {
                unset($data['image']);
            }

            $notice->update($data);

            LogHelper::updated('notice', $notice->id, $notice->company_id, $notice->title);
            Log::info('Notice updated successfully', ['notice_id' => $notice->id]);

            DB::commit();
            return $notice->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Notice update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update notice');
        }
    }

    /**
     * Delete notice (soft delete)
     */
    public function deleteNotice(int $id): bool
    {
        DB::beginTransaction();
        try {
            $notice = $this->getNoticeById($id);
            $notice->delete();

            LogHelper::deleted('notice', $notice->id, $notice->company_id, $notice->title);
            Log::info('Notice soft deleted successfully', ['notice_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Notice delete failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete notice');
        }
    }

    /**
     * Restore soft deleted notice
     */
    public function restoreNotice(int $id): Notice
    {
        DB::beginTransaction();
        try {
            $notice = Notice::withTrashed()->find($id);
            if (!$notice) {
                throw ApiException::notFound('Notice');
            }

            $notice->restore();

            LogHelper::restored('notice', $notice->id, $notice->company_id, $notice->title);
            Log::info('Notice restored successfully', ['notice_id' => $id]);

            DB::commit();
            return $notice;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Notice restore failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore notice');
        }
    }

    /**
     * Permanently delete notice
     */
    public function forceDeleteNotice(int $id): bool
    {
        DB::beginTransaction();
        try {
            $notice = Notice::withTrashed()->find($id);
            if (!$notice) {
                throw ApiException::notFound('Notice');
            }

            if ($notice->image) {
                FileUploadHelper::deleteFile($notice->image, 'r2');
            }

            $notice->forceDelete();

            LogHelper::forceDeleted('notice', $id, $notice->company_id);
            Log::info('Notice permanently deleted', ['notice_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Notice force delete failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete notice');
        }
    }

    /**
     * Toggle status (active/inactive)
     */
    public function toggleStatus(int $id): Notice
    {
        DB::beginTransaction();
        try {
            $notice = $this->getNoticeById($id);
            $newStatus = $notice->status === Status::Active->value ? Status::Inactive->value : Status::Active->value;
            $notice->update(['status' => $newStatus]);

            LogHelper::statusChanged('notice', $notice->id, $notice->company_id);
            Log::info('Notice status toggled', ['notice_id' => $id, 'status' => $newStatus]);

            DB::commit();
            return $notice->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Notice toggle status failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle notice status');
        }
    }
}
