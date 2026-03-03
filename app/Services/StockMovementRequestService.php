<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{StockMovementRequest, Product, ProductVariation, StockMovement};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, DB, Log};

class StockMovementRequestService
{
    /**
     * Get all stock movement requests with filters
     */
    public function getAllRequests(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = StockMovementRequest::with([
                'sourceWarehouse',
                'destinationWarehouse',
                'requestedBy',
                'approvedBy',
                'rejectedBy',
                'items.product',
                'items.variation.attributes.attributeGroup',
                'items.variation.attributes.attributeValue',
            ]);

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['source_warehouse_id'])) {
                $query->where('source_warehouse_id', $filters['source_warehouse_id']);
            }

            if (isset($filters['destination_warehouse_id'])) {
                $query->where('destination_warehouse_id', $filters['destination_warehouse_id']);
            }

            if (isset($filters['date_from'])) {
                $query->whereDate('request_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->whereDate('request_date', '<=', $filters['date_to']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('request_number', 'like', "%{$filters['search']}%")
                        ->orWhere('notes', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'request_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching stock movement requests: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch stock movement requests');
        }
    }

    /**
     * Get request by ID
     */
    public function getRequestById(int $id): StockMovementRequest
    {
        $request = StockMovementRequest::with([
            'sourceWarehouse',
            'destinationWarehouse',
            'items.product.brand',
            'items.variation.attributes.attributeGroup',
            'items.variation.attributes.attributeValue',
            'requestedBy',
            'approvedBy',
            'rejectedBy',
            'stockMovement'
        ])->find($id);

        if (!$request) {
            throw ApiException::notFound('Stock Movement Request');
        }

        return $request;
    }

    /**
     * Create stock movement request
     * Now supports both single and variation products
     */
    public function createRequest(array $data): StockMovementRequest
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            unset($data['items']);

            // Validate stock availability
            $this->validateStockAvailability($items, $data['source_warehouse_id']);

            $data['status'] = Status::Pending->value;

            // Create request
            $request = StockMovementRequest::create($data);

            // Create request items
            foreach ($items as $item) {
                $request->items()->create([
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'transfer_quantity' => $item['transfer_quantity'],
                ]);
            }

            DB::commit();

            Log::info('Stock movement request created', [
                'request_id' => $request->id,
                'request_number' => $request->request_number
            ]);
            LogHelper::created('stock_movement_request', $request->id, $request->company_id);

            return $this->getRequestById($request->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement request creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create stock movement request: ' . $e->getMessage());
        }
    }

    /**
     * Update stock movement request
     */
    public function updateRequest(int $id, array $data): StockMovementRequest
    {
        DB::beginTransaction();

        try {
            $request = $this->getRequestById($id);

            if (!$request->isPending()) {
                throw ApiException::badRequest('Only pending requests can be updated');
            }

            $items = $data['items'];
            unset($data['items']);

            // Validate stock availability
            $this->validateStockAvailability($items, $data['source_warehouse_id']);

            $request->update([
                'request_date' => $data['request_date'],
                'source_warehouse_id' => $data['source_warehouse_id'],
                'destination_warehouse_id' => $data['destination_warehouse_id'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Delete old items and create new ones
            $request->items()->delete();

            foreach ($items as $item) {
                $request->items()->create([
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'transfer_quantity' => $item['transfer_quantity'],
                ]);
            }

            DB::commit();

            Log::info('Stock movement request updated', ['request_id' => $id]);
            LogHelper::updated('stock_movement_request', $id, $request->company_id);

            return $this->getRequestById($request->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement request update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update stock movement request');
        }
    }

    /**
     * Approve request
     */
    public function approveRequest(int $id): StockMovementRequest
    {
        DB::beginTransaction();

        try {
            $request = $this->getRequestById($id);

            if (!$request->isPending()) {
                throw ApiException::badRequest('Only pending requests can be approved');
            }

            $request->update([
                'status' => Status::Approved->value,
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            Log::info('Stock movement request approved', ['request_id' => $id]);
            LogHelper::custom('approved', 'stock_movement_request', $id, $request->company_id, 'approved');

            return $this->getRequestById($request->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement request approval failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to approve stock movement request');
        }
    }

    /**
     * Reject request
     */
    public function rejectRequest(int $id, string $reason): StockMovementRequest
    {
        DB::beginTransaction();

        try {
            $request = $this->getRequestById($id);

            if (!$request->isPending()) {
                throw ApiException::badRequest('Only pending requests can be rejected');
            }

            $request->update([
                'status' => Status::Rejected->value,
                'rejected_by' => Auth::id(),
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            DB::commit();

            Log::info('Stock movement request rejected', ['request_id' => $id]);
            LogHelper::custom('rejected', 'stock_movement_request', $id, $request->company_id, 'rejected');

            return $this->getRequestById($request->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement request rejection failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to reject stock movement request');
        }
    }

    /**
     * Cancel request
     */
    public function cancelRequest(int $id): StockMovementRequest
    {
        DB::beginTransaction();

        try {
            $request = $this->getRequestById($id);

            if (!$request->isPending() && !$request->isApproved()) {
                throw ApiException::badRequest('Only pending or approved requests can be cancelled');
            }

            if ($request->isTransferred()) {
                throw ApiException::badRequest('Cannot cancel transferred requests');
            }

            $request->update(['status' => Status::Cancelled->value]);

            DB::commit();

            Log::info('Stock movement request cancelled', ['request_id' => $id]);
            LogHelper::custom('cancelled', 'stock_movement_request', $id, $request->company_id, 'cancelled');

            return $this->getRequestById($request->id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement request cancellation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to cancel stock movement request');
        }
    }

    /**
     * Delete request
     */
    public function deleteRequest(int $id): void
    {
        DB::beginTransaction();

        try {
            $request = $this->getRequestById($id);

            if ($request->isTransferred()) {
                throw ApiException::badRequest('Cannot delete transferred requests');
            }
            $request->delete();

            DB::commit();

            Log::info('Stock movement request deleted', ['request_id' => $id]);
            LogHelper::deleted('stock_movement_request', $id, $request->company_id);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock movement request deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete stock movement request');
        }
    }

    /**
     * Convert approved request to stock movement
     * Now supports variation products
     */
    public function convertToMovement(int $id, array $movementData): StockMovement
    {
        DB::beginTransaction();

        try {
            $request = $this->getRequestById($id);

            if (!$request->isApproved()) {
                throw ApiException::badRequest('Only approved requests can be converted to movements');
            }

            if ($request->stockMovement) {
                throw ApiException::badRequest('This request has already been converted to a movement');
            }

            // Prepare movement data from request
            $data = [
                'request_id' => $request->id,
                'company_id' => $request->company_id,
                'movement_date' => $movementData['movement_date'] ?? now()->format('Y-m-d'),
                'source_warehouse_id' => $request->source_warehouse_id,
                'destination_warehouse_id' => $request->destination_warehouse_id,
                'notes' => $request->notes,
                'items' => []
            ];

            // Convert request items to movement items
            foreach ($request->items as $requestItem) {
                $itemData = [
                    'product_id' => $requestItem->product_id,
                    'quantity' => $requestItem->transfer_quantity,
                    'batch_number' => $movementData['items'][$requestItem->id]['batch_number'] ?? null,
                    'source_bin_id' => $movementData['items'][$requestItem->id]['source_bin_id'] ?? null,
                    'destination_bin_id' => $movementData['items'][$requestItem->id]['destination_bin_id'] ?? null,
                    'serial_numbers' => $movementData['items'][$requestItem->id]['serial_numbers'] ?? null,
                ];

                // Add variation_id if exists
                if ($requestItem->variation_id) {
                    $itemData['variation_id'] = $requestItem->variation_id;
                }

                $data['items'][] = $itemData;
            }

            // Use InventoryService to create movement
            $inventoryService = app(InventoryService::class);
            $movement = $inventoryService->createMovement($data);

            // Update request status
            $request->update(['status' => Status::Transferred->value]);

            DB::commit();

            Log::info('Stock movement request converted to movement', [
                'request_id' => $id,
                'movement_id' => $movement->id
            ]);
            LogHelper::custom('converted_to_movement', 'stock_movement_request', $id, $movement->company_id);

            return $movement;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Request to movement conversion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to convert request to movement');
        }
    }

    /**
     * Validate stock availability
     * Now handles both single and variation products
     */
    private function validateStockAvailability(array $items, int $sourceWarehouseId): void
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product) {
                throw ApiException::badRequest("Product ID {$item['product_id']} not found");
            }

            if (isset($item['variation_id']) && $item['variation_id']) {
                // Validate variation stock
                $variation = ProductVariation::with('stocks')
                    ->where('id', $item['variation_id'])
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (!$variation) {
                    throw ApiException::badRequest(
                        "Variation ID {$item['variation_id']} does not belong to product: {$product->title}"
                    );
                }

                $variationStock = $variation->stocks()
                    ->where('warehouse_id', $sourceWarehouseId)
                    ->first();

                $availableStock = $variationStock ? $variationStock->quantity : 0;

                if ($availableStock < $item['transfer_quantity']) {
                    throw ApiException::badRequest(
                        "Insufficient stock for {$product->title} (Variation: {$variation->sku}). Available: {$availableStock}, Requested: {$item['transfer_quantity']}"
                    );
                }
            } else {
                // Validate single product stock
                $warehouseInfo = collect($product->warehouse_info ?? []);
                $stock = $warehouseInfo->firstWhere('warehouse_id', (string) $sourceWarehouseId);
                $availableStock = $stock['quantity'] ?? 0;

                if ($availableStock < $item['transfer_quantity']) {
                    throw ApiException::badRequest(
                        "Insufficient stock for product: {$product->title}. Available: {$availableStock}, Requested: {$item['transfer_quantity']}"
                    );
                }
            }
        }
    }
}
