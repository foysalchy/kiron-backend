<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\BusinessPaymentMethod;
use App\Models\Page;
use App\Models\PaymentMethodType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PageService
{
   /**
     * ১. Get All Pages (With Pagination & Filters)
     */
    public function getAllPages(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Page::query();

            // Status Filter (Enum mapping)
            if (isset($filters['status']) && $filters['status'] !== 'all') {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Title
            if (!empty($filters['search'])) {
                $query->where('title', 'like', "%{$filters['search']}%");
            }

            $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Exception $e) {
            Log::error('Error fetching pages: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch pages');
        }
    }

    /**
     * ২. Get Page by ID
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
     * ৩. Create Page
     */
    public function createPage(array $data): Page
    {
        DB::beginTransaction();
        try {
            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::uploadImage($data['image'], 'pages');
            }

            $page = Page::create($data);
            LogHelper::created('page', $page->id, $page->company_id, $page->title);

            DB::commit();
            Log::info('Page created successfully', ['id' => $page->id]);

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['image'])) FileUploadHelper::delete($data['image']);
            Log::error('Page creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create page');
        }
    }

    /**
     * ৪. Update Page
     */
    public function updatePage(int $id, array $data): Page
    {
        DB::beginTransaction();
        try {
            $page = $this->getPageById($id);

            if (isset($data['image'])) {
                $data['image'] = FileUploadHelper::replace($data['image'], $page->image, 'pages');
            }

            $page->update($data);
            LogHelper::updated('page', $page->id, $page->company_id, $page->title);

            DB::commit();
            Log::info('Page updated successfully', ['id' => $page->id]);

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Page update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update page');
        }
    }

    /**
     * ৫. Delete Page (Soft Delete)
     */
    public function deletePage(int $id): bool
    {
        DB::beginTransaction();
        try {
            $page = $this->getPageById($id);
            $page->delete();

            LogHelper::deleted('page', $page->id, $page->company_id, $page->title);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete page');
        }
    }

    /**
     * ৬. Restore Page (From Trash)
     */
    public function restorePage(int $id): Page
    {
        DB::beginTransaction();
        try {
            $page = Page::withTrashed()->find($id);
            if (!$page) throw ApiException::notFound('Page');

            $page->restore();
            LogHelper::restored('page', $page->id, $page->company_id, $page->title);

            DB::commit();
            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore page');
        }
    }

    /**
     * ৭. Permanent Delete
     */
    public function forceDeletePage(int $id): bool
    {
        DB::beginTransaction();
        try {
            $page = Page::withTrashed()->find($id);
            if (!$page) throw ApiException::notFound('Page');

            if ($page->image) {
                FileUploadHelper::delete($page->image);
            }

            $page->forceDelete();
            LogHelper::forceDeleted('page', $page->id, $page->company_id, $page->title);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to permanently delete page');
        }
    }
    /**
     * Toggle status
     */
    public function toggleStatus(int $id): page
    {
        DB::beginTransaction();
        try {
            $page = $this->getPageById($id);
            $currentStatus = Status::from($page->status);

            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $page->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('page',$page->id,$page->company_id,$page->title . ' status changed to ' . $newStatus->label());
            DB::commit();
            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
