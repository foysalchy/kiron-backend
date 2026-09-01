<?php

namespace App\Services\Production;

use App\Models\ProductionWastage;
use App\Models\Product;
use App\Services\ProductService;
use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductionWastageService
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function getAll(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = ProductionWastage::with([
                'productionOrder',
                'product.brand',
                'productVariation',
                'warehouse',
                'creator'
            ]);

            if (!empty($filters['production_order_id'])) {
                $query->where('production_order_id', $filters['production_order_id']);
            }

            if (!empty($filters['reason'])) {
                $query->where('reason', $filters['reason']);
            }

            if (!empty($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (!empty($filters['date_from'])) {
                $query->whereDate('date', '>=', $filters['date_from']);
            }

            if (!empty($filters['date_to'])) {
                $query->whereDate('date', '<=', $filters['date_to']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', function ($pq) use ($search) {
                        $pq->where('title', 'like', "%{$search}%");
                    })->orWhereHas('productionOrder', function ($oq) use ($search) {
                        $oq->where('order_number', 'like', "%{$search}%");
                    });
                });
            }

            $sortBy = $filters['sort_by'] ?? 'date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching production wastages: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch production wastages');
        }
    }

    public function getById(int $id): ProductionWastage
    {
        $wastage = ProductionWastage::with([
            'productionOrder',
            'product.brand',
            'productVariation',
            'warehouse',
            'creator'
        ])->find($id);

        if (!$wastage) {
            throw ApiException::notFound('Production Wastage');
        }

        return $wastage;
    }

    public function create(array $data): ProductionWastage
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = Auth::id();
            $qty = (float)($data['quantity'] ?? 0);
            $unitCost = (float)($data['unit_cost'] ?? 0);
            $data['total_cost'] = round($qty * $unitCost, 2);

            $wastage = ProductionWastage::create($data);

            // Deduct stock if adjust_stock option is true
            if (!empty($data['adjust_stock'])) {
                $product = Product::find($data['product_id']);
                if ($product && $product->manage_stock) {
                    $this->productService->adjustStock($product->id, [
                        'warehouse_id' => $data['warehouse_id'],
                        'variation_id' => $data['product_variation_id'] ?? null,
                        'quantity' => -(int)round($qty),
                        'transaction_type' => 'adjustment',
                        'reference_type' => 'ProductionWastage',
                        'reference_id' => $wastage->id,
                        'notes' => "Production Wastage: " . ($data['reason'] ?? 'Scrap')
                    ]);
                }
            }

            DB::commit();
            return $this->getById($wastage->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording wastage: ' . $e->getMessage());
            throw ApiException::serverError('Failed to record wastage: ' . $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        $wastage = $this->getById($id);
        $wastage->delete();
    }
}
