<?php

namespace App\Services\Project;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectLaborCost;
use App\Models\ProjectMaterial;
use App\Models\ProjectEquipmentCost;
use App\Models\ProjectExpense;
use App\Models\ProjectOverhead;
use App\Models\ProjectRevenue;
use App\Models\ProjectTimeEntry;
use Illuminate\Support\Facades\DB;

class ProjectDashboardService
{
    public function getDashboardStats(array $filters = []): array
    {
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $completedProjects = Project::where('status', 'completed')->count();
        $planningProjects = Project::where('status', 'planning')->count();
        $onHoldProjects = Project::where('status', 'on_hold')->count();
        $overdueProjects = Project::where('status', '!=', 'completed')
            ->where('deadline', '<', now()->format('Y-m-d'))
            ->count();

        // Tasks stats
        $totalTasks = ProjectTask::count();
        $completedTasks = ProjectTask::where('is_completed', true)->count();
        $inProgressTasks = ProjectTask::where('status', 'in_progress')->count();
        $overdueTasks = ProjectTask::where('is_completed', false)
            ->where('due_date', '<', now()->format('Y-m-d'))
            ->count();

        // Financials
        $totalRevenue = (float) ProjectRevenue::sum('amount');
        $receivedRevenue = (float) ProjectRevenue::sum('received_amount');
        $outstandingRevenue = (float) ProjectRevenue::sum('outstanding_amount');

        $laborCost = (float) ProjectLaborCost::sum('total_cost');
        $materialCost = (float) ProjectMaterial::sum('total_cost');
        $equipmentCost = (float) ProjectEquipmentCost::sum('total_cost');
        $expenseCost = (float) ProjectExpense::sum('amount');
        $overheadCost = (float) ProjectOverhead::sum('total_cost');

        $totalCost = $laborCost + $materialCost + $equipmentCost + $expenseCost + $overheadCost;
        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? round(($grossProfit / $totalRevenue) * 100, 2) : 0;

        $totalHours = (float) ProjectTimeEntry::sum('duration_hours');

        return [
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
            'completed_projects' => $completedProjects,
            'planning_projects' => $planningProjects,
            'on_hold_projects' => $onHoldProjects,
            'overdue_projects' => $overdueProjects,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'in_progress_tasks' => $inProgressTasks,
            'overdue_tasks' => $overdueTasks,
            'total_revenue' => $totalRevenue,
            'received_revenue' => $receivedRevenue,
            'outstanding_revenue' => $outstandingRevenue,
            'labor_cost' => $laborCost,
            'material_cost' => $materialCost,
            'equipment_cost' => $equipmentCost,
            'expense_cost' => $expenseCost,
            'overhead_cost' => $overheadCost,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'profit_margin' => $profitMargin,
            'total_hours' => $totalHours,
        ];
    }

    public function getDashboardCharts(): array
    {
        // Monthly Revenue vs Cost Trend (Last 6 Months)
        $revenueCostTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $key = $monthDate->format('M Y');
            $mStart = $monthDate->copy()->startOfMonth()->format('Y-m-d');
            $mEnd = $monthDate->copy()->endOfMonth()->format('Y-m-d');

            $rev = (float) ProjectRevenue::whereBetween('date', [$mStart, $mEnd])->sum('amount');
            $lCost = (float) ProjectLaborCost::whereBetween('date', [$mStart, $mEnd])->sum('total_cost');
            $mCost = (float) ProjectMaterial::whereBetween('date', [$mStart, $mEnd])->sum('total_cost');
            $eCost = (float) ProjectEquipmentCost::whereBetween('date', [$mStart, $mEnd])->sum('total_cost');
            $expCost = (float) ProjectExpense::whereBetween('date', [$mStart, $mEnd])->sum('amount');

            $totalC = $lCost + $mCost + $eCost + $expCost;

            $revenueCostTrend[] = [
                'month' => $key,
                'revenue' => $rev,
                'cost' => $totalC,
                'profit' => $rev - $totalC,
            ];
        }

        // Project Status Distribution
        $statusDistribution = Project::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => ucwords(str_replace('_', ' ', $item->status)),
                    'value' => (int) $item->count,
                ];
            });

        // Cost Breakdown by Type
        $labor = (float) ProjectLaborCost::sum('total_cost');
        $material = (float) ProjectMaterial::sum('total_cost');
        $equipment = (float) ProjectEquipmentCost::sum('total_cost');
        $expense = (float) ProjectExpense::sum('amount');
        $overhead = (float) ProjectOverhead::sum('total_cost');

        $costBreakdown = [
            ['name' => 'Labor Cost', 'value' => $labor],
            ['name' => 'Material Cost', 'value' => $material],
            ['name' => 'Equipment Cost', 'value' => $equipment],
            ['name' => 'Expenses', 'value' => $expense],
            ['name' => 'Overhead', 'value' => $overhead],
        ];

        // Projects by Industry Type
        $typeDistribution = Project::select('project_type', DB::raw('count(*) as count'))
            ->groupBy('project_type')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => ucwords(str_replace('_', ' ', $item->project_type)),
                    'value' => (int) $item->count,
                ];
            });

        return [
            'revenue_cost_trend' => $revenueCostTrend,
            'status_distribution' => $statusDistribution,
            'cost_breakdown' => $costBreakdown,
            'type_distribution' => $typeDistribution,
        ];
    }
}
