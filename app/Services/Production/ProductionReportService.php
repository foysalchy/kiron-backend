<?php

namespace App\Services\Production;

use App\Models\ProductionOrder;
use App\Models\ProductionConsumption;
use App\Models\ProductionOutput;
use App\Models\ProductionWastage;
use App\Models\ProductionCost;
use App\Models\BillOfMaterial;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductionReportService
{
    /**
     * Dashboard Summary Metrics
     */
    public function getDashboardStats(): array
    {
        $today = now()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');

        $todayProduced = ProductionOrder::whereDate('actual_completion_date', $today)
            ->where('status', 'completed')
            ->sum('produced_quantity');

        $monthProduced = ProductionOrder::whereBetween('actual_completion_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'completed')
            ->sum('produced_quantity');

        $pending = ProductionOrder::whereIn('status', ['draft', 'planned'])->count();
        $inProgress = ProductionOrder::whereIn('status', ['in_progress', 'quality_check'])->count();
        $completed = ProductionOrder::where('status', 'completed')->count();

        $totalCost = ProductionCost::sum('total_cost');
        $materialConsumption = ProductionConsumption::sum('quantity');
        $totalWastage = ProductionWastage::sum('quantity');

        return [
            'today_production' => (float)$todayProduced,
            'month_production' => (float)$monthProduced,
            'pending_orders' => (int)$pending,
            'in_progress_orders' => (int)$inProgress,
            'completed_orders' => (int)$completed,
            'total_production_cost' => (float)$totalCost,
            'raw_material_consumption' => (float)$materialConsumption,
            'total_wastage' => (float)$totalWastage,
        ];
    }

    /**
     * Dashboard Charts Data
     */
    public function getDashboardCharts(): array
    {
        // Monthly Production Trend (Last 6 Months)
        $months = [];
        $monthlyTrend = [];
        $costTrend = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $key = $monthDate->format('M Y');
            $mStart = $monthDate->copy()->startOfMonth();
            $mEnd = $monthDate->copy()->endOfMonth();

            $qty = ProductionOrder::where('status', 'completed')
                ->whereBetween('actual_completion_date', [$mStart, $mEnd])
                ->sum('produced_quantity');

            $cost = ProductionOrder::where('status', 'completed')
                ->whereBetween('actual_completion_date', [$mStart, $mEnd])
                ->sum('actual_total_cost');

            $monthlyTrend[] = [
                'month' => $key,
                'produced_quantity' => (float)$qty,
            ];

            $costTrend[] = [
                'month' => $key,
                'total_cost' => (float)$cost,
            ];
        }

        // Production by Product Top 5
        $productionByProduct = ProductionOrder::with('product')
            ->where('status', 'completed')
            ->select('product_id', DB::raw('SUM(produced_quantity) as total_produced'))
            ->groupBy('product_id')
            ->orderByDesc('total_produced')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'product_name' => $item->product?->title ?? 'Unknown',
                    'total_produced' => (float)$item->total_produced,
                ];
            });

        // Wastage Trend by Reason
        $wastageByReason = ProductionWastage::select('reason', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(total_cost) as total_cost'))
            ->groupBy('reason')
            ->get()
            ->map(function ($item) {
                return [
                    'reason' => ucwords(str_replace('_', ' ', $item->reason)),
                    'quantity' => (float)$item->total_qty,
                    'cost' => (float)$item->total_cost,
                ];
            });

        return [
            'monthly_production_trend' => $monthlyTrend,
            'production_cost_trend' => $costTrend,
            'production_by_product' => $productionByProduct,
            'wastage_by_reason' => $wastageByReason,
        ];
    }

    /**
     * General Production Report
     */
    public function getProductionReport(array $filters = [])
    {
        $query = ProductionOrder::with([
            'product.brand',
            'productVariation',
            'finishedGoodsWarehouse',
            'costSummary'
        ]);

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['warehouse_id'])) {
            $query->where('finished_goods_warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Raw Material Consumption Report
     */
    public function getMaterialConsumptionReport(array $filters = [])
    {
        $query = ProductionConsumption::with([
            'product',
            'productVariation',
            'warehouse',
            'productionOrder'
        ]);

        if (!empty($filters['date_from'])) {
            $query->whereDate('consumed_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('consumed_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        return $query->orderBy('consumed_at', 'desc')->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Cost & Variance Report
     */
    public function getCostReport(array $filters = [])
    {
        $query = ProductionCost::with([
            'productionOrder.product',
            'productionOrder.rawMaterialWarehouse',
            'productionOrder.finishedGoodsWarehouse'
        ]);

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Production Efficiency Report
     */
    public function getEfficiencyReport(array $filters = [])
    {
        $query = ProductionOrder::with(['product', 'stageLogs'])
            ->where('status', 'completed');

        if (!empty($filters['date_from'])) {
            $query->whereDate('actual_completion_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('actual_completion_date', '<=', $filters['date_to']);
        }

        $orders = $query->orderBy('actual_completion_date', 'desc')->paginate($filters['per_page'] ?? 20);

        $orders->getCollection()->transform(function ($order) {
            $planned = (float)$order->planned_quantity ?: 1.0;
            $produced = (float)$order->produced_quantity;
            $totalDurationMins = $order->stageLogs->sum('duration_minutes');
            
            $wastageQty = ProductionWastage::where('production_order_id', $order->id)->sum('quantity');
            $efficiency = min(100, round(($produced / $planned) * 100, 2));

            $order->efficiency_percentage = $efficiency;
            $order->total_production_time_hours = round($totalDurationMins / 60, 2);
            $order->total_wastage_quantity = (float)$wastageQty;
            return $order;
        });

        return $orders;
    }
}
