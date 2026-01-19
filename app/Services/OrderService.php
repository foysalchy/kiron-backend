<?php

namespace App\Services;

use App\Models\{Order, OrderDetail, OrderPayment, Product};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

class OrderService
{
    public function __construct(
        protected CouponService $couponService
    ) {}

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
        ->where('status', Order::STATUS_ON_HOLD)
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

            // Handle payments - both POS and Sales use order_payments table
            $totalPaid = 0;
            if (!empty($payments)) {
                $totalPaid = array_sum(array_column($payments, 'amount'));
            }

            $data['payment_amount'] = $totalPaid;
            $data['payment_status'] = $this->determinePaymentStatus($data['grand_total'], $totalPaid);

            // Create order (order_no auto-generated in boot method)
            $order = Order::create($data);

            // Create order details
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
                $product=Product::where('id',$item['product_id'])->first();
                $product->available_stock=$product->available_stock-$item['quantity'];
                $product->update();
            }

            // Create payments (for both POS and Sales if provided)
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
                'type' => $order->type
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

            $order->update(['status' => Order::STATUS_CANCELLED]);

            DB::commit();

            Log::info('Order cancelled', ['order_id' => $id]);
            LogHelper::custom('cancelled', 'order', $id, $order->company_id);

            return $order;
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

            $order->update(['status' => Order::STATUS_COMPLETED]);

            DB::commit();

            Log::info('Order completed', ['order_id' => $id]);
            LogHelper::custom('completed', 'order', $id, $order->company_id);

            return $order;
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

            $order->update([
                'status' => Order::STATUS_ON_HOLD,
                'hold_ref' => $ref,
            ]);

            DB::commit();

            Log::info('Order put on hold', ['order_id' => $id]);
            LogHelper::custom('on_hold', 'order', $id, $order->company_id);

            return $order;
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

            $order->update([
                'status' => Order::STATUS_PENDING,
                'hold_ref' => null,
            ]);

            DB::commit();

            Log::info('Order resumed from hold', ['order_id' => $id]);
            LogHelper::custom('resumed', 'order', $id, $order->company_id);

            return $order;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order resume failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to resume order');
        }
    }
}
