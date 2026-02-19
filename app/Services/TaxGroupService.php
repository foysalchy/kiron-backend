<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\TaxGroup;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class TaxGroupService
{
    /**
     * Get all tax groups with filters
     */
    public function getAllTaxGroups(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = TaxGroup::query();

            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('total_rate', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Exception $e) {
            Log::error('Tax Group list fetch failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch tax groups');
        }
    }
    /**
     * Get single tax group by ID
     */
    public function getTaxGroupById(int $id): TaxGroup
    {
        $taxGroup = TaxGroup::find($id);
        if (!$taxGroup) {
            throw ApiException::notFound('Tax Group');
        }
        return $taxGroup;
    }

    /**
     * Create a new Tax Group
     */
    public function createTaxGroup(array $data): TaxGroup
    {
        DB::beginTransaction();
        try {
            $data['total_rate'] = $this->calculateTotalRate($data['sub_tax']);

            $taxGroup = TaxGroup::create($data);

            LogHelper::created('tax_group', $taxGroup->id, $taxGroup->company_id);
            DB::commit();

            return $taxGroup;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create tax group');
        }
    }
    /**
     * Update an existing Tax Group
     */
    public function updateTaxGroup(int $id, array $data): TaxGroup
    {
        DB::beginTransaction();
        try {
            $taxGroup = TaxGroup::findOrFail($id);

            if (isset($data['sub_tax'])) {
                $data['total_rate'] = $this->calculateTotalRate($data['sub_tax']);
            }

            $taxGroup->update($data);

            LogHelper::updated('tax_group', $taxGroup->id, $taxGroup->company_id);
            DB::commit();

            return $taxGroup;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update tax group');
        }
    }
    /**
     * Soft delete tax rate
     */
    public function deleteTaxGroup(int $id): bool
    {
        DB::beginTransaction();
        try {
            $taxGroup = $this->getTaxGroupById($id);
            $taxGroup->delete();

            LogHelper::deleted('tax_group', $taxGroup->id, $taxGroup->company_id, $taxGroup->name);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete tax group');
        }
    }
    /**
     * Restore soft deleted tax group
     */
    public function restoreTaxGroup(int $id): TaxGroup
    {
        DB::beginTransaction();
        try {
            $taxGroup = TaxGroup::withTrashed()->find($id);
            if (!$taxGroup) {
                throw ApiException::notFound('Tax Group');
            }
            $taxGroup->restore();

            LogHelper::restored('tax_rate', $taxGroup->id, $taxGroup->company_id, $taxGroup->id);
            DB::commit();
            return $taxGroup;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore tax group');
        }
    }
    /**
     * Permanently delete a tax group
     */
    public function forceDeleteTaxGroup(int $id): bool
    {
        DB::beginTransaction();
        try {
            $taxGroup = TaxRate::withTrashed()->find($id);

            if (!$taxGroup) {
                throw ApiException::notFound('Tax Group');
            }

            $taxGroup->forceDelete();

            LogHelper::forceDeleted('tax_group', $id, $taxGroup->company_id, $taxGroup->id);
            Log::info('Tax Group permanently deleted', ['tax_group_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tax Group permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete tax group');
        }
    }
    /**
     * Helper: Calculate total rate from sub_tax names
     */
    private function calculateTotalRate(array $subTaxNames): float
    {
        return (float) TaxRate::whereIn('name', $subTaxNames)->sum('tax_rate');
    }
}
