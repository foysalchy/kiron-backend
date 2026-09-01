<?php

namespace App\Services\Project;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectLaborCost;
use App\Models\ProjectMaterial;
use App\Models\ProjectEquipmentCost;
use App\Models\ProjectExpense;
use App\Models\ProjectRevenue;
use App\Models\ProjectTimeEntry;
use Illuminate\Support\Facades\DB;

class ProjectReportService
{
    public function getProjectSummaryReport(array $filters = [])
    {
        $query = Project::with([
            'customer:id,name',
            'projectManager:id,first_name,last_name',
            'department:id,name',
        ]);

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['project_type']) && $filters['project_type'] !== 'all') {
            $query->where('project_type', $filters['project_type']);
        }

        $projects = $query->latest()->get();

        return $projects->map(function ($p) {
            return [
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'project_type' => $p->project_type,
                'customer' => $p->customer?->name ?? 'N/A',
                'manager' => $p->projectManager ? "{$p->projectManager->first_name} {$p->projectManager->last_name}" : 'N/A',
                'budget' => (float)$p->budget,
                'total_cost' => (float)$p->total_cost,
                'revenue' => (float)$p->total_revenue,
                'profit' => (float)$p->gross_profit,
                'profit_margin' => (float)$p->profit_margin,
                'progress' => (float)$p->progress,
                'status' => $p->status,
                'start_date' => $p->start_date?->format('Y-m-d'),
                'deadline' => $p->deadline?->format('Y-m-d'),
            ];
        });
    }

    public function getMaterialReport(array $filters = [])
    {
        $query = ProjectMaterial::with(['project:id,name,code', 'phase:id,name', 'product:id,title', 'warehouse:id,name']);

        if (!empty($filters['project_id'])) $query->where('project_id', $filters['project_id']);
        if (!empty($filters['warehouse_id'])) $query->where('warehouse_id', $filters['warehouse_id']);
        if (!empty($filters['date_from'])) $query->whereDate('date', '>=', $filters['date_from']);
        if (!empty($filters['date_to'])) $query->whereDate('date', '<=', $filters['date_to']);

        return $query->latest('date')->get();
    }

    public function getLaborReport(array $filters = [])
    {
        $query = ProjectLaborCost::with(['project:id,name,code', 'phase:id,name', 'task:id,title', 'employee:id,first_name,last_name']);

        if (!empty($filters['project_id'])) $query->where('project_id', $filters['project_id']);
        if (!empty($filters['employee_id'])) $query->where('employee_id', $filters['employee_id']);
        if (!empty($filters['date_from'])) $query->whereDate('date', '>=', $filters['date_from']);
        if (!empty($filters['date_to'])) $query->whereDate('date', '<=', $filters['date_to']);

        return $query->latest('date')->get();
    }

    public function getProfitabilityReport(array $filters = [])
    {
        $query = Project::with(['customer:id,name']);

        if (!empty($filters['project_id'])) $query->where('id', $filters['project_id']);
        if (!empty($filters['project_type'])) $query->where('project_type', $filters['project_type']);

        $projects = $query->get();

        return $projects->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'customer' => $p->customer?->name ?? 'N/A',
                'contract_value' => (float)$p->contract_value,
                'revenue' => (float)$p->total_revenue,
                'labor_cost' => (float)$p->total_labor_cost,
                'material_cost' => (float)$p->total_material_cost,
                'equipment_cost' => (float)$p->total_equipment_cost,
                'expense_cost' => (float)$p->total_expense_cost,
                'overhead_cost' => (float)$p->total_overhead_cost,
                'total_cost' => (float)$p->total_cost,
                'gross_profit' => (float)$p->gross_profit,
                'profit_margin' => (float)$p->profit_margin,
            ];
        });
    }
}
