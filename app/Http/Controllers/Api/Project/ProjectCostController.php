<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectLaborCost;
use App\Models\ProjectMaterial;
use App\Models\ProjectEquipmentCost;
use App\Models\ProjectExpense;
use App\Models\ProjectOverhead;
use App\Models\ProjectBudget;
use App\Services\Project\ProjectCostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectCostController extends Controller
{
    public function __construct(
        protected ProjectCostService $costService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $projectId = $request->get('project_id');

        $labor = ProjectLaborCost::with(['employee', 'phase', 'task'])->when($projectId, fn($q) => $q->where('project_id', $projectId))->latest('date')->get();
        $materials = ProjectMaterial::with(['product', 'warehouse', 'phase', 'task'])->when($projectId, fn($q) => $q->where('project_id', $projectId))->latest('date')->get();
        $equipment = ProjectEquipmentCost::with(['vendor', 'phase', 'task'])->when($projectId, fn($q) => $q->where('project_id', $projectId))->latest('date')->get();
        $expenses = ProjectExpense::with(['vendor', 'phase', 'task'])->when($projectId, fn($q) => $q->where('project_id', $projectId))->latest('date')->get();
        $overheads = ProjectOverhead::when($projectId, fn($q) => $q->where('project_id', $projectId))->get();
        $budgets = ProjectBudget::with('phase')->when($projectId, fn($q) => $q->where('project_id', $projectId))->get();

        return ResponseHelper::success([
            'labor_costs' => $labor,
            'materials' => $materials,
            'equipment_costs' => $equipment,
            'expenses' => $expenses,
            'overheads' => $overheads,
            'budgets' => $budgets,
            'totals' => [
                'labor' => (float)$labor->sum('total_cost'),
                'materials' => (float)$materials->sum('total_cost'),
                'equipment' => (float)$equipment->sum('total_cost'),
                'expenses' => (float)$expenses->sum('amount'),
                'overheads' => (float)$overheads->sum('total_cost'),
                'total_cost' => (float)$labor->sum('total_cost') + (float)$materials->sum('total_cost') + (float)$equipment->sum('total_cost') + (float)$expenses->sum('amount') + (float)$overheads->sum('total_cost'),
            ]
        ], 'Cost records retrieved');
    }

    // Labor
    public function storeLabor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'employee_id' => 'nullable|exists:employees,id',
            'worker_name' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:100',
            'cost_type' => 'required|string',
            'rate' => 'required|numeric',
            'quantity' => 'required|numeric',
            'unit' => 'nullable|string',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $labor = $this->costService->addLaborCost($validated);
        return ResponseHelper::created($labor, 'Labor cost recorded');
    }

    public function destroyLabor(int $id): JsonResponse
    {
        $this->costService->deleteLaborCost($id);
        return ResponseHelper::success(null, 'Labor cost deleted');
    }

    // Materials
    public function storeMaterial(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'product_id' => 'nullable|exists:products,id',
            'product_variation_id' => 'nullable',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'item_name' => 'nullable|string',
            'quantity' => 'required|numeric|min:0.0001',
            'unit' => 'nullable|string',
            'unit_cost' => 'nullable|numeric',
            'date' => 'required|date',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $material = $this->costService->addMaterial($validated);
        return ResponseHelper::created($material, 'Material recorded & inventory adjusted');
    }

    public function destroyMaterial(int $id): JsonResponse
    {
        $this->costService->deleteMaterial($id);
        return ResponseHelper::success(null, 'Material removed');
    }

    // Equipment
    public function storeEquipment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'equipment_name' => 'required|string',
            'usage_type' => 'required|string',
            'rate' => 'required|numeric',
            'quantity' => 'required|numeric',
            'unit' => 'nullable|string',
            'date' => 'required|date',
            'vendor_id' => 'nullable|exists:parties,id',
            'notes' => 'nullable|string',
        ]);

        $equip = $this->costService->addEquipmentCost($validated);
        return ResponseHelper::created($equip, 'Equipment cost recorded');
    }

    public function destroyEquipment(int $id): JsonResponse
    {
        $this->costService->deleteEquipmentCost($id);
        return ResponseHelper::success(null, 'Equipment cost deleted');
    }

    // Expenses
    public function storeExpense(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'task_id' => 'nullable|exists:project_tasks,id',
            'category' => 'required|string',
            'vendor_id' => 'nullable|exists:parties,id',
            'title' => 'required|string',
            'amount' => 'required|numeric',
            'payment_method' => 'nullable|string',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'attachment' => 'nullable|string',
        ]);

        $expense = $this->costService->addExpense($validated);
        return ResponseHelper::created($expense, 'Expense recorded');
    }

    public function destroyExpense(int $id): JsonResponse
    {
        $this->costService->deleteExpense($id);
        return ResponseHelper::success(null, 'Expense deleted');
    }

    // Overheads
    public function storeOverhead(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string',
            'calculation_type' => 'required|string',
            'rate_or_percentage' => 'nullable|numeric',
            'total_cost' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $overhead = $this->costService->addOverhead($validated);
        return ResponseHelper::created($overhead, 'Overhead recorded');
    }

    public function destroyOverhead(int $id): JsonResponse
    {
        $this->costService->deleteOverhead($id);
        return ResponseHelper::success(null, 'Overhead deleted');
    }

    // Budgets
    public function saveBudgets(Request $request, int $projectId): JsonResponse
    {
        $request->validate([
            'budgets' => 'required|array',
            'budgets.*.category' => 'required|string',
            'budgets.*.allocated_amount' => 'required|numeric',
        ]);

        $budgets = $this->costService->saveBudgets($projectId, $request->budgets);
        return ResponseHelper::success($budgets, 'Budgets saved');
    }
}
