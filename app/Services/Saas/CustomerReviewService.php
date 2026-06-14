<?php
namespace App\Services\Saas;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\CustomerReview;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{DB,Log};

class CustomerReviewService
{
    /**
     * Get all reviews with filters
     */
    public function getAllReviews(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = CustomerReview::query();

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
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('designation', 'like', "%{$search}%")
                      ->orWhere('review', 'like', "%{$search}%");
                });
            }

            $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching reviews: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch reviews');
        }
    }

    /**
     * Get by ID
     */
    public function getReviewById(int $id): CustomerReview
    {
        $review = CustomerReview::find($id);
        if (!$review) {
            throw ApiException::notFound('Customer Review');
        }
        return $review;
    }

    /**
     * Create Review
     */
    public function createReview(array $data): CustomerReview
    {
        DB::beginTransaction();
        try {
            $review = CustomerReview::create($data);
            LogHelper::created('customer_review', $review->id, null, $review->name);
            DB::commit();
            return $review;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create review');
        }
    }

    /**
     * Update Review
     */
    public function updateReview(int $id, array $data): CustomerReview
    {
        DB::beginTransaction();
        try {
            $review = $this->getReviewById($id);
            $review->update($data);
            LogHelper::updated('customer_review', $id, null, $review->name);
            DB::commit();
            return $review->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Review update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update review');
        }
    }

    /**
     * Delete
     */
    public function deleteReview(int $id): bool
    {
        DB::beginTransaction();
        try {
            $review = $this->getReviewById($id);
            $review->delete();
            LogHelper::deleted('customer_review', $id, null, $review->name);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete review');
        }
    }

    /**
     * Restore
     */
    public function restoreReview(int $id): CustomerReview
    {
        DB::beginTransaction();
        try {
            $review = CustomerReview::onlyTrashed()->findOrFail($id);
            $review->restore();
            LogHelper::restored('customer_review', $id, null, $review->name);
            DB::commit();
            return $review;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore review');
        }
    }

    /**
     * Force Delete
     */
    public function forceDeleteReview(int $id): bool
    {
        DB::beginTransaction();
        try {
            $review = CustomerReview::withTrashed()->findOrFail($id);
            $review->forceDelete();
            LogHelper::forceDeleted('customer_review', $id, null, $review->name);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to permanently delete review');
        }
    }

    /**
     * Toggle Status
     */
    public function toggleStatus(int $id): CustomerReview
    {
        DB::beginTransaction();
        try {
            $review = $this->getReviewById($id);
            $newStatus = ($review->status->value == Status::Active->value) ? Status::Inactive->value : Status::Active->value;
            $review->update(['status' => $newStatus]);
            LogHelper::statusChanged('customer_review', $id, null, $review->name . ' to ' . $newStatus);
            DB::commit();
            return $review;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
