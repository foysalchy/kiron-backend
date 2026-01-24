<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\PayHead;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};


class PayHeadService
{
    /**
     * Get all pay heads with optional pagination
     */
    public function getAllPayHeads(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = PayHead::query();

            // Search by name
            if (isset($filters['search'])) {
                $query->where('name', 'like', "%{$filters['search']}%");
            }
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }
            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching pay heads: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch pay heads');
        }
    }
    /**
     * Get pay head by ID
     */
    public function getPayHeadById(int $id): PayHead
    {
        $payHead = PayHead::find($id);

        if (!$payHead) {
            throw ApiException::notFound('Pay Head');
        }

        return $payHead;
    }
    /**
     * Create a new pay head
     */
    public function createPayHead(array $data): PayHead
    {
        DB::beginTransaction();

        try {
            $payHead = PayHead::create($data);
            LogHelper::created('payHead', $payHead->id, $payHead->company_id,$payHead->name);

            DB::commit();

            Log::info('Pay head created successfully', ['pay_head_id' => $payHead->id]);

            return $payHead;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Pay head creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to create pay head');
        }
    }
    /**
     * Update pay head
     */
    public function updatePayHead(int $id, array $data): PayHead
    {
        DB::beginTransaction();

        try {
            $payHead = $this->getPayHeadById($id);

            $payHead->update($data);
            LogHelper::updated('payHead', $payHead->id, $payHead->company_id,$payHead->name);

            DB::commit();

            Log::info('Pay head updated successfully', ['pay_head_id' => $payHead->id]);

            return $payHead->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Pay head update failed: ' . $e->getMessage(), [
                'pay_head_id' => $id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to update pay head');
        }
    }

    /**
     * Delete pay head (soft delete)
     */
    public function deletePayHead(int $id): bool
    {
        try {
            $payHead = $this->getPayHeadById($id);

            $payHead->delete();
            LogHelper::deleted('payHead', $payHead->id, $payHead->company_id,$payHead->name);

            Log::info('Pay head deleted successfully', ['pay_head_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Pay head deletion failed: ' . $e->getMessage(), [
                'pay_head_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            throw ApiException::serverError('Failed to delete pay head');
        }
    }

    /**
     * Restore soft deleted pay head
     */
    public function restorePayHead(int $id): PayHead
    {
        try {
            $payHead = PayHead::withTrashed()->find($id);

            if (!$payHead) {
                throw ApiException::notFound('PayHead');
            }

            $payHead->restore();
            LogHelper::restored('payHead', $payHead->id, $payHead->company_id,$payHead->name);

            Log::info('Pay head restored successfully', ['pay_head_id' => $id]);

            return $payHead;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Pay head restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore pay head');
        }
    }

    /**
     * Permanently delete pay head
     */
    public function forceDeletePayHead(int $id): bool
    {
        DB::beginTransaction();

        try {
            $payHead = PayHead::withTrashed()->find($id);

            if (!$payHead) {
                throw ApiException::notFound('PayHead');
            }

            $payHead->forceDelete();
            LogHelper::forceDeleted('payHead', $payHead->id, $payHead->company_id,$payHead->name);

            DB::commit();

            Log::info('Pay head permanently deleted', ['pay_head_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pay head permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete pay head');
        }
    }
}
