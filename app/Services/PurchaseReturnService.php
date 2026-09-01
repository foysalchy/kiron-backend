<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{Party, Product, Purchase, PurchasePaymentReturn, PurchaseReturn, PurchaseReturnDetail, ProductStockLedger, ProductVariationStockLedger};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class PurchaseReturnService
{
    protected ProductService $productService;
    protected PartyDueService $partyDueService;

    public function __construct(ProductService $productService, PartyDueService $partyDueService)
    {
        $this->productService = $productService;
        $this->partyDueService = $partyDueService;
    }

    /**
     * Get all purchase returns with optional pagination
     */
    public function getAllPurchaseReturns(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = PurchaseReturn::with([
                'purchase',
                'supplier',
                'purchaseReturnDetails.product',
                'purchaseReturnDetails.variation.attributes.attributeGroup',
                'purchaseReturnDetails.variation.attributes.attributeValue'
            ]);

            if (isset($filters['purchase_id'])) {
                $query->where('purchase_id', $filters['purchase_id']);
            }

            if (isset($filters['supplier_id'])) {
                $query->where('supplier_id', $filters['supplier_id']);
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
            Log::error('Error fetching purchase returns: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch purchase returns');
        }
    }

    /**
     * Get purchase return by ID
     */
    public function getPurchaseReturnById(int $id): PurchaseReturn
    {
        $purchaseReturn = PurchaseReturn::with([
            'purchase',
            'supplier',
            'purchaseReturnPayments',
            'purchaseReturnDetails.product',
            'purchaseReturnDetails.variation.attributes.attributeGroup',
            'purchaseReturnDetails.variation.attributes.attributeValue'
        ])->find($id);

        if (!$purchaseReturn) {
            throw ApiException::notFound('Purchase Return');
        }

        return $purchaseReturn;
    }

    /**
     * Get purchase products with variations
     */
    public function purchaseProducts(int $purchaseId)
    {
        $purchase = Purchase::with([
            'purchaseDetails.product',
            'purchaseDetails.variation.attributes.attributeGroup',
            'purchaseDetails.variation.attributes.attributeValue'
        ])->find($purchaseId);

        if (!$purchase) {
            throw ApiException::notFound('Purchase');
        }

        return $purchase;
    }
    /**
     * Create a new purchase return
     */
    public function createPurchaseReturn(array $data): PurchaseReturn
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            $payments = $data['payments'] ?? [];
            unset($data['items'], $data['payments']);

            // Get purchase details
            $purchase = Purchase::find($data['purchase_id']);
            if (!$purchase) {
                throw ApiException::notFound('Purchase');
            }

            $data['warehouse_id'] = $purchase->warehouse_id;
            $data['supplier_id'] = $purchase->supplier_id;

            // Calculate totals (grand_total becomes the refund amount owed for this return)
            $totals = $this->calculateTotals($items, $data);
            $data = array_merge($data, $totals);


            $refundAmount = round((float) $data['refund_amount'], 2);
            $paymentsTotal = round(array_sum(array_column($payments, 'amount')), 2);

            if ($paymentsTotal > $refundAmount) {
                throw ApiException::badRequest('Payment total cannot exceed refund amount');
            }

            // Create purchase return
            $purchaseReturn = PurchaseReturn::create($data);

            // Create purchase return details
            foreach ($items as $item) {
                $itemTotal = $this->calculateItemTotal($item);

                PurchaseReturnDetail::create([
                    'purchase_return_id' => $purchaseReturn->id,
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

            // Remove returned stock from warehouse (if status is Cleared)
            if ($purchaseReturn->status == Status::Cleared->value) {
                $this->removeReturnedStockFromWarehouse($purchaseReturn);
            }

            // Create payments if provided
            if (!empty($payments)) {
                foreach ($payments as $payment) {
                    PurchasePaymentReturn::create([
                        'purchase_return_id' => $purchaseReturn->id,
                        'amount' => $payment['amount'],
                        'payment_method' => $payment['payment_method'],
                        'reference_no' => $payment['reference_no'] ?? null,
                        'note' => $payment['note'] ?? null,
                    ]);
                }

                // Payments received against this return increase supplier's due_amount
                if ($purchaseReturn->supplier_id && $paymentsTotal > 0) {
                    $this->partyDueService->recalculatePartyDue($purchaseReturn->supplier_id);
                }
            }

            DB::commit();

            // Auto Double-Entry Voucher for Purchase Return
            try {
                \App\Services\AutoAccountingService::postPurchaseReturnJournal($purchaseReturn);
            } catch (\Exception $accErr) {
                Log::warning("Purchase return auto-journal failed: " . $accErr->getMessage());
            }

            Log::info('Purchase return created successfully', [
                'purchase_return_id' => $purchaseReturn->id,
                'status' => $purchaseReturn->status
            ]);
            LogHelper::created('purchase_return', $purchaseReturn->id, $purchaseReturn->company_id, 'total quantities ' . $purchaseReturn->total_quantities . ' refund amount ' . $purchaseReturn->refund_amount);

            return $purchaseReturn;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase return creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create purchase return: ' . $e->getMessage());
        }
    }

    /**
     * Update purchase return
     */
    public function updatePurchaseReturn(int $id, array $data): PurchaseReturn
    {
        DB::beginTransaction();

        try {
            $purchaseReturn = $this->getPurchaseReturnById($id);

            // Check if purchase return can be edited
            if ($purchaseReturn->isCancelled()) {
                throw ApiException::badRequest('Cannot update cancelled purchase return');
            }

            if ($purchaseReturn->isCleared()) {
                throw ApiException::badRequest('Cannot update cleared purchase return');
            }

            $items = $data['items'] ?? null;
            $payments = $data['payments'] ?? null;
            unset($data['items'], $data['payments']);

            // If items are provided, recalculate totals
            if ($items) {
                $totals = $this->calculateTotals($items, $data);
                $data = array_merge($data, $totals);

                // Delete old details and create new ones
                $purchaseReturn->purchaseReturnDetails()->delete();

                foreach ($items as $item) {
                    $itemTotal = $this->calculateItemTotal($item);

                    PurchaseReturnDetail::create([
                        'purchase_return_id' => $purchaseReturn->id,
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

            // If payments provided, replace old payments and adjust supplier due_amount
            if ($payments !== null) {
                $refundAmount = round((float) ($data['refund_amount'] ?? $purchaseReturn->refund_amount), 2);
                $newPaymentsTotal = round(array_sum(array_column($payments, 'amount')), 2);

                if ($newPaymentsTotal > $refundAmount) {
                    throw ApiException::badRequest('Payment total cannot exceed refund amount');
                }

                // Reverse the due_amount effect of the OLD payments
                $oldPaymentsTotal = round(
                    (float) $purchaseReturn->purchaseReturnPayments()->sum('amount'),
                    2
                );
                if ($purchaseReturn->supplier_id && $oldPaymentsTotal > 0) {
                    $this->partyDueService->recalculatePartyDue($purchaseReturn->supplier_id);
                }

                // Delete old payments and create new ones
                $purchaseReturn->purchaseReturnPayments()->delete();

                foreach ($payments as $payment) {
                    PurchasePaymentReturn::create([
                        'purchase_return_id' => $purchaseReturn->id,
                        'amount' => $payment['amount'],
                        'payment_method' => $payment['payment_method'],
                        'reference_no' => $payment['reference_no'] ?? null,
                        'note' => $payment['note'] ?? null,
                    ]);
                }

                // Apply the due_amount effect of the NEW payments
                if ($purchaseReturn->supplier_id && $newPaymentsTotal > 0) {
                    $this->partyDueService->recalculatePartyDue($purchaseReturn->supplier_id);
                }
            }

            $purchaseReturn->update($data);

            DB::commit();

            // Auto Double-Entry Voucher
            try {
                \App\Services\AutoAccountingService::postPurchaseReturnJournal($purchaseReturn->fresh());
            } catch (\Exception $accErr) {
                Log::warning("Purchase return update auto-journal failed: " . $accErr->getMessage());
            }

            Log::info('Purchase return updated successfully', ['purchase_return_id' => $purchaseReturn->id]);
            LogHelper::updated('purchase_return', $purchaseReturn->id, $purchaseReturn->company_id, 'total quantities ' . $purchaseReturn->total_quantities . ' refund amount ' . $purchaseReturn->refund_amount);

            return $purchaseReturn->fresh(['purchaseReturnPayments']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase return update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update purchase return');
        }
    }

    /**
     * Change purchase return status
     */
    public function changeStatus(int $id, int $status): PurchaseReturn
    {
        DB::beginTransaction();

        try {
            $purchaseReturn = $this->getPurchaseReturnById($id);
            $oldStatus = $purchaseReturn->status;

            // Block Cleared unless refund amount is fully paid
            if ($status === Status::Cleared->value) {
                $refundAmount = round((float) $purchaseReturn->refund_amount, 2);
                $paymentsTotal = round(
                    (float) $purchaseReturn->purchaseReturnPayments()->sum('amount'),
                    2
                );

                if ($paymentsTotal < $refundAmount) {
                    throw ApiException::badRequest(
                        'Cannot mark as Cleared: refund amount is not fully paid (paid ' . $paymentsTotal . ' of ' . $refundAmount . ')'
                    );
                }
            }

            // If changing from non-cleared to cleared, remove stock
            if ($oldStatus !== Status::Cleared->value && $status === Status::Cleared->value) {
                $this->removeReturnedStockFromWarehouse($purchaseReturn);
            }

            // If changing from cleared to non-cleared, restore stock
            if ($oldStatus === Status::Cleared->value && $status !== Status::Cleared->value) {
                $this->addReturnedStockToWarehouse($purchaseReturn);
            }

            $getStatus = Status::from($status);
            $purchaseReturn->update([
                'status' => $getStatus->value
            ]);

            DB::commit();

            Log::info('Purchase return status changed', [
                'purchase_return_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);
            LogHelper::custom('status_changed', 'purchase_return', $id, $purchaseReturn->company_id, 'purchase return no: ' . $purchaseReturn->return_no . ' new status ' . $getStatus->label());

            return $purchaseReturn->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase return status change failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change status: ' . $e->getMessage());
        }
    }

    /**
     * Add payment to return
     */
    public function addPayment(int $id, array $paymentData): PurchaseReturn
    {
        DB::beginTransaction();

        try {
            $purchaseReturn = $this->getPurchaseReturnById($id);

            // Cannot add payment to cancelled or cleared return
            if ($purchaseReturn->isCancelled()) {
                throw ApiException::badRequest('Cannot add payment to cancelled return');
            }
            if ($purchaseReturn->isCleared()) {
                throw ApiException::badRequest('Cannot add payment to cleared return');
            }

            $amount = round((float) $paymentData['amount'], 2);

            if ($amount <= 0) {
                throw ApiException::badRequest('Payment amount must be greater than zero');
            }

            $refundAmount = round((float) $purchaseReturn->refund_amount, 2);
            $paidSoFar = round(
                (float) $purchaseReturn->purchaseReturnPayments()->sum('amount'),
                2
            );

            if ($paidSoFar + $amount > $refundAmount) {
                throw ApiException::badRequest('Payment amount exceeds remaining refund amount');
            }

            // Create payment
            PurchasePaymentReturn::create([
                'purchase_return_id' => $id,
                'amount' => $amount,
                'payment_method' => $paymentData['payment_method'],
                'reference_no' => $paymentData['reference_no'] ?? null,
                'note' => $paymentData['note'] ?? null,
            ]);

            // Payment received against this return increases supplier's due_amount
            if ($purchaseReturn->supplier_id) {
                $this->partyDueService->recalculatePartyDue($purchaseReturn->supplier_id);
            }

            DB::commit();

            Log::info('Payment added to purchase return', ['purchase_return_id' => $id]);
            LogHelper::custom('payment_added', 'purchase_return', $id, $purchaseReturn->company_id, $purchaseReturn->return_no . ' receive payment ' . $amount);

            return $purchaseReturn->fresh(['purchaseReturnPayments']);
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
     * Delete purchase return (soft delete)
     */
    public function deletePurchaseReturn(int $id): bool
    {
        DB::beginTransaction();

        try {
            $purchaseReturn = $this->getPurchaseReturnById($id);

            // Only pending purchase returns can be deleted
            if (!$purchaseReturn->isDraft()) {
                throw ApiException::badRequest('Only draft purchase returns can be deleted');
            }

            $purchaseReturn->delete();

            DB::commit();

            Log::info('Purchase return deleted successfully', ['purchase_return_id' => $id]);
            LogHelper::deleted('purchase_return', $id, $purchaseReturn->company_id, $purchaseReturn->return_no);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase return deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete purchase return');
        }
    }

    /**
     * Restore purchase return
     */
    public function restorePurchaseReturn(int $id): PurchaseReturn
    {
        DB::beginTransaction();

        try {
            $purchaseReturn = PurchaseReturn::onlyTrashed()->find($id);

            if (!$purchaseReturn) {
                throw ApiException::notFound('Purchase Return');
            }

            $purchaseReturn->restore();

            DB::commit();

            Log::info('Purchase return restored successfully', ['purchase_return_id' => $id]);
            LogHelper::custom('restored', 'purchase_return', $id, $purchaseReturn->company_id, $purchaseReturn->return_no);

            return $purchaseReturn;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase return restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore purchase return');
        }
    }

    /**
     * Force delete purchase return
     */
    public function forceDeletePurchaseReturn(int $id): bool
    {
        DB::beginTransaction();

        try {
            $purchaseReturn = PurchaseReturn::withTrashed()->find($id);

            if (!$purchaseReturn) {
                throw ApiException::notFound('Purchase Return');
            }

            // Only pending returns can be deleted
            if (!$purchaseReturn->isDraft()) {
                throw ApiException::badRequest('Only draft returns can be deleted');
            }

            // Delete details
            PurchaseReturnDetail::where('purchase_return_id', $id)->forceDelete();

            // Delete payments
            PurchasePaymentReturn::where('purchase_return_id', $id)->forceDelete();

            // Delete related stock ledgers for single products
            ProductStockLedger::where('reference_type', 'PurchaseReturn')
                ->where('reference_id', $id)
                ->forceDelete();

            // Delete related stock ledgers for variation products
            ProductVariationStockLedger::where('reference_type', 'PurchaseReturn')
                ->where('reference_id', $id)
                ->forceDelete();

            $companyId = $purchaseReturn->company_id;
            $purchaseReturn->forceDelete();

            DB::commit();

            Log::info('Purchase return permanently deleted', ['purchase_return_id' => $id]);
            LogHelper::custom('force_deleted', 'purchase_return', $id, $companyId, $purchaseReturn->return_no);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Permanent purchase return deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete purchase return');
        }
    }

    // ========================================
    // STOCK MANAGEMENT PRIVATE METHODS
    // ========================================

    /**
     * Remove returned stock from warehouse (Deduct from inventory)
     * Handles both single and variation products
     */
    private function removeReturnedStockFromWarehouse(PurchaseReturn $purchaseReturn): void
    {
        foreach ($purchaseReturn->purchaseReturnDetails as $detail) {
            $product = $detail->product ?? Product::find($detail->product_id);

            if (!$product || !$product->manage_stock) {
                Log::info('Skipping return stock addition: manage_stock is off', [
                    'order_return_id' => $purchaseReturn->id,
                    'product_id' => $detail->product_id,
                ]);
                continue;
            }
            $stockData = [
                'warehouse_id' => $purchaseReturn->purchase->warehouse_id,
                'bin_id' => null,
                'quantity' => $detail->quantity,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'return',
                'reference_type' => 'PurchaseReturn',
                'reference_id' => $purchaseReturn->id,
                'notes' => "Stock returned to supplier - Purchase: {$purchaseReturn->purchase->reference_no} - Return: {$purchaseReturn->return_no}"
            ];

            // ✅ Add variation_id if exists
            if ($detail->variation_id) {
                $stockData['variation_id'] = $detail->variation_id;
            }

            $this->productService->removeStockFromWarehouse($detail->product_id, $stockData);

            Log::info('Stock removed for returned item', [
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $detail->product_id,
                'variation_id' => $detail->variation_id,
                'quantity' => $detail->quantity
            ]);
        }
    }

    /**
     * Add returned stock back to warehouse (Status change from Cleared to other)
     * Handles both single and variation products
     */
    private function addReturnedStockToWarehouse(PurchaseReturn $purchaseReturn): void
    {
        foreach ($purchaseReturn->purchaseReturnDetails as $detail) {
            $product = $detail->product ?? Product::find($detail->product_id);

            if (!$product || !$product->manage_stock) {
                Log::info('Skipping return stock addition: manage_stock is off', [
                    'order_return_id' => $purchaseReturn->id,
                    'product_id' => $detail->product_id,
                ]);
                continue;
            }
            $stockData = [
                'warehouse_id' => $purchaseReturn->purchase->warehouse_id,
                'bin_id' => null,
                'quantity' => $detail->quantity,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'adjustment',
                'reference_type' => 'PurchaseReturnReversal',
                'reference_id' => $purchaseReturn->id,
                'notes' => "Stock restored - Return status changed from Cleared: {$purchaseReturn->return_no}"
            ];

            // ✅ Add variation_id if exists
            if ($detail->variation_id) {
                $stockData['variation_id'] = $detail->variation_id;
            }

            $this->productService->addStockToWarehouse($detail->product_id, $stockData);

            Log::info('Stock restored for return status change', [
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $detail->product_id,
                'variation_id' => $detail->variation_id,
                'quantity' => $detail->quantity
            ]);
        }
    }

    // ========================================
    // CALCULATION METHODS
    // ========================================

    /**
     * Calculate item total from purchase detail
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
     * Calculate purchase return totals
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
