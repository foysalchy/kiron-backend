<?php

namespace App\Services\Production;

use App\Models\ProductionPlan;
use App\Models\ProductionOrder;
use App\Models\ProductionSetting;
use App\Models\BillOfMaterial;
use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductionPlanningService
{
    protected ProductionOrderService $orderService;

    public function __construct(ProductionOrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function getAll(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = ProductionPlan::with([
                'product.brand',
                'productVariation',
                'billOfMaterial',
                'warehouse',
                'workCenter',
                'creator'
            ]);

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['priority'])) {
                $query->where('priority', $filters['priority']);
            }

            if (!empty($filters['warehouse_id'])) {
                $query->where('warehouse_id', $filters['warehouse_id']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('plan_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($pq) use ($search) {
                            $pq->where('title', 'like', "%{$search}%");
                        });
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching production plans: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch production plans');
        }
    }

    public function getById(int $id): ProductionPlan
    {
        $plan = ProductionPlan::with([
            'product.brand',
            'productVariation',
            'billOfMaterial.items.product',
            'warehouse',
            'workCenter',
            'productionOrders',
            'creator'
        ])->find($id);

        if (!$plan) {
            throw ApiException::notFound('Production Plan');
        }

        return $plan;
    }

    public function create(array $data): ProductionPlan
    {
        try {
            if (empty($data['plan_number'])) {
                $data['plan_number'] = $this->generatePlanNumber();
            }
            $data['created_by'] = Auth::id();

            return ProductionPlan::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating production plan: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create production plan: ' . $e->getMessage());
        }
    }

    public function update(int $id, array $data): ProductionPlan
    {
        $plan = $this->getById($id);
        try {
            $plan->update($data);
            return $this->getById($id);
        } catch (\Exception $e) {
            Log::error('Error updating production plan: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update production plan');
        }
    }

    public function delete(int $id): void
    {
        $plan = $this->getById($id);
        $plan->delete();
    }

    /**
     * Convert an approved plan into an actual production order
     */
    public function convertToOrder(int $id, array $additionalData = []): ProductionOrder
    {
        DB::beginTransaction();
        try {
            $plan = $this->getById($id);

            $orderData = array_merge([
                'production_plan_id' => $plan->id,
                'product_id' => $plan->product_id,
                'product_variation_id' => $plan->product_variation_id,
                'bill_of_material_id' => $plan->bill_of_material_id,
                'raw_material_warehouse_id' => $plan->warehouse_id,
                'finished_goods_warehouse_id' => $plan->warehouse_id,
                'work_center_id' => $plan->work_center_id,
                'planned_quantity' => $plan->planned_quantity,
                'unit' => $plan->unit,
                'priority' => $plan->priority,
                'planned_start_date' => $plan->planned_start_date,
                'expected_completion_date' => $plan->planned_end_date,
                'status' => 'planned',
                'assigned_to' => $plan->assigned_team,
                'notes' => $plan->notes,
            ], $additionalData);

            $order = $this->orderService->create($orderData);

            $plan->update(['status' => 'converted_to_order']);

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error converting plan to order: ' . $e->getMessage());
            throw ApiException::serverError('Failed to convert plan to order: ' . $e->getMessage());
        }
    }

    private function generatePlanNumber(): string
    {
        $prefix = 'PP';
        $setting = ProductionSetting::first();
        if ($setting && !empty($setting->plan_prefix)) {
            $prefix = $setting->plan_prefix;
        }

        $date = now()->format('Ymd');
        $count = ProductionPlan::whereDate('created_at', now())->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }
}
