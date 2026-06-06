<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{Purchase, PurchaseDetail, Product, ProductStockLedger, ProductVariationStockLedger, PurchasePayment};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class PurchaseService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Get all purchases with optional pagination
     */
    public function getAllPurchases(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Purchase::with([
                'warehouse',
                'supplier',
                'purchaseDetails.product',
                'purchaseDetails.variation.attributes.attributeGroup',
                'purchaseDetails.variation.attributes.attributeValue'
            ])->withSum('payments', 'amount');

            if (isset($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (isset($filters['supplier_id'])) {
                $query->where('supplier_id', $filters['supplier_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['payment_status'])) {
                $query->where('payment_status', $filters['payment_status']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('purchase_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('purchase_date', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $query->where('reference_no', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching purchases: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch purchases');
        }
    }

    /**
     * Get purchase by ID
     */
    public function getPurchaseById(int $id): Purchase
    {
        $purchase = Purchase::with([
            'warehouse',
            'supplier',
            'payments',
            'purchaseDetails.product',
            'purchaseDetails.variation.attributes.attributeGroup',
            'purchaseDetails.variation.attributes.attributeValue'
        ])->withSum('payments', 'amount')
            ->findOrFail($id);

        if (!$purchase) {
            throw ApiException::notFound('Purchase');
        }

        return $purchase;
    }

    /**
     * Create a new purchase
     */
    public function createPurchase(array $data): Purchase
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            unset($data['items']);

            // Calculate totals
            $totals = $this->calculateTotals($items, $data);
            $data = array_merge($data, $totals);

            // Determine payment status
            $data['payment_status'] = $this->determinePaymentStatus(
                $data['grand_total'],
                $data['payment_amount'] ?? 0
            );

            // Create purchase
            $purchase = Purchase::create($data);

            // Create purchase details
            foreach ($items as $item) {
                $itemTotal = $this->calculateItemTotal($item);

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null, // ✅ Add variation support
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'unit_cost' => $item['unit_cost'],
                    'discount' => $item['discount'] ?? 0,
                    'tax_group_id' => $item['tax_group_id'] ?? null,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            // If purchase is completed, add stock to warehouse
            if (isset($data['status']) && $data['status'] == Status::Completed->value) {
                $this->addPurchaseStockToWarehouse($purchase);
            }

            DB::commit();

            Log::info('Purchase created successfully', ['purchase_id' => $purchase->id]);
            LogHelper::created('purchase', $purchase->id, $purchase->company_id, 'Total Quantities : ' . $purchase->total_quantities);

            return $purchase;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create purchase: ' . $e->getMessage());
        }
    }

    /**
     * Update purchase
     */
    public function updatePurchase(int $id, array $data): Purchase
    {


        DB::beginTransaction();

        try {
            $purchase = $this->getPurchaseById($id);

            // Check if purchase can be edited
            if ($purchase->isCancelled()) {
                throw ApiException::badRequest('Cannot update cancelled purchase');
            }

            // Store old status
            $oldStatus = $purchase->status;

            $items = $data['items'] ?? null;
            unset($data['items']);

            // If items are provided, recalculate totals
            if ($items) {
                // If purchase was completed, we need to reverse old stock first
                if ($oldStatus == Status::Completed->value) {
                    $this->removePurchaseStockFromWarehouse($purchase);
                }

                $totals = $this->calculateTotals($items, $data);
                $data = array_merge($data, $totals);

                // Update payment status
                $data['payment_status'] = $this->determinePaymentStatus(
                    $data['grand_total'],
                    $data['payment_amount'] ?? $purchase->payment_amount ?? 0
                );

                // Delete old details and create new ones
                $purchase->purchaseDetails()->delete();

                foreach ($items as $item) {
                    $itemTotal = $this->calculateItemTotal($item);

                    PurchaseDetail::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                        'unit_cost' => $item['unit_cost'],
                        'discount' => $item['discount'] ?? 0,
                        'tax_group_id' => $item['tax_group_id'] ?? null,
                        'tax' => $item['tax'] ?? 0,
                        'total' => $itemTotal,
                    ]);
                }

                // If new status is completed, add new stock
                if (($data['status'] ?? $oldStatus) == Status::Completed->value) {
                    $purchase->update($data); // Update first to get new items
                    $purchase->refresh();
                    $this->addPurchaseStockToWarehouse($purchase);
                }
            } else if (isset($data['payment_amount'])) {
                // Only payment updated
                $data['payment_status'] = $this->determinePaymentStatus(
                    $purchase->grand_total,
                    $data['payment_amount']
                );
            }

            $purchase->update($data);

            DB::commit();

            Log::info('Purchase updated successfully', ['purchase_id' => $purchase->id]);
            LogHelper::updated('purchase', $purchase->id, $purchase->company_id, 'Total Quantities : ' . $purchase->total_quantities);

            return $purchase;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update purchase: ' . $e->getMessage());
        }
    }

    /**
     * Delete purchase (soft delete)
     */
    public function deletePurchase(int $id): bool
    {
        DB::beginTransaction();

        try {
            $purchase = $this->getPurchaseById($id);

            // Only draft purchases can be deleted
            if (!$purchase->isDraft()) {
                throw ApiException::badRequest('Only draft purchases can be deleted');
            }

            $purchase->delete();

            DB::commit();

            Log::info('Purchase deleted successfully', ['purchase_id' => $id]);
            LogHelper::deleted('purchase', $id, $purchase->company_id);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete purchase');
        }
    }

    /**
     * Change purchase status
     */
    public function changeStatus(int $id, int $status): Purchase
    {
        DB::beginTransaction();

        try {
            $purchase = $this->getPurchaseById($id);
            $oldStatus = $purchase->status;

            // If changing from draft to completed, add stock
            if ($oldStatus == Status::Draft->value && $status == Status::Completed->value) {
                $this->addPurchaseStockToWarehouse($purchase);
            }

            // If changing from completed to draft/cancelled, remove stock
            if ($oldStatus == Status::Completed->value && $status != Status::Completed->value) {
                $this->removePurchaseStockFromWarehouse($purchase);
            }

            $getStatus = Status::from($status);
            $purchase->update([
                'status' => $getStatus->value
            ]);

            DB::commit();

            Log::info('Purchase status changed', [
                'purchase_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);
            LogHelper::custom('status_changed', 'purchase', $id, $purchase->company_id, 'purchase status marked as ' . $getStatus->label());

            return $purchase->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase status change failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change status: ' . $e->getMessage());
        }
    }

    /**
     * Add payment
     */
    public function addPayment(int $id, array $paymentData): Purchase
    {
        DB::beginTransaction();

        try {
            $purchase = $this->getPurchaseById($id);

            PurchasePayment::create([
                'purchase_id'  => $purchase->id,
                'amount'       => $paymentData['amount'],
                'payment_date' => $paymentData['payment_date'],
                'payment_type' => $paymentData['payment_type'] ?? null,
                'account'      => $paymentData['account'] ?? null,
                'reference_no' => $paymentData['reference_no'] ?? null,
                'note'         => $paymentData['note'] ?? null,
            ]);

            $purchase->updatePaymentStatus();

            DB::commit();

            Log::info('Payment added to purchase', [
                'purchase_id' => $id,
                'amount'      => $paymentData['amount'],
            ]);
            LogHelper::custom('payment_added', 'purchase', $id, $purchase->company_id);

            return $purchase->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Add payment failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to add payment');
        }
    }
    /**
     * Restore purchase
     */
    public function restorePurchase(int $id): Purchase
    {
        DB::beginTransaction();

        try {
            $purchase = Purchase::withTrashed()->find($id);

            if (!$purchase) {
                throw ApiException::notFound('Purchase');
            }

            $purchase->restore();

            DB::commit();

            Log::info('Purchase restored successfully', ['purchase_id' => $id]);
            LogHelper::custom('restored', 'purchase', $id, $purchase->company_id);

            return $purchase;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore purchase');
        }
    }

    /**
     * Force delete purchase
     */
    public function forceDeletePurchase(int $id): bool
    {
        DB::beginTransaction();

        try {
            $purchase = Purchase::withTrashed()->find($id);

            if (!$purchase) {
                throw ApiException::notFound('Purchase');
            }

            // Delete all purchase details first
            PurchaseDetail::where('purchase_id', $id)->forceDelete();

            // Delete related stock ledgers for single products
            ProductStockLedger::where('reference_type', 'Purchase')
                ->where('reference_id', $id)
                ->forceDelete();

            // Delete related stock ledgers for variation products
            ProductVariationStockLedger::where('reference_type', 'Purchase')
                ->where('reference_id', $id)
                ->forceDelete();

            // Permanently delete the purchase
            $companyId = $purchase->company_id;
            $purchase->forceDelete();

            DB::commit();

            Log::info('Purchase permanently deleted', ['purchase_id' => $id]);
            LogHelper::custom('force_deleted', 'purchase', $id, $companyId);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Permanent purchase deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete purchase');
        }
    }

    // ========================================
    // STOCK MANAGEMENT METHODS
    // ========================================

    /**
     * Add purchase stock to warehouse (handles both single and variation products)
     */
    private function addPurchaseStockToWarehouse(Purchase $purchase): void
    {
        foreach ($purchase->purchaseDetails as $detail) {
            $stockData = [
                'warehouse_id' => $purchase->warehouse_id,
                'bin_id' => null, // Can be added later if needed
                'quantity' => $detail->quantity,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'purchase',
                'reference_type' => 'Purchase',
                'reference_id' => $purchase->id,
                'notes' => "Purchase stock added - Reference: {$purchase->reference_no}"
            ];

            // ✅ Add variation_id if exists
            if ($detail->variation_id) {
                $stockData['variation_id'] = $detail->variation_id;
            }

            $this->productService->addStockToWarehouse($detail->product_id, $stockData);

            Log::info('Stock added for purchase item', [
                'purchase_id' => $purchase->id,
                'product_id' => $detail->product_id,
                'variation_id' => $detail->variation_id,
                'quantity' => $detail->quantity
            ]);
        }
    }

    /**
     * Remove purchase stock from warehouse (Reverse)
     * Handles both single and variation products
     */
    private function removePurchaseStockFromWarehouse(Purchase $purchase): void
    {
        foreach ($purchase->purchaseDetails as $detail) {
            $stockData = [
                'warehouse_id' => $purchase->warehouse_id,
                'bin_id' => null,
                'quantity' => $detail->quantity,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'adjustment',
                'reference_type' => 'PurchaseReversal',
                'reference_id' => $purchase->id,
                'notes' => "Purchase stock reversed - Reference: {$purchase->reference_no}"
            ];

            // ✅ Add variation_id if exists
            if ($detail->variation_id) {
                $stockData['variation_id'] = $detail->variation_id;
            }

            $this->productService->removeStockFromWarehouse($detail->product_id, $stockData);

            Log::info('Stock removed for purchase reversal', [
                'purchase_id' => $purchase->id,
                'product_id' => $detail->product_id,
                'variation_id' => $detail->variation_id,
                'quantity' => $detail->quantity
            ]);
        }
    }

    // ========================================
    // PRIVATE HELPER METHODS
    // ========================================

    /**
     * Calculate item total
     */
    private function calculateItemTotal(array $item): float
    {
        $quantity = $item['quantity'];
        $price = $item['purchase_price'];
        $discount = $item['discount'] ?? 0;
        $tax = $item['tax'] ?? 0;

        $subtotal = $quantity * $price;
        $afterDiscount = $subtotal - $discount;
        $total = $afterDiscount + $tax;

        return round($total, 2);
    }

    /**
     * Calculate purchase totals
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
            return Purchase::PAYMENT_UNPAID;
        }

        if ($paidAmount >= $grandTotal) {
            return Purchase::PAYMENT_PAID;
        }

        return Purchase::PAYMENT_PARTIAL;
    }
}
