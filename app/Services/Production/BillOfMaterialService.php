<?php

namespace App\Services\Production;

use App\Models\BillOfMaterial;
use App\Models\BomItem;
use App\Models\ProductionSetting;
use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BillOfMaterialService
{
    public function getAll(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = BillOfMaterial::with([
                'product.brand',
                'productVariation',
                'items.product',
                'items.productVariation',
                'creator'
            ]);

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['product_id'])) {
                $query->where('product_id', $filters['product_id']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('bom_number', 'like', "%{$search}%")
                        ->orWhere('version', 'like', "%{$search}%")
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
            Log::error('Error fetching BOMs: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch Bills of Materials');
        }
    }

    public function getById(int $id): BillOfMaterial
    {
        $bom = BillOfMaterial::with([
            'product.brand',
            'productVariation',
            'items.product',
            'items.productVariation',
            'creator'
        ])->find($id);

        if (!$bom) {
            throw ApiException::notFound('Bill of Material');
        }

        return $bom;
    }

    public function create(array $data): BillOfMaterial
    {
        DB::beginTransaction();
        try {
            if (empty($data['bom_number'])) {
                $data['bom_number'] = $this->generateBomNumber();
            }

            $data['created_by'] = Auth::id();

            // Calculate item costs and totals
            $itemsData = $data['items'] ?? [];
            unset($data['items']);

            $bom = BillOfMaterial::create($data);
            $companyId = $bom->company_id;

            $estimatedMaterialCost = 0;
            foreach ($itemsData as $item) {
                $qty = (float)($item['quantity'] ?? 0);
                $unitCost = (float)($item['unit_cost'] ?? 0);
                $wastage = (float)($item['wastage_percentage'] ?? 0);
                
                // Base cost + wastage factor
                $effectiveQty = $qty * (1 + ($wastage / 100));
                $totalCost = round($effectiveQty * $unitCost, 2);
                $estimatedMaterialCost += $totalCost;

                BomItem::create([
                    'company_id' => $companyId,
                    'bill_of_material_id' => $bom->id,
                    'product_id' => $item['product_id'],
                    'product_variation_id' => $item['product_variation_id'] ?? null,
                    'component_type' => $item['component_type'] ?? 'raw_material',
                    'quantity' => $qty,
                    'unit' => $item['unit'] ?? 'PCS',
                    'unit_cost' => $unitCost,
                    'wastage_percentage' => $wastage,
                    'total_cost' => $totalCost,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            $overheadSum = (float)($bom->labor_cost ?? 0) +
                (float)($bom->machine_cost ?? 0) +
                (float)($bom->electricity_cost ?? 0) +
                (float)($bom->overhead_cost ?? 0) +
                (float)($bom->packaging_cost ?? 0) +
                (float)($bom->other_cost ?? 0);

            $bom->update([
                'estimated_material_cost' => $estimatedMaterialCost,
                'total_cost' => $estimatedMaterialCost + $overheadSum,
            ]);

            DB::commit();
            return $this->getById($bom->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating BOM: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create Bill of Material: ' . $e->getMessage());
        }
    }

    public function update(int $id, array $data): BillOfMaterial
    {
        DB::beginTransaction();
        try {
            $bom = $this->getById($id);
            $companyId = $bom->company_id;

            $itemsData = $data['items'] ?? null;
            unset($data['items']);

            $bom->update($data);

            if ($itemsData !== null) {
                // Delete old items and recreate
                BomItem::where('bill_of_material_id', $bom->id)->delete();

                $estimatedMaterialCost = 0;
                foreach ($itemsData as $item) {
                    $qty = (float)($item['quantity'] ?? 0);
                    $unitCost = (float)($item['unit_cost'] ?? 0);
                    $wastage = (float)($item['wastage_percentage'] ?? 0);

                    $effectiveQty = $qty * (1 + ($wastage / 100));
                    $totalCost = round($effectiveQty * $unitCost, 2);
                    $estimatedMaterialCost += $totalCost;

                    BomItem::create([
                        'company_id' => $companyId,
                        'bill_of_material_id' => $bom->id,
                        'product_id' => $item['product_id'],
                        'product_variation_id' => $item['product_variation_id'] ?? null,
                        'component_type' => $item['component_type'] ?? 'raw_material',
                        'quantity' => $qty,
                        'unit' => $item['unit'] ?? 'PCS',
                        'unit_cost' => $unitCost,
                        'wastage_percentage' => $wastage,
                        'total_cost' => $totalCost,
                        'notes' => $item['notes'] ?? null,
                    ]);
                }

                $overheadSum = (float)($bom->labor_cost ?? 0) +
                    (float)($bom->machine_cost ?? 0) +
                    (float)($bom->electricity_cost ?? 0) +
                    (float)($bom->overhead_cost ?? 0) +
                    (float)($bom->packaging_cost ?? 0) +
                    (float)($bom->other_cost ?? 0);

                $bom->update([
                    'estimated_material_cost' => $estimatedMaterialCost,
                    'total_cost' => $estimatedMaterialCost + $overheadSum,
                ]);
            } else {
                $bom->recalculateTotals();
            }

            DB::commit();
            return $this->getById($bom->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating BOM: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update Bill of Material: ' . $e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        $bom = $this->getById($id);
        $bom->delete();
    }

    public function clone(int $id, ?string $newVersion = null): BillOfMaterial
    {
        $bom = $this->getById($id);
        $data = $bom->toArray();

        unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
        $data['bom_number'] = $this->generateBomNumber();
        $data['version'] = $newVersion ?: ($bom->version . '-copy');
        $data['status'] = 'draft';
        $data['items'] = $bom->items->toArray();

        return $this->create($data);
    }

    private function generateBomNumber(): string
    {
        $prefix = 'BOM';
        $setting = ProductionSetting::first();
        if ($setting && !empty($setting->bom_prefix)) {
            $prefix = $setting->bom_prefix;
        }

        $date = now()->format('Ymd');
        $count = BillOfMaterial::whereDate('created_at', now())->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }
}
