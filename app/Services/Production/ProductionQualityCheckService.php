<?php

namespace App\Services\Production;

use App\Models\ProductionQualityCheck;
use App\Models\ProductionOrder;
use App\Models\Product;
use App\Services\ProductService;
use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductionQualityCheckService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function getAll(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = ProductionQualityCheck::with([
                'productionOrder',
                'product.brand',
                'productVariation',
                'inspector',
                'defectiveWarehouse'
            ]);

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['production_order_id'])) {
                $query->where('production_order_id', $filters['production_order_id']);
            }

            $sortBy = $filters['sort_by'] ?? 'inspection_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching quality checks: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch quality checks');
        }
    }

    public function getById(int $id): ProductionQualityCheck
    {
        $qc = ProductionQualityCheck::with([
            'productionOrder',
            'product.brand',
            'productVariation',
            'inspector',
            'defectiveWarehouse'
        ])->find($id);

        if (!$qc) {
            throw ApiException::notFound('Quality Check');
        }

        return $qc;
    }

    public function create(array $data): ProductionQualityCheck
    {
        DB::beginTransaction();
        try {
            $order = ProductionOrder::findOrFail($data['production_order_id']);
            if (in_array($order->status, ['completed', 'cancelled'])) {
                throw ApiException::badRequest('Quality check cannot be performed on a completed or cancelled production order.');
            }

            $data['inspector_id'] = $data['inspector_id'] ?? Auth::id();
            $data['inspection_date'] = $data['inspection_date'] ?? now();
            $data['company_id'] = $data['company_id'] ?? $order->company_id;

            $qc = ProductionQualityCheck::create($data);

            // If order status needs to be updated to quality_check or progressed
            if (in_array($order->status, ['in_progress', 'planned'])) {
                $order->update(['status' => 'quality_check']);
            }

            // Transfer defective products to defective warehouse if specified
            $defectiveQty = (float)($data['defective_quantity'] ?? 0);
            if ($defectiveQty > 0 && !empty($data['defective_warehouse_id'])) {
                $product = Product::find($data['product_id']);
                if ($product && $product->manage_stock) {
                    $this->productService->adjustStock($product->id, [
                        'warehouse_id' => $data['defective_warehouse_id'],
                        'variation_id' => $data['product_variation_id'] ?? null,
                        'quantity' => (int)round($defectiveQty),
                        'transaction_type' => 'adjustment',
                        'reference_type' => 'ProductionQualityCheck',
                        'reference_id' => $qc->id,
                        'notes' => "Defective goods from QC for PO: " . ($order?->order_number ?? '')
                    ]);
                }
            }

            DB::commit();
            return $this->getById($qc->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating quality check: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create quality check: ' . $e->getMessage());
        }
    }
}
