<?php

namespace App\Services;

use App\Models\{Purchase, PurchaseDetail};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class PurchaseService
{
    /**
     * Get all purchases with optional pagination
     */
    public function getAllPurchases(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Purchase::with(['warehouse', 'supplier', 'purchaseDetails.product']);

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

            $sortBy = $filters['sort_by'] ?? 'purchase_date';
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
        $purchase = Purchase::with(['warehouse', 'supplier', 'purchaseDetails.product'])->find($id);

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
                    'quantity' => $item['quantity'],
                    'purchase_price' => $item['purchase_price'],
                    'unit_cost' => $item['unit_cost'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $itemTotal,
                ]);
            }

            DB::commit();

            Log::info('Purchase created successfully', ['purchase_id' => $purchase->id]);
            LogHelper::created('purchase', $purchase->id, $purchase->company_id);

            return $purchase->load(['warehouse', 'supplier', 'purchaseDetails.product']);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create purchase');
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

            $items = $data['items'] ?? null;
            unset($data['items']);

            // If items are provided, recalculate totals
            if ($items) {
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
                        'quantity' => $item['quantity'],
                        'purchase_price' => $item['purchase_price'],
                        'unit_cost' => $item['unit_cost'],
                        'discount' => $item['discount'] ?? 0,
                        'tax' => $item['tax'] ?? 0,
                        'total' => $itemTotal,
                    ]);
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
            LogHelper::updated('purchase', $purchase->id, $purchase->company_id);

            return $purchase->fresh(['warehouse', 'supplier', 'purchaseDetails.product']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Purchase update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update purchase');
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

    /**
     * Change purchase status
     */
    public function changeStatus(int $id, int $status): Purchase
    {
        try {
            $purchase = $this->getPurchaseById($id);
            $purchase->update(['status' => $status]);

            Log::info('Purchase status changed', ['purchase_id' => $id, 'status' => $status]);
            LogHelper::custom('status_changed', 'purchase', $id, $purchase->company_id);

            return $purchase;
        } catch (\Exception $e) {
            Log::error('Purchase status change failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change status');
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

            $currentPaid = $purchase->payment_amount ?? 0;
            $newPaid = $currentPaid + $paymentData['amount'];

            $purchase->update([
                'payment_amount' => $newPaid,
                'payment_type' => $paymentData['payment_type'] ?? $purchase->payment_type,
                'account' => $paymentData['account'] ?? $purchase->account,
                'payment_note' => $paymentData['payment_note'] ?? $purchase->payment_note,
                'payment_status' => $this->determinePaymentStatus($purchase->grand_total, $newPaid),
            ]);

            DB::commit();

            Log::info('Payment added to purchase', ['purchase_id' => $id, 'amount' => $paymentData['amount']]);
            LogHelper::custom('payment_added', 'purchase', $id, $purchase->company_id);

            return $purchase;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Add payment failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to add payment');
        }
    }

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

            Log::info('purchase restored successfully', ['purchase_id' => $id]);
            LogHelper::custom('restored', 'purchase', $id, $purchase->company_id);

            return $purchase->load(['purchaseDetails.product']);
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
     * Permanently delete a requisition
     */
    public function forceDeletePurchase(int $id): bool
    {
        DB::beginTransaction();

        try {
            $purchase = Purchase::withTrashed()->find($id);

            if (!$purchase) {
                throw ApiException::notFound('Requisition');
            }

            // Delete all requisition details first
            PurchaseDetail::where('purchase_id', $id)->delete();

            // Permanently delete the requisition
            $companyId = $purchase->company_id;
            $purchase->forceDelete();

            DB::commit();

            Log::info('Requisition permanently deleted', ['purchase_id' => $id]);
            LogHelper::custom('force_deleted', 'purchase', $id, $companyId);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Permanent requisition deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete requisition');
        }
    }
}
