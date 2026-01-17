<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{StoreCouponRequest, UpdateCouponRequest};
use App\Services\CouponService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\{JsonResponse, Request};

class CouponController extends Controller
{
    public function __construct(
        protected CouponService $couponService
    ) {}

    /**
     * Get all coupons
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'discount_type' => $request->query('discount_type'),
            'search' => $request->query('search'),
            'valid_only' => $request->query('valid_only'),
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        $data = $this->couponService->getAllCoupons($filters, true);

        return ResponseHelper::success($data, 'Coupons retrieved successfully');
    }

    /**
     * Create new coupon
     */
    public function store(StoreCouponRequest $request): JsonResponse
    {
        $data = $this->couponService->createCoupon($request->validated());

        return ResponseHelper::success($data, 'Coupon created successfully', 201);
    }

    /**
     * Get single coupon
     */
    public function show(int $id): JsonResponse
    {
        $data = $this->couponService->getCouponById($id);

        return ResponseHelper::success($data, 'Coupon retrieved successfully');
    }

    /**
     * Update coupon
     */
    public function update(UpdateCouponRequest $request, int $id): JsonResponse
    {
        $data = $this->couponService->updateCoupon($id, $request->validated());

        return ResponseHelper::success($data, 'Coupon updated successfully');
    }

    /**
     * Delete coupon
     */
    public function destroy(int $id): JsonResponse
    {
        $this->couponService->deleteCoupon($id);

        return ResponseHelper::success(null, 'Coupon deleted successfully');
    }

    /**
     * Validate coupon
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'order_amount' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:parties,id',
        ]);

        $result = $this->couponService->validateCoupon(
            $request->code,
            $request->order_amount,
            $request->customer_id
        );

        return ResponseHelper::success($result, 'Coupon validated successfully');
    }

    /**
     * Change status
     */
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:0,1',
        ]);

        $data = $this->couponService->changeStatus($id, $request->status);

        return ResponseHelper::success($data, 'Coupon status updated successfully');
    }
}