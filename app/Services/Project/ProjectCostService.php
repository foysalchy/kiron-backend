<?php

namespace App\Services\Project;

use App\Exceptions\ApiException;
use App\Models\ProjectLaborCost;
use App\Models\ProjectMaterial;
use App\Models\ProjectEquipmentCost;
use App\Models\ProjectExpense;
use App\Models\ProjectOverhead;
use App\Models\ProjectBudget;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Warehouse;
use App\Models\ProductStockLedger;
use App\Models\StockMovement;
use App\Models\StockMovementItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectCostService
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    // ==========================================
    // 1. Labor Cost
    // ==========================================
    public function addLaborCost(array $data): ProjectLaborCost
    {
        return DB::transaction(function () use ($data) {
            $rate = (float)($data['rate'] ?? 0);
            $qty = (float)($data['quantity'] ?? 1);
            $data['total_cost'] = $rate * $qty;

            $cost = ProjectLaborCost::create($data);

            $this->projectService->logActivity(
                $cost->project_id,
                $cost->task_id,
                'labor_cost_added',
                "Added labor cost: ৳{$cost->total_cost} for {$cost->worker_name} ({$cost->role})"
            );

            return $cost->load(['employee', 'phase', 'task']);
        });
    }

    public function deleteLaborCost(int $id): bool
    {
        $cost = ProjectLaborCost::findOrFail($id);
        $projectId = $cost->project_id;
        $cost->delete();
        $this->projectService->logActivity($projectId, null, 'labor_cost_deleted', "Deleted labor cost #{$id}");
        return true;
    }

    // ==========================================
    // 2. Materials & Warehouse Stock Issuance
    // ==========================================
    public function addMaterial(array $data): ProjectMaterial
    {
        return DB::transaction(function () use ($data) {
            $qty = (float)($data['quantity'] ?? 0);
            $unitCost = (float)($data['unit_cost'] ?? 0);

            // Fetch product info if selected
            if (!empty($data['product_id'])) {
                $product = Product::find($data['product_id']);
                if ($product) {
                    if (empty($data['item_name'])) $data['item_name'] = $product->title;
                    if ($unitCost <= 0) $unitCost = (float)($product->purchase_price ?? $product->price ?? 0);
                    if (empty($data['unit'])) $data['unit'] = $product->unit ?? 'Pcs';
                }
            }

            $data['unit_cost'] = $unitCost;
            $data['total_cost'] = $qty * $unitCost;

            // Deduct stock from warehouse if warehouse_id and product_id are provided
            if (!empty($data['warehouse_id']) && !empty($data['product_id']) && $qty > 0) {
                ProductStockLedger::create([
                    'product_id' => $data['product_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'type' => 'project_out',
                    'quantity' => -$qty,
                    'note' => "Issued for Project #{$data['project_id']}" . (!empty($data['task_id']) ? " Task #{$data['task_id']}" : ""),
                ]);

                // Create Stock Movement record for audit integrity
                $stockMovement = StockMovement::create([
                    'movement_type' => 'out',
                    'source_warehouse_id' => $data['warehouse_id'],
                    'reference_type' => 'Project',
                    'reference_id' => $data['project_id'],
                    'movement_date' => $data['date'] ?? now()->format('Y-m-d'),
                    'status' => 'completed',
                    'notes' => "Issued for Project #{$data['project_id']}" . (!empty($data['task_id']) ? " Task #{$data['task_id']}" : ""),
                ]);

                StockMovementItem::create([
                    'stock_movement_id' => $stockMovement->id,
                    'product_id' => $data['product_id'],
                    'product_variation_id' => $data['product_variation_id'] ?? null,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'total_cost' => $data['total_cost'],
                ]);

                $data['stock_movement_id'] = $stockMovement->id;
            }

            $material = ProjectMaterial::create($data);

            $this->projectService->logActivity(
                $material->project_id,
                $material->task_id,
                'material_issued',
                "Issued material: {$material->quantity} {$material->unit} {$material->item_name} (৳{$material->total_cost})"
            );

            return $material->load(['product', 'warehouse', 'phase', 'task']);
        });
    }

    public function deleteMaterial(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $material = ProjectMaterial::findOrFail($id);
            $projectId = $material->project_id;

            // Restore warehouse stock ledger if issued
            if ($material->warehouse_id && $material->product_id && $material->quantity > 0) {
                ProductStockLedger::create([
                    'product_id' => $material->product_id,
                    'warehouse_id' => $material->warehouse_id,
                    'type' => 'project_reverse',
                    'quantity' => $material->quantity,
                    'note' => "Restored from cancelled Material Issue #{$material->id} for Project #{$material->project_id}",
                ]);
            }

            $material->delete();
            $this->projectService->logActivity($projectId, null, 'material_deleted', "Deleted material record #{$id}");
            return true;
        });
    }

    // ==========================================
    // 3. Equipment Costs
    // ==========================================
    public function addEquipmentCost(array $data): ProjectEquipmentCost
    {
        $rate = (float)($data['rate'] ?? 0);
        $qty = (float)($data['quantity'] ?? 1);
        $data['total_cost'] = $rate * $qty;

        $equip = ProjectEquipmentCost::create($data);

        $this->projectService->logActivity(
            $equip->project_id,
            $equip->task_id,
            'equipment_added',
            "Added equipment usage: {$equip->equipment_name} (৳{$equip->total_cost})"
        );

        return $equip->load(['vendor', 'phase', 'task']);
    }

    public function deleteEquipmentCost(int $id): bool
    {
        $equip = ProjectEquipmentCost::findOrFail($id);
        $projectId = $equip->project_id;
        $equip->delete();
        $this->projectService->logActivity($projectId, null, 'equipment_deleted', "Deleted equipment cost #{$id}");
        return true;
    }

    // ==========================================
    // 4. Project Expenses
    // ==========================================
    public function addExpense(array $data): ProjectExpense
    {
        $expense = ProjectExpense::create($data);

        $this->projectService->logActivity(
            $expense->project_id,
            $expense->task_id,
            'expense_added',
            "Added project expense: {$expense->title} (৳{$expense->amount})"
        );

        return $expense->load(['vendor', 'phase', 'task']);
    }

    public function deleteExpense(int $id): bool
    {
        $expense = ProjectExpense::findOrFail($id);
        $projectId = $expense->project_id;
        $expense->delete();
        $this->projectService->logActivity($projectId, null, 'expense_deleted', "Deleted expense #{$id}");
        return true;
    }

    // ==========================================
    // 5. Overheads
    // ==========================================
    public function addOverhead(array $data): ProjectOverhead
    {
        $overhead = ProjectOverhead::create($data);
        $this->projectService->logActivity($overhead->project_id, null, 'overhead_added', "Added overhead: {$overhead->name} (৳{$overhead->total_cost})");
        return $overhead;
    }

    public function deleteOverhead(int $id): bool
    {
        $overhead = ProjectOverhead::findOrFail($id);
        $projectId = $overhead->project_id;
        $overhead->delete();
        $this->projectService->logActivity($projectId, null, 'overhead_deleted', "Deleted overhead #{$id}");
        return true;
    }

    // ==========================================
    // 6. Budgets
    // ==========================================
    public function saveBudgets(int $projectId, array $budgets): array
    {
        return DB::transaction(function () use ($projectId, $budgets) {
            ProjectBudget::where('project_id', $projectId)->delete();

            $saved = [];
            foreach ($budgets as $b) {
                if (!empty($b['category'])) {
                    $saved[] = ProjectBudget::create([
                        'project_id' => $projectId,
                        'phase_id' => $b['phase_id'] ?? null,
                        'category' => $b['category'],
                        'allocated_amount' => $b['allocated_amount'] ?? 0,
                        'revised_amount' => $b['revised_amount'] ?? $b['allocated_amount'] ?? 0,
                        'notes' => $b['notes'] ?? null,
                    ]);
                }
            }

            $this->projectService->logActivity($projectId, null, 'budget_updated', "Updated budget allocations");
            return $saved;
        });
    }
}
