<?php

namespace App\Services;

use App\Models\{PosOrder, PosOrderDetail, PosOrderPayment};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

class PosOrderService
{
    public function __construct(
        protected CouponService $couponService
    ) {}

    /**
     * Get all POS orders
     */
    public function getAllPosOrders(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = PosOrder::with([
                'warehouse',
                'customer',
                'coupon',
                'posOrderDetails.product',
                'posOrderPayments'
            ]);

            if (isset($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (isset($filters['customer_id'])) {
                $query->where('customer_id', $filters['customer_id']);
            }

            if (isset($filters['is_walk_in'])) {
                $query->where('is_walk_in', $filters['is_walk_in']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['payment_status'])) {
                $query->where('payment_status', $filters['payment_status']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('order_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('order_date', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $query->where('order_no', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'order_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate 
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Exception $e) {
            Log::error('Error fetching POS orders: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch POS orders');
        }
    }

    /**
     * Get POS order by ID
     */
    public function getPosOrderById(int $id): PosOrder
    {
        $order = PosOrder::with([
            'warehouse',
            'customer',
            'coupon',
            'posOrderDetails.product',
            'posOrderPayments'
        ])->find($id);

        if (!$order) {
            throw ApiException::notFound('POS Order');
        }

        return $order;
    }

    /**
     * Create POS order
     */
    public function createPosOrder(array $data): PosOrder
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            $payments = $data['payments'];
            $couponCode = $data['coupon_code'] ?? null;

            unset($data['items'], $data['payments'], $data['coupon_code']);
            $data['order_date'] = $data['order_date'] ?? Carbon::now();

            // Calculate totals
            $totals = $this->calculateTotals($items);
            $data['total_items'] = $totals['total_items'];
            $data['subtotal'] = $totals['subtotal'];
            $data['tax_amount'] = $totals['tax_amount'];
            $data['grand_total'] = $totals['grand_total'];

            // Apply coupon if provided
            $data['discount_amount'] = 0;
            $data['coupon_id'] = null;

            if ($couponCode) {
                $couponResult = $this->couponService->validateCoupon(
                    $couponCode,
                    $data['grand_total'],
                    $data['customer_id']
                );

                $data['coupon_id'] = $couponResult['coupon_id'];
                $data['discount_amount'] = $couponResult['discount_amount'];
                $data['grand_total'] = $couponResult['final_amount'];
            }

            // Calculate payment totals
            $totalPaid = array_sum(array_column($payments, 'amount'));
            $data['paid_amount'] = $totalPaid;
            $data['change_amount'] = max(0, $totalPaid - $data['grand_total']);

            // Determine payment status
            $data['payment_status'] = $this->determinePaymentStatus(
                $data['grand_total'],
                $totalPaid
            );

            // Create order
            $order = PosOrder::create($data);

            // Create order details
            foreach ($items as $item) {
                $itemSubtotal = $this->calculateItemSubtotal($item);

                PosOrderDetail::create([
                    'pos_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'subtotal' => $itemSubtotal,
                ]);
            }

            // Create payments
            foreach ($payments as $payment) {
                PosOrderPayment::create([
                    'pos_order_id' => $order->id,
                    'amount' => $payment['amount'],
                    'payment_method' => $payment['payment_method'],
                    'reference_no' => $payment['reference_no'] ?? null,
                    'note' => $payment['note'] ?? null,
                ]);
            }

            // Apply coupon usage
            if ($data['coupon_id']) {
                $this->couponService->applyCoupon(
                    $data['coupon_id'],
                    $order->id,
                    $data['subtotal'],
                    $data['discount_amount'],
                    $data['customer_id']
                );
            }

            DB::commit();

            Log::info('POS order created successfully', ['order_id' => $order->id]);
            LogHelper::created('pos_order', $order->id, $order->company_id);

            return $order->load([
                'warehouse',
                'customer',
                'coupon',
                'posOrderDetails.product',
                'posOrderPayments'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('POS order creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create POS order: ' . $e->getMessage());
        }
    }

    /**
     * Calculate item subtotal
     */
    private function calculateItemSubtotal(array $item): float
    {
        $quantity = $item['quantity'];
        $unitPrice = $item['unit_price'];
        $discount = $item['discount'] ?? 0;
        $tax = $item['tax'] ?? 0;

        $subtotal = ($quantity * $unitPrice) - $discount + $tax;

        return round($subtotal, 2);
    }

    /**
     * Calculate order totals
     */
    private function calculateTotals(array $items): array
    {
        $totalItems = 0;
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($items as $item) {
            $totalItems += $item['quantity'];
            $itemSubtotal = $this->calculateItemSubtotal($item);
            $subtotal += $itemSubtotal;
            $taxAmount += $item['tax'] ?? 0;
        }

        return [
            'total_items' => $totalItems,
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'grand_total' => round($subtotal, 2),
        ];
    }

    /**
     * Determine payment status
     */
    private function determinePaymentStatus(float $grandTotal, float $paidAmount): int
    {
        if ($paidAmount <= 0) {
            return PosOrder::PAYMENT_PENDING;
        }

        if ($paidAmount >= $grandTotal) {
            return PosOrder::PAYMENT_PAID;
        }

        return PosOrder::PAYMENT_PARTIAL;
    }

    

    /**
     * Cancel order
     */
    public function cancelOrder(int $id): PosOrder
    {
        DB::beginTransaction();

        try {
            $order = $this->getPosOrderById($id);

            if ($order->isCompleted()) {
                throw ApiException::badRequest('Cannot cancel completed order');
            }

            if ($order->isCancelled()) {
                throw ApiException::badRequest('Order is already cancelled');
            }

            $order->update(['status' => PosOrder::STATUS_CANCELLED]);

            DB::commit();

            Log::info('POS order cancelled', ['order_id' => $id]);
            LogHelper::custom('cancelled', 'pos_order', $id, $order->company_id);

            return $order;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('POS order cancellation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to cancel order');
        }
    }

    /**
     * Complete order
     */
    public function completeOrder(int $id): PosOrder
    {
        DB::beginTransaction();

        try {
            $order = $this->getPosOrderById($id);

            if ($order->isCompleted()) {
                throw ApiException::badRequest('Order is already completed');
            }

            if ($order->isCancelled()) {
                throw ApiException::badRequest('Cannot complete cancelled order');
            }

            $order->update(['status' => PosOrder::STATUS_COMPLETED]);

            DB::commit();

            Log::info('POS order completed', ['order_id' => $id]);
            LogHelper::custom('completed', 'pos_order', $id, $order->company_id);

            return $order;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('POS order completion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to complete order');
        }
    }

    /**
     * Hold order
     */
    public function holdOrder(int $id, ?string $ref = null): PosOrder
    {
        DB::beginTransaction();

        try {
            $order = $this->getPosOrderById($id);

            if ($order->isCompleted()) {
                throw ApiException::badRequest('Cannot hold completed order');
            }

            if ($order->isCancelled()) {
                throw ApiException::badRequest('Cannot hold cancelled order');
            }

            if ($order->isOnHold()) {
                throw ApiException::badRequest('Order is already on hold');
            }

            $order->update([
                'status' => PosOrder::STATUS_ON_HOLD,
                'hold_ref' => $ref,
            ]);

            DB::commit();

            Log::info('POS order put on hold', ['order_id' => $id]);
            LogHelper::custom('on_hold', 'pos_order', $id, $order->company_id);

            return $order;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('POS order hold failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to hold order');
        }
    }

    /**
     * Resume held order
     */
    public function resumeOrder(int $id): PosOrder
    {
        DB::beginTransaction();

        try {
            $order = $this->getPosOrderById($id);

            if (!$order->isOnHold()) {
                throw ApiException::badRequest('Order is not on hold');
            }

            $order->update([
                'status' => PosOrder::STATUS_PENDING,
                'hold_reason' => null,
            ]);

            DB::commit();

            Log::info('POS order resumed from hold', ['order_id' => $id]);
            LogHelper::custom('resumed', 'pos_order', $id, $order->company_id);

            return $order;

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('POS order resume failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to resume order');
        }
    }

    /**
     * Get all held orders
     */
    public function getHeldOrders(int $warehouseId): Collection
    {
        try {
            return PosOrder::with([
                'customer',
                'posOrderDetails.product'
            ])
            ->where('warehouse_id', $warehouseId)
            ->onHold()
            ->orderBy('created_at', 'desc')
            ->get();

        } catch (\Exception $e) {
            Log::error('Error fetching held orders: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch held orders');
        }
    }
}