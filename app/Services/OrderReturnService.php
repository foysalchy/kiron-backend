<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{OrderReturn, OrderReturnDetail, OrderReturnPayment, Order, Party, Product, ProductStockLedger, ProductVariationStockLedger};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class OrderReturnService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

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
                'orderReturnDetails.variation.attributes.attributeGroup',
                'orderReturnDetails.variation.attributes.attributeValue',
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
            'actionLogs',
            'orderReturnDetails.product',
            'orderReturnDetails.variation.attributes.attributeGroup',
            'orderReturnDetails.variation.attributes.attributeValue',
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
            if (!$order->warehouse_id) {
                throw ApiException::badRequest('Order does not have a warehouse assigned');
            }

            $data['warehouse_id'] = $order->warehouse_id;
            $data['customer_id'] = $order->customer_id;

            // Calculate totals
            $totals = $this->calculateTotals($items, $data);
            $data = array_merge($data, $totals);

            $refundAmount = round((float) ($data['refund_amount'] ?? 0), 2);
            $paymentsTotal = round(array_sum(array_column($payments, 'amount')), 2);

            if ($paymentsTotal > $refundAmount) {
                throw ApiException::badRequest('Payment total cannot exceed refund amount');
            }

            // Create order return (return_no auto-generated)
            $orderReturn = OrderReturn::create($data);

            // Create return details with variation support
            foreach ($items as $item) {
                $itemTotal = $this->calculateItemTotal($item);

                OrderReturnDetail::create([
                    'order_return_id' => $orderReturn->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_group_id' => $item['tax_group_id'] ?? null,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            // Add returned stock to warehouse (if status is Cleared)
            if ($orderReturn->status === Status::Cleared->value) {
                $this->addReturnedStockToWarehouse($orderReturn);
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
            if ($orderReturn->status == Status::Cleared->value && $refundAmount > 0) {
                $unpaidRefund = round($refundAmount - $paymentsTotal, 2);


                if ($unpaidRefund > 0) {
                    if ($order->customer_id) {
                        Party::where('id', $order->customer_id)->increment('balance', $unpaidRefund);
                    }
                }
            }
            DB::commit();

            // Auto Double-Entry Voucher for Sales Return
            try {
                \App\Services\AutoAccountingService::postOrderReturnJournal($orderReturn);
            } catch (\Exception $accErr) {
                Log::warning("Order return auto-journal failed: " . $accErr->getMessage());
            }

            Log::info('Order return created successfully', [
                'order_return_id' => $orderReturn->id,
                'status' => $orderReturn->status
            ]);
            LogHelper::created('order_return', $orderReturn->id, $orderReturn->company_id, 'total quantities ' . $orderReturn->total_quantities . ' refund amount ' . $orderReturn->refund_amount);

            return $orderReturn->load([
                'warehouse',
                'customer',
                'order',
                'orderReturnDetails.product',
                'orderReturnDetails.variation.attributes.attributeGroup',
                'orderReturnDetails.variation.attributes.attributeValue',
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
            $payments = $data['payments'] ?? null;
            unset($data['items'], $data['payments']);

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
                        'variation_id' => $item['variation_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax_group_id' => $item['tax_group_id'] ?? null,
                        'tax' => $item['tax'] ?? 0,
                        'total' => $itemTotal,
                    ]);
                }
            }

            // If payments provided, replace old payments and adjust wallet balance
            if ($payments !== null) {
                $refundAmount = round((float) ($data['refund_amount'] ?? $orderReturn->refund_amount), 2);
                $newPaymentsTotal = round(array_sum(array_column($payments, 'amount')), 2);

                if ($newPaymentsTotal > $refundAmount) {
                    throw ApiException::badRequest('Payment total cannot exceed refund amount');
                }

                // Reverse the balance effect of the OLD payments (addPayment had decremented balance per payment)
                $oldPaymentsTotal = round(
                    (float) $orderReturn->orderReturnPayments()->sum('amount'),
                    2
                );
                if ($orderReturn->customer_id && $oldPaymentsTotal > 0) {
                    Party::where('id', $orderReturn->customer_id)->increment('balance', $oldPaymentsTotal);
                }

                // Delete old payments and create new ones
                $orderReturn->orderReturnPayments()->delete();

                foreach ($payments as $payment) {
                    OrderReturnPayment::create([
                        'order_return_id' => $orderReturn->id,
                        'amount' => $payment['amount'],
                        'payment_method' => $payment['payment_method'],
                        'reference_no' => $payment['reference_no'] ?? null,
                        'note' => $payment['note'] ?? null,
                    ]);
                }

                // Apply the balance effect of the NEW payments
                if ($orderReturn->customer_id && $newPaymentsTotal > 0) {
                    Party::where('id', $orderReturn->customer_id)->decrement('balance', $newPaymentsTotal);
                }
            }

            $orderReturn->update($data);

            DB::commit();

            // Auto Double-Entry Voucher
            try {
                \App\Services\AutoAccountingService::postOrderReturnJournal($orderReturn->fresh());
            } catch (\Exception $accErr) {
                Log::warning("Order return update auto-journal failed: " . $accErr->getMessage());
            }

            Log::info('Order return updated successfully', ['order_return_id' => $orderReturn->id]);
            LogHelper::updated('order_return', $orderReturn->id, $orderReturn->company_id, 'total quantities ' . $orderReturn->total_quantities . ' refund amount ' . $orderReturn->refund_amount);

            return $orderReturn->fresh([
                'warehouse',
                'customer',
                'order',
                'orderReturnDetails.product',
                'orderReturnDetails.variation.attributes.attributeGroup',
                'orderReturnDetails.variation.attributes.attributeValue',
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
     * Change status
     */
    public function changeStatus(int $id, int $status): OrderReturn
    {
        DB::beginTransaction();

        try {
            $orderReturn = $this->getOrderReturnById($id);
            $oldStatus = $orderReturn->status;

            // Block Cleared unless refund amount is fully paid
            if ($status === Status::Cleared->value) {
                $refundAmount = round((float) $orderReturn->refund_amount, 2);
                $paymentsTotal = round(
                    (float) $orderReturn->orderReturnPayments()->sum('amount'),
                    2
                );

                if ($paymentsTotal < $refundAmount) {
                    throw ApiException::badRequest(
                        'Cannot mark as Cleared: refund amount is not fully paid (paid ' . $paymentsTotal . ' of ' . $refundAmount . ')'
                    );
                }
            }

            // If changing from non-cleared to cleared, add stock
            if ($oldStatus !== Status::Cleared->value && $status === Status::Cleared->value) {
                $this->addReturnedStockToWarehouse($orderReturn);
            }

            // If changing from cleared to non-cleared, remove stock
            if ($oldStatus === Status::Cleared->value && $status !== Status::Cleared->value) {
                $this->removeReturnedStockFromWarehouse($orderReturn);
            }

            $getStatus = Status::from($status);
            $orderReturn->update([
                'status' => $getStatus->value
            ]);

            DB::commit();

            Log::info('Order return status changed', [
                'order_return_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $getStatus->label()
            ]);
            LogHelper::custom('status_changed', 'order_return', $id, $orderReturn->company_id, 'order return no: ' . $orderReturn->return_no . ' new status ' . $getStatus->label());

            return $orderReturn->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status change failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change status: ' . $e->getMessage());
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

            if ($orderReturn->isCancelled()) {
                throw ApiException::badRequest('Cannot add payment to cancelled return');
            }

            $amount = round((float) $paymentData['amount'], 2);

            OrderReturnPayment::create([
                'order_return_id' => $id,
                'amount' => $amount,
                'payment_method' => $paymentData['payment_method'],
                'reference_no' => $paymentData['reference_no'] ?? null,
                'note' => $paymentData['note'] ?? null,
            ]);

            if ($orderReturn->customer_id && $amount > 0) {
                Party::where('id', $orderReturn->customer_id)->decrement('balance', $amount);
            }

            DB::commit();

            Log::info('Payment added to return', ['order_return_id' => $id]);
            LogHelper::custom('payment_added', 'order_return', $id, $orderReturn->company_id, $orderReturn->return_no . ' receive payment ' .  $paymentData['amount']);

            return $orderReturn->fresh([
                'warehouse',
                'customer',
                'order',
                'orderReturnDetails.product',
                'orderReturnDetails.variation.attributes.attributeGroup',
                'orderReturnDetails.variation.attributes.attributeValue',
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
     * Add payment to return
     */
    public function modifyRefund(int $id, array $amount): OrderReturn
    {
        DB::beginTransaction();

        try {
            $orderReturn = $this->getOrderReturnById($id);

            // Cannot modify refund amount on cancelled return
            if ($orderReturn->isCancelled()) {
                throw ApiException::badRequest('Cannot modify refund amount on cancelled return');
            }
            if ($orderReturn->isCleared()) {
                throw ApiException::badRequest('Cannot modify refund amount on cleared return');
            }

            $newAmount = round((float) $amount['amount'], 2);

            if ($newAmount <= 0) {
                throw ApiException::badRequest('Refund amount must be greater than zero');
            }

            // New refund amount cannot be less than what's already been paid
            $paidSoFar = round(
                (float) $orderReturn->orderReturnPayments()->sum('amount'),
                2
            );

            if ($newAmount < $paidSoFar) {
                throw ApiException::badRequest(
                    'Refund amount cannot be less than the amount already paid (' . $paidSoFar . ')'
                );
            }

            $oldAmount = $orderReturn->refund_amount;
            $orderReturn->refund_amount = $newAmount;
            $orderReturn->update();

            LogHelper::custom('modify_refund_amount', 'order_return', $id, $orderReturn->company_id, $orderReturn->return_no . ' modify refund amount ' . $oldAmount . ' to ' . $newAmount);

            DB::commit();

            Log::info('Refund amount modified', ['order_return_id' => $id]);

            return $orderReturn->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Modify refund amount failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to modify refund amount');
        }
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
            LogHelper::deleted('order_return', $id, $orderReturn->company_id, $orderReturn->return_no);

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

    /**
     * Restore order return
     */
    public function restoreOrderReturn(int $id): OrderReturn
    {
        DB::beginTransaction();

        try {
            $orderReturn = OrderReturn::onlyTrashed()->find($id);

            if (!$orderReturn) {
                throw ApiException::notFound('Order Return');
            }

            $orderReturn->restore();

            DB::commit();

            Log::info('Order return restored successfully', ['order_return_id' => $id]);
            LogHelper::custom('restored', 'order_return', $id, $orderReturn->company_id, $orderReturn->return_no);

            return $orderReturn;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Order return restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore order return');
        }
    }

    /**
     * Force delete order return
     */
    public function forceDeleteOrderReturn(int $id): bool
    {
        DB::beginTransaction();

        try {
            $orderReturn = OrderReturn::withTrashed()->find($id);

            if (!$orderReturn) {
                throw ApiException::notFound('Order Return');
            }
            // Only pending returns can be deleted
            if (!$orderReturn->isPending()) {
                throw ApiException::badRequest('Only pending returns can be deleted');
            }

            // Delete details
            OrderReturnDetail::where('order_return_id', $id)->forceDelete();

            // Delete payments
            OrderReturnPayment::where('order_return_id', $id)->forceDelete();

            // Delete related stock ledgers for single products
            ProductStockLedger::where('reference_type', 'OrderReturn')
                ->where('reference_id', $id)
                ->forceDelete();

            // Delete related stock ledgers for variation products
            ProductVariationStockLedger::where('reference_type', 'OrderReturn')
                ->where('reference_id', $id)
                ->forceDelete();

            $companyId = $orderReturn->company_id;
            $orderReturn->forceDelete();

            DB::commit();

            Log::info('Order return permanently deleted', ['order_return_id' => $id]);
            LogHelper::custom('force_deleted', 'order_return', $id, $companyId, $orderReturn->return_no);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Permanent order return deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete order return');
        }
    }

    /**
     * Add returned stock to warehouse
     * Handles both single and variation products
     */
    private function addReturnedStockToWarehouse(OrderReturn $orderReturn): void
    {
        foreach ($orderReturn->orderReturnDetails as $detail) {
            $product = $detail->product ?? Product::find($detail->product_id);

            if (!$product || !$product->manage_stock) {
                Log::info('Skipping return stock addition: manage_stock is off', [
                    'order_return_id' => $orderReturn->id,
                    'product_id' => $detail->product_id,
                ]);
                continue;
            }
            $stockData = [
                'warehouse_id' => $orderReturn->warehouse_id,
                'bin_id' => null,
                'quantity' => $detail->quantity,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'return',
                'reference_type' => 'OrderReturn',
                'reference_id' => $orderReturn->id,
                'notes' => "Stock returned from order: {$orderReturn->order->order_no} - Return: {$orderReturn->return_no}"
            ];

            // Add variation_id if exists
            if ($detail->variation_id) {
                $stockData['variation_id'] = $detail->variation_id;
            }

            $this->productService->addStockToWarehouse($detail->product_id, $stockData);

            Log::info('Stock added for returned item', [
                'order_return_id' => $orderReturn->id,
                'product_id' => $detail->product_id,
                'variation_id' => $detail->variation_id,
                'quantity' => $detail->quantity
            ]);
        }
    }

    /**
     * Remove returned stock from warehouse (Status change from Cleared to other)
     * Handles both single and variation products
     */
    private function removeReturnedStockFromWarehouse(OrderReturn $orderReturn): void
    {
        foreach ($orderReturn->orderReturnDetails as $detail) {
            $product = $detail->product ?? Product::find($detail->product_id);

            if (!$product || !$product->manage_stock) {
                Log::info('Skipping return stock addition: manage_stock is off', [
                    'order_return_id' => $orderReturn->id,
                    'product_id' => $detail->product_id,
                ]);
                continue;
            }
            $stockData = [
                'warehouse_id' => $orderReturn->warehouse_id,
                'bin_id' => null,
                'quantity' => $detail->quantity,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'adjustment',
                'reference_type' => 'OrderReturnReversal',
                'reference_id' => $orderReturn->id,
                'notes' => "Stock removed - Return status changed from Cleared: {$orderReturn->return_no}"
            ];

            // Add variation_id if exists
            if ($detail->variation_id) {
                $stockData['variation_id'] = $detail->variation_id;
            }

            $this->productService->removeStockFromWarehouse($detail->product_id, $stockData);

            Log::info('Stock removed for return status change', [
                'order_return_id' => $orderReturn->id,
                'product_id' => $detail->product_id,
                'variation_id' => $detail->variation_id,
                'quantity' => $detail->quantity
            ]);
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
}
