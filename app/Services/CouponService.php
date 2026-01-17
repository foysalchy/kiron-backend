<?php

namespace App\Services;

use App\Models\{Coupon, CouponUsage};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class CouponService
{
    /**
     * Get all coupons
     */
    public function getAllCoupons(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Coupon::query();

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['discount_type'])) {
                $query->where('discount_type', $filters['discount_type']);
            }

            if (isset($filters['search'])) {
                $query->where(function($q) use ($filters) {
                    $q->where('code', 'like', "%{$filters['search']}%")
                      ->orWhere('name', 'like', "%{$filters['search']}%");
                });
            }

            if (isset($filters['valid_only']) && $filters['valid_only']) {
                $query->valid();
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate 
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Exception $e) {
            Log::error('Error fetching coupons: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch coupons');
        }
    }

    /**
     * Get coupon by ID
     */
    public function getCouponById(int $id): Coupon
    {
        $coupon = Coupon::with('couponUsages')->find($id);

        if (!$coupon) {
            throw ApiException::notFound('Coupon');
        }

        return $coupon;
    }

    /**
     * Get coupon by code
     */
    public function getCouponByCode(string $code): Coupon
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            throw ApiException::notFound('Coupon');
        }

        return $coupon;
    }

    /**
     * Create coupon
     */
    public function createCoupon(array $data): Coupon
    {
        DB::beginTransaction();

        try {

            $data['used_count'] = 0;

            $coupon = Coupon::create($data);

            DB::commit();

            Log::info('Coupon created successfully', ['coupon_id' => $coupon->id]);
            LogHelper::created('coupon', $coupon->id, $coupon->company_id);

            return $coupon;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Coupon creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create coupon');
        }
    }

    /**
     * Update coupon
     */
    public function updateCoupon(int $id, array $data): Coupon
    {
        DB::beginTransaction();

        try {
            $coupon = $this->getCouponById($id);

            // Cannot update if already used
            if ($coupon->used_count > 0) {
                throw ApiException::badRequest('Cannot update coupon that has already been used');
            }

            $coupon->update($data);

            DB::commit();

            Log::info('Coupon updated successfully', ['coupon_id' => $coupon->id]);
            LogHelper::updated('coupon', $coupon->id, $coupon->company_id);

            return $coupon->fresh();

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Coupon update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update coupon');
        }
    }

    /**
     * Delete coupon
     */
    public function deleteCoupon(int $id): bool
    {
        DB::beginTransaction();

        try {
            $coupon = $this->getCouponById($id);

            // Cannot delete if already used
            if ($coupon->used_count > 0) {
                throw ApiException::badRequest('Cannot delete coupon that has already been used');
            }

            $coupon->delete();

            DB::commit();

            Log::info('Coupon deleted successfully', ['coupon_id' => $id]);
            LogHelper::deleted('coupon', $id, $coupon->company_id);

            return true;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Coupon deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete coupon');
        }
    }

    /**
     * Validate coupon for use
     */
    public function validateCoupon(string $code, float $orderAmount, ?int $customerId = null): array
    {
        try {
            $coupon = $this->getCouponByCode($code);

            // Check if coupon is valid
            if (!$coupon->isValid()) {
                throw ApiException::badRequest('Coupon is not valid or has expired');
            }

            // Check minimum purchase
            if (!$coupon->meetsMinimumPurchase($orderAmount)) {
                throw ApiException::badRequest(
                    "Minimum purchase amount is {$coupon->min_purchase_amount}"
                );
            }

            // Check customer usage limit
            if ($customerId && !$coupon->canBeUsedByCustomer($customerId)) {
                throw ApiException::badRequest('You have already used this coupon maximum times');
            }

            // Calculate discount
            $discountAmount = $coupon->calculateDiscount($orderAmount);

            return [
                'valid' => true,
                'coupon_id' => $coupon->id,
                'coupon_code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'final_amount' => $orderAmount - $discountAmount,
            ];

        } catch (ApiException $e) {
            throw $e;

        } catch (\Exception $e) {
            Log::error('Coupon validation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to validate coupon');
        }
    }

    /**
     * Apply coupon (record usage)
     */
    public function applyCoupon(int $couponId, int $orderId, float $orderAmount, float $discountAmount, ?int $customerId = null): CouponUsage
    {
        DB::beginTransaction();

        try {
            $coupon = $this->getCouponById($couponId);

            // Create usage record
            $usage = CouponUsage::create([
                'coupon_id' => $couponId,
                'customer_id' => $customerId,
                'order_id' => $orderId,
                'order_amount' => $orderAmount,
                'discount_amount' => $discountAmount,
            ]);

            // Increment used count
            $coupon->increment('used_count');

            DB::commit();

            Log::info('Coupon applied successfully', [
                'coupon_id' => $couponId, 
                'order_id' => $orderId
            ]);

            return $usage;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Coupon application failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to apply coupon');
        }
    }

    /**
     * Change coupon status
     */
    public function changeStatus(int $id, int $status): Coupon
    {
        try {
            $coupon = $this->getCouponById($id);
            $coupon->update(['status' => $status]);

            Log::info('Coupon status changed', ['coupon_id' => $id, 'status' => $status]);
            LogHelper::custom('status_changed', 'coupon', $id, $coupon->company_id);

            return $coupon;

        } catch (\Exception $e) {
            Log::error('Coupon status change failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change status');
        }
    }
}