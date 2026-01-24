<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PageService
{
    /**
     * Get all pages with optional pagination
     */
    public function getAllPages(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Page::query();
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }
            if (isset($filters['search'])) {
                $query->where('title', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching pages: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch pages');
        }
    }

    /**
     * Get page by ID
     */
    public function getPageById(int $id): Page
    {
        $page = Page::find($id);

        if (!$page) {
            throw ApiException::notFound('Page');
        }

        return $page;
    }

    /**
     * Create a new page
     */
    public function createPage(array $data): Page
    {
        DB::beginTransaction();

        try {
            // Handle image upload
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage(
                    $data['image'],
                    'pages/images',
                    'public',
                    2048
                );
            }

            $page = Page::create($data);
            LogHelper::created('page', $page->id, $page->company_id, $page->title);

            DB::commit();

            Log::info('Page created successfully', ['page_id' => $page->id]);

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Page creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create page');
        }
    }

    /**
     * Update page
     */
    public function updatePage(int $id, array $data): Page
    {
        DB::beginTransaction();

        try {
            $page = $this->getPageById($id);

            // Handle image upload
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace(
                    $data['image'],
                    $page->image,
                    'pages/images'
                );
            }

            $page->update($data);
            LogHelper::updated('page', $page->id, $page->company_id, $page->title);

            DB::commit();

            Log::info('Page updated successfully', ['page_id' => $page->id]);

            return $page;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image'])) {
                FileUploadHelper::delete($data['image']);
            }

            Log::error('Page update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update page');
        }
    }

    /**
     * Delete page (soft delete)
     */
    public function deletePage(int $id): bool
    {
        DB::beginTransaction();
        try {
            $page = $this->getPageById($id);
            $page->delete();
            LogHelper::deleted('page', $page->id, $page->company_id, $page->title);

            Log::info('Page deleted successfully', ['page_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Page deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete page');
        }
    }

    /**
     * Restore soft deleted page
     */
    public function restorePage(int $id): Page
    {
        DB::beginTransaction();
        try {
            $page = Page::withTrashed()->find($id);

            if (!$page) {
                throw ApiException::notFound('Page');
            }

            $page->restore();
            LogHelper::restored('page', $page->id, $page->company_id, $page->title);

            Log::info('Page restored successfully', ['page_id' => $id]);

            DB::commit();
            return $page;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Page restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore page');
        }
    }

    /**
     * Permanently delete page
     */
    public function forceDeletePage(int $id): bool
    {
        DB::beginTransaction();

        try {
            $page = Page::withTrashed()->find($id);

            if (!$page) {
                throw ApiException::notFound('Page');
            }

            // Delete logo
            FileUploadHelper::delete($page->image);

            $page->forceDelete();
            LogHelper::forceDeleted('page', $page->id, $page->company_id, $page->title);

            DB::commit();

            Log::info('Page permanently deleted', ['page_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Page permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete page');
        }
    }

    /**
     * Toggle page status
     */
    public function toggleStatus(int $id): Page
    {
        DB::beginTransaction();
        try {
            $page = $this->getPageById($id);
            $currentStatus = Status::from($page->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $page->update([
                'status' => $newStatus->value
            ]);
            LogHelper::statusChanged('page', $page->id, $page->company_id,$page->title .' new status '.$newStatus->label());
            Log::info('Page status toggled', ['page_id' => $id]);

            DB::commit();
            return $page;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Page status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle page status');
        }
    }
}
