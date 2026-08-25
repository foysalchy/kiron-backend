<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{Quotation, QuotationItem, Order, Party};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, DB, Hash, Log};

class QuotationService
{

    protected OrderService $orderService;

    public function __construct(
        OrderService $orderService,
    ) {
        $this->orderService = $orderService;
    }
    /**
     * Get all quotations
     */
    public function getAllQuotations(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Quotation::with([
                'warehouse',
                'items.product',
                'items.variation.attributes.attributeGroup',
                'items.variation.attributes.attributeValue',
                'convertedOrder',
            ]);

            if (isset($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('quotation_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('quotation_date', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('quotation_no', 'like', "%{$filters['search']}%")
                        ->orWhere('reference_no', 'like', "%{$filters['search']}%");
                });
            }

            if (isset($filters['not_converted']) && $filters['not_converted']) {
                $query->whereNull('converted_to_order_id');
            }

            $sortBy = $filters['sort_by'] ?? 'id';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching quotations: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch quotations');
        }
    }

    /**
     * Get quotation by ID
     */
    public function getQuotationById(int $id): Quotation
    {
        $quotation = Quotation::with([
            'warehouse',
            'items.product',
            'items.variation.attributes.attributeGroup',
            'items.variation.attributes.attributeValue',
            'convertedOrder',
            'creator',
            'actionLogs'
        ])->find($id);

        if (!$quotation) {
            throw ApiException::notFound('Quotation');
        }

        return $quotation;
    }

    /**
     * Create quotation
     */
    public function createQuotation(array $data): Quotation
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            unset($data['items']);

            // Calculate totals
            $totals = $this->calculateTotals($items, $data);
            $data = array_merge($data, $totals);

            // Set created_by
            $data['created_by'] = Auth::id();

            // Create quotation
            $quotation = Quotation::create($data);

            // Create items
            foreach ($items as $item) {
                $itemTotals = $this->calculateItemTotals($item);

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'subtotal' => $itemTotals['subtotal'],
                    'total' => $itemTotals['total'],
                    'description' => $item['description'] ?? null,
                ]);
            }

            DB::commit();

            Log::info('Quotation created successfully', ['quotation_id' => $quotation->id]);
            LogHelper::created('quotation', $quotation->id, $quotation->company_id, 'quotation no: ' . $quotation->quotation_no);

            return $quotation->load([
                'customer',
                'warehouse',
                'items.product',
                'items.variation.attributes.attributeGroup',
                'items.variation.attributes.attributeValue',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Quotation creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create quotation: ' . $e->getMessage());
        }
    }

    /**
     * Update quotation
     */
    public function updateQuotation(int $id, array $data): Quotation
    {
        DB::beginTransaction();

        try {
            $quotation = $this->getQuotationById($id);

            // Cannot update if converted to order
            if ($quotation->isConverted()) {
                throw ApiException::badRequest('Cannot update quotation that has been converted to order');
            }

            // Cannot update accepted or rejected quotations
            if ($quotation->isAccepted() || $quotation->isRejected()) {
                throw ApiException::badRequest('Cannot update accepted or rejected quotations');
            }

            $items = $data['items'] ?? null;
            unset($data['items']);

            // If items provided, recalculate totals
            if ($items) {
                $totals = $this->calculateTotals($items, $data);
                $data = array_merge($data, $totals);

                // Delete old items and create new ones
                $quotation->items()->delete();

                foreach ($items as $item) {
                    $itemTotals = $this->calculateItemTotals($item);

                    QuotationItem::create([
                        'quotation_id' => $quotation->id,
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'] ?? 0,
                        'tax' => $item['tax'] ?? 0,
                        'subtotal' => $itemTotals['subtotal'],
                        'total' => $itemTotals['total'],
                        'description' => $item['description'] ?? null,
                    ]);
                }
            }

            $quotation->update($data);

            DB::commit();

            Log::info('Quotation updated successfully', ['quotation_id' => $quotation->id]);
            LogHelper::updated('quotation', $quotation->id, $quotation->company_id, 'quotation no: ' . $quotation->quotation_no);

            return $quotation->fresh([
                'customer',
                'warehouse',
                'items.product',
                'items.variation.attributes.attributeGroup',
                'items.variation.attributes.attributeValue',
            ]);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Quotation update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update quotation');
        }
    }

    /**
     * Change status
     */
    public function changeStatus(int $id, int $status): Quotation
    {
        DB::beginTransaction();

        try {
            $quotation = $this->getQuotationById($id);
            $oldStatus = $quotation->status;

            // Cannot change status if converted
            if ($quotation->isConverted()) {
                throw ApiException::badRequest('Cannot change status of converted quotation');
            }

            $quotation->update(['status' => $status]);

            DB::commit();

            Log::info('Quotation status changed', [
                'quotation_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);
            LogHelper::custom('status_changed', 'quotation', $id, $quotation->company_id, 'quotation no: ' . $quotation->quotation_no . ' new status: ' . $quotation->status_label);

            return $quotation->fresh();
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
     * Convert quotation to order
     */
    public function convertToOrder(int $id, array $orderData = []): Order
    {
        DB::beginTransaction();

        try {
            $quotation = $this->getQuotationById($id);

            // Validations
            if ($quotation->isConverted()) {
                throw ApiException::badRequest('Quotation already converted to order');
            }

            if (!$quotation->isAccepted()) {
                throw ApiException::badRequest('Only accepted quotations can be converted');
            }

            if (!$quotation->isValid()) {
                throw ApiException::badRequest('Quotation has expired');
            }


            // Prepare order data from quotation
            $orderItems = $quotation->items->map(function ($item) {
                $itemData = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'tax' => $item->tax,
                ];

                if ($item->variation_id) {
                    $itemData['variation_id'] = $item->variation_id;
                }
                return $itemData;
            })->toArray();
            $customer =  Party::create([
                'company_id' => $quotation->company_id,
                'name' => $quotation->name,
                'email' => $quotation->email,
                'phone' => $quotation->phone,
                'address' => $quotation->address,
                'password' => Hash::make('password'),
                'type' => 2,

            ]);
            $orderPayload = [
                'customer_id' => $customer->id,
                'warehouse_id' => $quotation->warehouse_id,
                'order_date' => $orderData['order_date'] ?? now()->toDateString(),
                'reference_no' => $orderData['reference_no'] ?? $quotation->quotation_no,
                'type' =>  'quotation',
                'status' => $orderData['status'] ?? Status::Pending->value, // Pending
                'items' => $orderItems,
                'tax_amount' => $quotation->tax_amount,
                'discount_amount' => $quotation->discount_amount,
                'shipping_charges' => $quotation->shipping_charges,
                'other_charges' => $quotation->other_charges,
                'note' => $orderData['note'] ?? $quotation->note,
                'payments' => $orderData['payments'] ?? [],
            ];

            // Create order using OrderService
            $order = $this->orderService->createOrder($orderPayload);

            // Update quotation with conversion info
            $quotation->update([
                'converted_to_order_id' => $order->id,
                'converted_at' => now(),
            ]);

            DB::commit();

            Log::info('Quotation converted to order', [
                'quotation_id' => $quotation->id,
                'order_id' => $order->id
            ]);
            LogHelper::custom('converted', 'quotation', $quotation->id, $quotation->company_id, 'quotation ' . $quotation->quotation_no . ' converted to order ' . $order->order_no);

            return $order;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quotation conversion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to convert quotation: ' . $e->getMessage());
        }
    }

    /**
     * Delete quotation
     */
    public function deleteQuotation(int $id): bool
    {
        DB::beginTransaction();

        try {
            $quotation = $this->getQuotationById($id);

            // Cannot delete if converted
            if ($quotation->isConverted()) {
                throw ApiException::badRequest('Cannot delete quotation that has been converted to order');
            }

            // Only draft quotations can be deleted
            if (!$quotation->isDraft()) {
                throw ApiException::badRequest('Only draft quotations can be deleted');
            }

            $quotation->delete();

            DB::commit();

            Log::info('Quotation deleted successfully', ['quotation_id' => $id]);
            LogHelper::deleted('quotation', $id, $quotation->company_id, $quotation->quotation_no);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Quotation deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete quotation');
        }
    }

    /**
     * Restore quotation
     */
    public function restoreQuotation(int $id): Quotation
    {
        DB::beginTransaction();

        try {
            $quotation = Quotation::onlyTrashed()->find($id);

            if (!$quotation) {
                throw ApiException::notFound('Quotation');
            }

            $quotation->restore();

            DB::commit();

            Log::info('Quotation restored successfully', ['quotation_id' => $id]);
            LogHelper::custom('restored', 'quotation', $id, $quotation->company_id, $quotation->quotation_no);

            return $quotation;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Quotation restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore quotation');
        }
    }

    /**
     * Force delete quotation
     */
    public function forceDeleteQuotation(int $id): bool
    {
        DB::beginTransaction();

        try {
            $quotation = Quotation::withTrashed()->find($id);

            if (!$quotation) {
                throw ApiException::notFound('Quotation');
            }

            // Cannot force delete if converted
            if ($quotation->isConverted()) {
                throw ApiException::badRequest('Cannot delete quotation that has been converted to order');
            }

            // Delete items
            QuotationItem::where('quotation_id', $id)->forceDelete();

            $companyId = $quotation->company_id;
            $quotation->forceDelete();

            DB::commit();

            Log::info('Quotation permanently deleted', ['quotation_id' => $id]);
            LogHelper::custom('force_deleted', 'quotation', $id, $companyId, $quotation->quotation_no);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Permanent quotation deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete quotation');
        }
    }

    /**
     * Calculate item totals
     */
    private function calculateItemTotals(array $item): array
    {
        $quantity = $item['quantity'];
        $unitPrice = $item['unit_price'];
        $discount = $item['discount'] ?? 0;
        $tax = $item['tax'] ?? 0;

        $subtotal = $quantity * $unitPrice;
        $total = $subtotal - $discount + $tax;

        return [
            'subtotal' => round($subtotal, 2),
            'total' => round($total, 2),
        ];
    }

    /**
     * Calculate totals
     */
    private function calculateTotals(array $items, array $data): array
    {
        $totalItems = 0;
        $subtotal = 0;

        foreach ($items as $item) {
            $totalItems += $item['quantity'];
            $itemTotals = $this->calculateItemTotals($item);
            $subtotal += $itemTotals['total'];
        }

        $taxAmount = $data['tax_amount'] ?? 0;
        $discountAmount = $data['discount_amount'] ?? 0;
        $shippingCharges = $data['shipping_charges'] ?? 0;
        $otherCharges = $data['other_charges'] ?? 0;

        $grandTotal = $subtotal + $taxAmount - $discountAmount + $shippingCharges + $otherCharges;

        return [
            'total_items' => $totalItems,
            'subtotal' => round($subtotal, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }
    /**
     * Bulk update status for multiple quotations
     */
    public function bulkUpdateStatus(array $ids, int $status): Collection
    {
        DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                $this->changeStatus((int)$id, $status);
            }

            DB::commit();
            Log::info('Quotations bulk status updated', ['ids' => $ids, 'status' => $status]);

            // ফিক্স: আপডেট হওয়া ডাটাগুলো ডাটাবেজ থেকে নিয়ে রিটার্ন করুন
            return Quotation::whereIn('id', $ids)->get();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk status update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Bulk delete multiple quotations
     */
    public function bulkDelete(array $ids): void
    {
        DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                $this->deleteQuotation((int)$id);
            }

            DB::commit();
            Log::info('Quotations bulk deleted', ['ids' => $ids]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk delete failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
