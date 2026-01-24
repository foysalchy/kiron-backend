<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{Purchase, PurchasePaymentReturn, PurchaseReturn, PurchaseReturnDetail};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class PurchaseReturnService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Get all purchase returns with optional pagination
     */
    public function getAllPurchaseReturns(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = PurchaseReturn::with(['purchase', 'supplier', 'purchaseReturnDetails.product']);

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
            'purchaseReturnDetails.product'
        ])->find($id);

        if (!$purchaseReturn) {
            throw ApiException::notFound('Purchase Return');
        }

        return $purchaseReturn;
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

            // Calculate totals
            $totals = $this->calculateTotals($items, $data);
            $data = array_merge($data, $totals);

            // Calculate refund amount
            $totalRefund = 0;
            if (!empty($payments)) {
                $totalRefund = array_sum(array_column($payments, 'amount'));
            }
            $data['refund_amount'] = $totalRefund;

            // Create purchase return
            $purchaseReturn = PurchaseReturn::create($data);

            // Create purchase return details
            foreach ($items as $item) {
                $itemTotal = $this->calculateItemTotal($item);

                PurchaseReturnDetail::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            // ✅ Remove returned stock from warehouse (if status is Cleared)
            if ($purchaseReturn->status === Status::Cleared->value) {
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
            }

            DB::commit();

            Log::info('Purchase return created successfully', [
                'purchase_return_id' => $purchaseReturn->id,
                'status' => $purchaseReturn->status
            ]);
            LogHelper::created('purchase_return', $purchaseReturn->id, $purchaseReturn->company_id, 'total quantities ' . $purchaseReturn->total_quantities . ' refund amount ' . $purchaseReturn->refund_amount);

            return $purchaseReturn->load([
                'purchase',
                'supplier',
                'purchaseReturnDetails.product'
            ]);
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
            unset($data['items']);

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
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax' => $item['tax'] ?? 0,
                        'total' => $itemTotal,
                    ]);
                }
            }

            $purchaseReturn->update($data);

            DB::commit();

            Log::info('Purchase return updated successfully', ['purchase_return_id' => $purchaseReturn->id]);
            LogHelper::updated('purchase_return', $purchaseReturn->id, $purchaseReturn->company_id, 'total quantities ' . $purchaseReturn->total_quantities . ' refund amount ' . $purchaseReturn->refund_amount);

            return $purchaseReturn->fresh([
                'purchase',
                'supplier',
                'purchaseReturnDetails.product'
            ]);
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
            LogHelper::custom('status_changed', 'purchase_return', $id, $purchaseReturn->company_id,'purchase return no: '. $purchaseReturn->return_no .' new status ' . $getStatus->label());

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

            // Cannot add payment to cancelled return
            if ($purchaseReturn->isCancelled()) {
                throw ApiException::badRequest('Cannot add payment to cancelled return');
            }

            // Create payment
            PurchasePaymentReturn::create([
                'purchase_return_id' => $id,
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'],
                'reference_no' => $paymentData['reference_no'] ?? null,
                'note' => $paymentData['note'] ?? null,
            ]);

            // Update refund amount
            $totalRefund = $purchaseReturn->purchaseReturnPayments()->sum('amount') + $paymentData['amount'];
            $purchaseReturn->update(['refund_amount' => $totalRefund]);

            DB::commit();

            Log::info('Payment added to purchase return', ['purchase_return_id' => $id]);
            LogHelper::custom('payment_added', 'purchase_return', $id, $purchaseReturn->company_id,$purchaseReturn->return_no . ' receive payment '.  $paymentData['amount']);

            return $purchaseReturn->fresh([
                'purchase',
                'supplier',
                'purchaseReturnDetails.product'
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
     * Delete purchase return (soft delete)
     */
    public function deletePurchaseReturn(int $id): bool
    {
        DB::beginTransaction();

        try {
            $purchaseReturn = $this->getPurchaseReturnById($id);

            // Only pending purchase returns can be deleted
            if (!$purchaseReturn->isPending()) {
                throw ApiException::badRequest('Only pending purchase returns can be deleted');
            }

            $purchaseReturn->delete();

            DB::commit();

            Log::info('Purchase return deleted successfully', ['purchase_return_id' => $id]);
            LogHelper::deleted('purchase_return', $id, $purchaseReturn->company_id,$purchaseReturn->return_no);

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
            LogHelper::custom('restored', 'purchase_return', $id, $purchaseReturn->company_id,$purchaseReturn->return_no);

            return $purchaseReturn->load(['purchase', 'supplier', 'purchaseReturnDetails.product']);
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
            if (!$purchaseReturn->isPending()) {
                throw ApiException::badRequest('Only pending returns can be deleted');
            }

            // Delete details
            PurchaseReturnDetail::where('purchase_return_id', $id)->delete();

            // Delete payments
            PurchasePaymentReturn::where('purchase_return_id', $id)->delete();

            $companyId = $purchaseReturn->company_id;
            $purchaseReturn->forceDelete();

            DB::commit();

            Log::info('Purchase return permanently deleted', ['purchase_return_id' => $id]);
            LogHelper::custom('force_deleted', 'purchase_return', $id, $companyId,$purchaseReturn->return_no);

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
     */
    private function removeReturnedStockFromWarehouse(PurchaseReturn $purchaseReturn): void
    {
        foreach ($purchaseReturn->purchaseReturnDetails as $detail) {
            $this->productService->removeStockFromWarehouse($detail->product_id, [
                'warehouse_id' => $purchaseReturn->purchase->warehouse_id,
                'bin_id' => null,
                'quantity' => $detail->quantity,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'return',
                'reference_type' => 'PurchaseReturn',
                'reference_id' => $purchaseReturn->id,
                'notes' => "Stock returned to supplier - Purchase: {$purchaseReturn->purchase->reference_no} - Return: {$purchaseReturn->return_no}"
            ]);

            Log::info('Stock removed for returned item', [
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity
            ]);
        }
    }

    /**
     * Add returned stock back to warehouse (Status change from Cleared to other)
     */
    private function addReturnedStockToWarehouse(PurchaseReturn $purchaseReturn): void
    {
        foreach ($purchaseReturn->purchaseReturnDetails as $detail) {
            $this->productService->addStockToWarehouse($detail->product_id, [
                'warehouse_id' => $purchaseReturn->purchase->warehouse_id,
                'bin_id' => null,
                'quantity' => $detail->quantity,
                'batch_number' => null,
                'serial_numbers' => null,
                'transaction_type' => 'adjustment',
                'reference_type' => 'PurchaseReturnReversal',
                'reference_id' => $purchaseReturn->id,
                'notes' => "Stock restored - Return status changed from Cleared: {$purchaseReturn->return_no}"
            ]);

            Log::info('Stock restored for return status change', [
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $detail->product_id,
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
