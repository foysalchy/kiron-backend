<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{Order, OrderDetail, OrderPayment, Product};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

class OrderService
{
    protected ProductService $productService;
    protected CouponService $couponService;

    public function __construct(
        CouponService $couponService,
        ProductService $productService
    ) {
        $this->productService = $productService;
        $this->couponService = $couponService;
    }

    /**
     * Get all orders
     */
    public function getAllOrders(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Order::with([
                'warehouse',
                'customer',
                'coupon',
                'orderDetails.product',
                'orderPayments'
            ]);

            if (isset($filters['type'])) {
                $query->where('type', $filters['type']);
            }

            if (isset($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (isset($filters['customer_id'])) {
                $query->where('customer_id', $filters['customer_id']);
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
                $query->where(function ($q) use ($filters) {
                    $q->where('order_no', 'like', "%{$filters['search']}%")
                        ->orWhere('reference_no', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'order_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch orders');
        }
    }

    /**
     * Get order by ID
     */
    public function getOrderById(int $id, $type): Order
    {
        $order = Order::with([
            'warehouse',
            'customer',
            'coupon',
            'orderDetails.product',
            'orderPayments'
        ])->where('type', $type)->find($id);

        if (!$order) {
            throw ApiException::notFound('Order');
        }

        return $order;
    }

    /**
     * Get hold order list
     */
    public function getHoldOrderList(int $id, $type): Collection
    {
        $order = Order::with([
            'warehouse',
            'customer',
            'coupon',
            'orderDetails.product',
            'orderPayments'
        ])->where('type', $type)
            ->where('warehouse_id', $id)
            ->where('status', Status::Hold->value)
            ->get();

        if (!$order) {
            throw ApiException::notFound('Order');
        }

        return $order;
    }

    /**
     * Create POS Order
     */
    public function createPOSOrder(array $data): Order
    {
        $data['type'] = Order::TYPE_POS;
        return $this->createOrder($data, true);
    }

    /**
     * Create Sales Order
     */
    public function createSalesOrder(array $data): Order
    {
        $data['type'] = Order::TYPE_SALES;
        return $this->createOrder($data, false);
    }

    /**
     * Create order (unified logic)
     */
    private function createOrder(array $data, bool $isPOS = false): Order
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            $couponCode = $data['coupon_code'] ?? null;
            $payments = $data['payments'] ?? [];

            unset($data['items'], $data['payments'], $data['coupon_code']);

            $data['order_date'] = $data['order_date'] ?? Carbon::now();

            // Calculate totals
            $totals = $this->calculateTotals($items, $data);
            $data = array_merge($data, $totals);

            // Apply coupon if provided
            $data['coupon_discount'] = 0;
            $data['coupon_id'] = null;

            if ($couponCode) {
                $couponResult = $this->couponService->validateCoupon(
                    $couponCode,
                    $data['grand_total'],
                    $data['customer_id'] ?? null
                );

                $data['coupon_id'] = $couponResult['coupon_id'];
                $data['coupon_discount'] = $couponResult['discount_amount'];
                $data['grand_total'] -= $data['coupon_discount'];
            }

            // Handle payments
            $totalPaid = 0;
            if (!empty($payments)) {
                $totalPaid = array_sum(array_column($payments, 'amount'));
            }

            $data['payment_amount'] = $totalPaid;
            $data['payment_status'] = $this->determinePaymentStatus($data['grand_total'], $totalPaid);

            // Create order
            $order = Order::create($data);

            // Create order details and deduct stock
            foreach ($items as $item) {
                $itemTotal = $this->calculateItemTotal($item);

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);

                // Deduct stock using ProductService (for completed/pending orders, not hold)
                if ($order->status !== Status::Hold->value) {
                    $this->deductOrderStock($order, $item);
                }
            }

            // Create payments
            if (!empty($payments)) {
                foreach ($payments as $payment) {
                    OrderPayment::create([
                        'order_id' => $order->id,
                        'amount' => $payment['amount'],
                        'payment_method' => $payment['payment_method'],
                        'reference_no' => $payment['reference_no'] ?? null,
                        'note' => $payment['note'] ?? null,
                    ]);
                }
            }

            // Apply coupon usage
            if ($data['coupon_id']) {
                $this->couponService->applyCoupon(
                    $data['coupon_id'],
                    $order->id,
                    $data['subtotal'],
                    $data['coupon_discount'],
                    $data['customer_id'] ?? null
                );
            }

            DB::commit();

            Log::info('Order created successfully', [
                'order_id' => $order->id,
                'type' => $order->type,
                'status' => $order->status
            ]);
            LogHelper::created('order', $order->id, $order->company_id);

            return $order->load([
                'warehouse',
                'customer',
                'coupon',
                'orderDetails.product',
                'orderPayments'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create order: ' . $e->getMessage());
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(int $id, string $type): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->getOrderById($id, $type);

            if ($order->isCompleted()) {
                throw ApiException::badRequest('Cannot cancel completed order');
            }

            if ($order->isCancelled()) {
                throw ApiException::badRequest('Order is already cancelled');
            }

            $oldStatus = $order->status;

            // If order was pending/completed (not on hold), restore stock
            if ($oldStatus !== Status::Hold->value) {
                $this->restoreOrderStock($order);
            }

            $order->update(['status' => Status::Cancelled->value]);

            DB::commit();

            Log::info('Order cancelled', ['order_id' => $id]);
            LogHelper::custom('cancelled', 'order', $id, $order->company_id);

            return $order->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order cancellation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to cancel order');
        }
    }

    /**
     * Complete order
     */
    public function completeOrder(int $id, string $type): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->getOrderById($id, $type);

            if ($order->isCompleted()) {
                throw ApiException::badRequest('Order is already completed');
            }

            if ($order->isCancelled()) {
                throw ApiException::badRequest('Cannot complete cancelled order');
            }

            $oldStatus = $order->status;

            // If order was on hold, deduct stock now
            if ($oldStatus === Status::Hold->value) {
                foreach ($order->orderDetails as $detail) {
                    $this->deductOrderStock($order, [
                        'product_id' => $detail->product_id,
                        'quantity' => $detail->quantity
                    ]);
                }
            }

            $order->update(['status' => Status::Completed->value]);

            DB::commit();

            Log::info('Order completed', ['order_id' => $id]);
            LogHelper::custom('completed', 'order', $id, $order->company_id);

            return $order->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order completion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to complete order');
        }
    }

    /**
     * Hold order (POS only)
     */
    public function holdOrder(int $id, ?string $ref = null, string $type): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->getOrderById($id, $type);

            if (!$order->isPOS()) {
                throw ApiException::badRequest('Only POS orders can be put on hold');
            }

            if ($order->isCompleted() || $order->isCancelled()) {
                throw ApiException::badRequest('Cannot hold completed or cancelled order');
            }

            if ($order->isOnHold()) {
                throw ApiException::badRequest('Order is already on hold');
            }

            $oldStatus = $order->status;

            // If order was pending, restore stock (will be deducted when resumed/completed)
            if ($oldStatus === Status::Pending->value) {
                $this->restoreOrderStock($order);
            }

            $order->update([
                'status' => Status::Hold->value,
                'hold_ref' => $ref,
            ]);

            DB::commit();

            Log::info('Order put on hold', ['order_id' => $id]);
            LogHelper::custom('on_hold', 'order', $id, $order->company_id);

            return $order->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order hold failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to hold order');
        }
    }

    /**
     * Resume held order
     */
    public function resumeOrder(int $id, string $type): Order
    {
        DB::beginTransaction();

        try {
            $order = $this->getOrderById($id, $type);

            if (!$order->isOnHold()) {
                throw ApiException::badRequest('Order is not on hold');
            }

            // Deduct stock when resuming
            foreach ($order->orderDetails as $detail) {
                $this->deductOrderStock($order, [
                    'product_id' => $detail->product_id,
                    'quantity' => $detail->quantity
                ]);
            }

            $order->update([
                'status' => Status::Pending->value,
                'hold_ref' => null,
            ]);

            DB::commit();

            Log::info('Order resumed from hold', ['order_id' => $id]);
            LogHelper::custom('resumed', 'order', $id, $order->company_id);

            return $order->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order resume failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to resume order');
        }
    }

    /**
     * Deduct stock for order item
     */
    private function deductOrderStock(Order $order, array $item): void
    {
        $this->productService->removeStockFromWarehouse($item['product_id'], [
            'warehouse_id' => $order->warehouse_id,
            'bin_id' => null,
            'quantity' => $item['quantity'],
            'batch_number' => null,
            'serial_numbers' => null,
            'transaction_type' => 'sale',
            'reference_type' => $order->type === Order::TYPE_POS ? 'POSOrder' : 'SalesOrder',
            'reference_id' => $order->id,
            'notes' => "Stock deducted for order: {$order->order_no}"
        ]);
    }

    /**
     * Restore stock for cancelled/held order
     */
    private function restoreOrderStock(Order $order): void
    {
        foreach ($order->orderDetails as $detail) {
            $this->productService->addStockToWarehouse($detail->product_id, [
                'warehouse_id' => $order->warehouse_id,
                'bin_id' => null,
                'quantity' => $detail->quantity,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'return',
                'reference_type' => 'OrderCancellation',
                'reference_id' => $order->id,
                'notes' => "Stock restored from cancelled/held order: {$order->order_no}"
            ]);
        }
    }

    // ========================================
    // CALCULATION METHODS
    // ========================================

    /**
     * Calculate item total
     */
    private function calculateItemTotal(array $item): float
    {
        $quantity = $item['quantity'];
        $unitPrice = $item['unit_price'];
        $discount = $item['discount'] ?? 0;
        $tax = $item['tax'] ?? 0;

        $total = ($quantity * $unitPrice) - $discount + $tax;

        return round($total, 2);
    }

    /**
     * Calculate order totals
     */
    private function calculateTotals(array $items, array $data): array
    {
        $totalQuantities = 0;
        $subtotal = 0;

        foreach ($items as $item) {
            $totalQuantities += $item['quantity'];
            $subtotal += $this->calculateItemTotal($item);
        }

        $otherCharges = $data['other_charges'] ?? 0;
        $discountOnAll = $data['discount_on_all'] ?? 0;
        $roundOff = $data['round_off'] ?? 0;

        $grandTotal = $subtotal + $otherCharges - $discountOnAll + $roundOff;

        return [
            'total_quantities' => $totalQuantities,
            'subtotal' => round($subtotal, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * Determine payment status
     */
    private function determinePaymentStatus(float $grandTotal, float $paidAmount): int
    {
        if ($paidAmount <= 0) {
            return Order::PAYMENT_UNPAID;
        }

        if ($paidAmount >= $grandTotal) {
            return Order::PAYMENT_PAID;
        }

        return Order::PAYMENT_PARTIAL;
    }
}
