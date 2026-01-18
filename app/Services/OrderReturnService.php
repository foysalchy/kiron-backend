<?php

namespace App\Services;

use App\Models\{OrderReturn, OrderReturnDetail, OrderReturnPayment, Order};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class OrderReturnService
{
    /**
     * Get all order returns
     */
    public function getAllOrderReturns(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = OrderReturn::with([
                'warehouse',
                'customer',
                'order',
                'orderReturnDetails.product',
                'orderReturnPayments'
            ]);

            if (isset($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (isset($filters['customer_id'])) {
                $query->where('customer_id', $filters['customer_id']);
            }

            if (isset($filters['order_id'])) {
                $query->where('order_id', $filters['order_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('return_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('return_date', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $query->where('return_no', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'return_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching order returns: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch order returns');
        }
    }

    /**
     * Get order return by ID
     */
    public function getOrderReturnById(int $id): OrderReturn
    {
        $return = OrderReturn::with([
            'warehouse',
            'customer',
            'order',
            'orderReturnDetails.product',
            'orderReturnPayments'
        ])->find($id);

        if (!$return) {
            throw ApiException::notFound('Order Return');
        }

        return $return;
    }

    /**
     * Create order return
     */
    public function createOrderReturn(array $data): OrderReturn
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            $payments = $data['payments'] ?? [];

            unset($data['items'], $data['payments']);

            // Get order details
            $order = Order::find($data['order_id']);
            if (!$order) {
                throw ApiException::notFound('Order');
            }
            $data['warehouse_id'] = $order->warehouse_id;
            $data['customer_id'] = $order->customer_id;

            // Calculate totals
            $totals = $this->calculateTotals($items, $data);
            $data = array_merge($data, $totals);

            // Calculate refund amount
            $totalRefund = 0;
            if (!empty($payments)) {
                $totalRefund = array_sum(array_column($payments, 'amount'));
            }
            $data['refund_amount'] = $totalRefund;

            // Create order return (return_no auto-generated)
            $orderReturn = OrderReturn::create($data);

            // Create return details
            foreach ($items as $item) {
                $itemTotal = $this->calculateItemTotal($item);

                OrderReturnDetail::create([
                    'order_return_id' => $orderReturn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            // Create payments if provided
            if (!empty($payments)) {
                foreach ($payments as $payment) {
                    OrderReturnPayment::create([
                        'order_return_id' => $orderReturn->id,
                        'amount' => $payment['amount'],
                        'payment_method' => $payment['payment_method'],
                        'reference_no' => $payment['reference_no'] ?? null,
                        'note' => $payment['note'] ?? null,
                    ]);
                }
            }

            DB::commit();

            Log::info('Order return created successfully', ['order_return_id' => $orderReturn->id]);
            LogHelper::created('order_return', $orderReturn->id, $orderReturn->company_id);

            return $orderReturn->load([
                'warehouse',
                'customer',
                'order',
                'orderReturnDetails.product',
                'orderReturnPayments'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order return creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create order return: ' . $e->getMessage());
        }
    }

    /**
     * Update order return
     */
    public function updateOrderReturn(int $id, array $data): OrderReturn
    {
        DB::beginTransaction();

        try {
            $orderReturn = $this->getOrderReturnById($id);

            // Cannot update cleared or cancelled returns
            if ($orderReturn->isCleared() || $orderReturn->isCancelled()) {
                throw ApiException::badRequest('Cannot update cleared or cancelled return');
            }

            $items = $data['items'] ?? null;
            unset($data['items']);

            // If items provided, recalculate totals
            if ($items) {
                $totals = $this->calculateTotals($items, $data);
                $data = array_merge($data, $totals);

                // Delete old details and create new ones
                $orderReturn->orderReturnDetails()->delete();

                foreach ($items as $item) {
                    $itemTotal = $this->calculateItemTotal($item);

                    OrderReturnDetail::create([
                        'order_return_id' => $orderReturn->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax' => $item['tax'] ?? 0,
                        'total' => $itemTotal,
                    ]);
                }
            }

            $orderReturn->update($data);

            DB::commit();

            Log::info('Order return updated successfully', ['order_return_id' => $orderReturn->id]);
            LogHelper::updated('order_return', $orderReturn->id, $orderReturn->company_id);

            return $orderReturn->fresh([
                'warehouse',
                'customer',
                'order',
                'orderReturnDetails.product',
                'orderReturnPayments'
            ]);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order return update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update order return');
        }
    }

    /**
     * Add payment to return
     */
    public function addPayment(int $id, array $paymentData): OrderReturn
    {
        DB::beginTransaction();

        try {
            $orderReturn = $this->getOrderReturnById($id);

            // Cannot add payment to cancelled return
            if ($orderReturn->isCancelled()) {
                throw ApiException::badRequest('Cannot add payment to cancelled return');
            }

            // Create payment
            OrderReturnPayment::create([
                'order_return_id' => $id,
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'],
                'reference_no' => $paymentData['reference_no'] ?? null,
                'note' => $paymentData['note'] ?? null,
            ]);

            // Update refund amount
            $totalRefund = $orderReturn->orderReturnPayments()->sum('amount') + $paymentData['amount'];
            $orderReturn->update(['refund_amount' => $totalRefund]);

            DB::commit();

            Log::info('Payment added to return', ['order_return_id' => $id]);
            LogHelper::custom('payment_added', 'order_return', $id, $orderReturn->company_id);

            return $orderReturn->fresh([
                'warehouse',
                'customer',
                'order',
                'orderReturnDetails.product',
                'orderReturnPayments'
            ]);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Add payment failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to add payment');
        }
    }

    /**
     * Change status
     */
    public function changeStatus(int $id, int $status): OrderReturn
    {
        try {
            $orderReturn = $this->getOrderReturnById($id);
            $orderReturn->update(['status' => $status]);

            Log::info('Order return status changed', ['order_return_id' => $id, 'status' => $status]);
            LogHelper::custom('status_changed', 'order_return', $id, $orderReturn->company_id);

            return $orderReturn;
        } catch (\Exception $e) {
            Log::error('Status change failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change status');
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
     * Calculate totals
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
        $couponDiscount = $data['coupon_discount'] ?? 0;
        $roundOff = $data['round_off'] ?? 0;

        $grandTotal = $subtotal + $otherCharges - $discountOnAll - $couponDiscount + $roundOff;

        return [
            'total_quantities' => $totalQuantities,
            'subtotal' => round($subtotal, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * Delete return
     */
    public function deleteOrderReturn(int $id): bool
    {
        DB::beginTransaction();

        try {
            $orderReturn = $this->getOrderReturnById($id);

            // Only pending returns can be deleted
            if (!$orderReturn->isPending()) {
                throw ApiException::badRequest('Only pending returns can be deleted');
            }

            $orderReturn->delete();

            DB::commit();

            Log::info('Order return deleted successfully', ['order_return_id' => $id]);
            LogHelper::deleted('order_return', $id, $orderReturn->company_id);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order return deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete order return');
        }
    }
}
