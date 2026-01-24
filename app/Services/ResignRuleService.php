<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\ResignRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class ResignRuleService
{
    /**
     * Get all resign rules with filters and pagination
     */
    public function getAllResignRules(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = ResignRule::query();

            // Apply Status Filter
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Apply Search Filter
            if (isset($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching resign rules: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch resign rules');
        }
    }
   /**
     * Get Resign Rule by ID
     */
    public function getResignRuleById(int $id): ResignRule
    {
        $rule = ResignRule::find($id);

        if (!$rule) {
            throw ApiException::notFound('Resign Rule');
        }

        return $rule;
    }
    /**
     * Create a new Resign Rule
     */
    public function createResignRule(array $data): ResignRule
    {
        DB::beginTransaction();
        try {
            $rule = ResignRule::create($data);

            LogHelper::created('resign_rule', $rule->id, $rule->company_id, $rule->name);
            DB::commit();
            Log::info('Resign rule created successfully', ['rule_id' => $rule->id]);

            return $rule;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resign Rule creation failed: ' . $e->getMessage(), ['data' => $data]);
            throw ApiException::serverError('Failed to create Resign Rule');
        }
    }

    /**
     * Update Resign Rule
     */
    public function updateResignRule(int $id, array $data): ResignRule
    {
        DB::beginTransaction();
        try {
            $rule = $this->getResignRuleById($id);
            $rule->update($data);

            LogHelper::updated('resign_rule', $rule->id, $rule->company_id, $rule->name);
            DB::commit();
            Log::info('Resign Rule updated successfully', ['rule_id' => $id]);

            return $rule->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resign Rule update failed: ' . $e->getMessage(), ['rule_id' => $id]);
            throw ApiException::serverError('Failed to update Resign Rule');
        }
    }

    /**
     * Soft Delete Resign Rule
     */
    public function deleteResignRule(int $id): bool
    {
        DB::beginTransaction();
        try {
            $rule = $this->getResignRuleById($id);
            $rule->delete();

            LogHelper::deleted('resign_rule', $rule->id, $rule->company_id, $rule->name);
            DB::commit();
            Log::info('Resign Rule soft deleted', ['rule_id' => $id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resign Rule deletion failed: ' . $e->getMessage(), ['rule_id' => $id]);
            throw ApiException::serverError('Failed to delete Resign Rule');
        }
    }

    /**
     * Restore Resign Rule
     */
    public function restoreResignRule(int $id): ResignRule
    {
        DB::beginTransaction();
        try {
            $rule = ResignRule::withTrashed()->find($id);

            if (!$rule) {
                throw ApiException::notFound('Resign Rule');
            }

            $rule->restore();

            LogHelper::restored('resign_rule', $rule->id, $rule->company_id, $rule->name);
            DB::commit();
            Log::info('Resign Rule restored successfully', ['rule_id' => $id]);
            return $rule;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resign Rule restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore Resign Rule');
        }
    }
    /**
     * Permanently delete the Resign Rule
     */
    public function forceDeleteResignRule(int $id): bool
    {
        DB::beginTransaction();

        try {
            $rule = ResignRule::withTrashed()->find($id);

            if (!$rule) {
                throw ApiException::notFound('Resign Rule');
            }
            $rule->forceDelete();

            LogHelper::forceDeleted('resign_rule', $rule->id, $rule->company_id, $rule->name);

            DB::commit();

            Log::info('Resign Rule permanently deleted', ['rule_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resign Rule permanent deletion failed: ' . $e->getMessage());

            throw ApiException::serverError('Failed to permanently delete Resign Rule');
        }
    }

    /**
     * Toggle Status (Active/Inactive)
     */
    public function toggleStatus(int $id): ResignRule
    {
        DB::beginTransaction();
        try {
            $rule = $this->getResignRuleById($id);
            $currentStatus = Status::from($rule->status);

            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $rule->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('resign_rule', $rule->id, $rule->company_id, $rule->name . ' new status ' . $newStatus->label());
            DB::commit();
            Log::info('Resign Rule status toggled', ['rule_id' => $id, 'new_status' => $newStatus->value]);

            return $rule;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resign Rule status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
