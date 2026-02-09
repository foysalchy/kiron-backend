<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\{Requisition, RequisitionDetail};
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class RequisitionService
{
    /**
     * Get all requisitions with optional pagination
     */
    public function getAllRequisitions(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Requisition::with([
                'user',
                'requisitionDetails.product',
                'requisitionDetails.variation.attributes.attributeGroup',
                'requisitionDetails.variation.attributes.attributeValue'
            ]);

            if (isset($filters['user_id'])) {
                $query->where('user_id', $filters['user_id']);
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (isset($filters['request_date_from'])) {
                $query->whereDate('request_date', '>=', $filters['request_date_from']);
            }

            if (isset($filters['request_date_to'])) {
                $query->whereDate('request_date', '<=', $filters['request_date_to']);
            }

            if (isset($filters['need_date_from'])) {
                $query->whereDate('need_date', '>=', $filters['need_date_from']);
            }

            if (isset($filters['need_date_to'])) {
                $query->whereDate('need_date', '<=', $filters['need_date_to']);
            }

            if (isset($filters['search'])) {
                $query->where('requisition_number', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'request_date';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching requisitions: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch requisitions');
        }
    }

    /**
     * Get requisition by ID
     */
    public function getRequisitionById(int $id): Requisition
    {
        $requisition = Requisition::with([
            'user',
            'requisitionDetails.product',
            'requisitionDetails.variation.attributes.attributeGroup',
            'requisitionDetails.variation.attributes.attributeValue'
        ])->find($id);

        if (!$requisition) {
            throw ApiException::notFound('Requisition');
        }

        return $requisition;
    }

    /**
     * Create a new requisition
     */
    public function createRequisition(array $data): Requisition
    {
        DB::beginTransaction();

        try {
            $items = $data['items'];
            unset($data['items']);


            // Calculate total amount
            $data['total_amount'] = $this->calculateTotalAmount($items);

            // Default status is pending (2)
            $data['status'] = $data['status'] ?? Status::Pending->value;

            // Create requisition
            $requisition = Requisition::create($data);

            // Create requisition details
            foreach ($items as $item) {
                $itemAmount = $this->calculateItemAmount($item);

                RequisitionDetail::create([
                    'requisition_id' => $requisition->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'] ?? null,
                    'unit' => $item['unit'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] ?? 0,
                    'amount' => $itemAmount,
                ]);
            }

            DB::commit();

            Log::info('Requisition created successfully', ['requisition_id' => $requisition->id]);
            LogHelper::created('requisition', $requisition->id, $requisition->company_id);

            return $requisition->load(['requisitionDetails']);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Requisition creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create requisition');
        }
    }

    /**
     * Update requisition
     */
    public function updateRequisition(int $id, array $data): Requisition
    {
        DB::beginTransaction();

        try {
            $requisition = $this->getRequisitionById($id);

            // Check if requisition can be edited
            if ($requisition->isCompleted()) {
                throw ApiException::badRequest('Cannot update completed requisition');
            }

            if ($requisition->isRejected()) {
                throw ApiException::badRequest('Cannot update rejected requisition');
            }

            $items = $data['items'] ?? null;
            unset($data['items']);

            // If items are provided, recalculate total amount
            if ($items) {
                $data['total_amount'] = $this->calculateTotalAmount($items);

                // Delete old details and create new ones
                $requisition->requisitionDetails()->delete();

                foreach ($items as $item) {
                    $itemAmount = $this->calculateItemAmount($item);

                    RequisitionDetail::create([
                        'requisition_id' => $requisition->id,
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'] ?? null,
                        'unit' => $item['unit'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'] ?? 0,
                        'amount' => $itemAmount,
                    ]);
                }
            }

            $requisition->update($data);

            DB::commit();

            Log::info('Requisition updated successfully', ['requisition_id' => $requisition->id]);
            LogHelper::updated('requisition', $requisition->id, $requisition->company_id);

            return $requisition->fresh(['requisitionDetails']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Requisition update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update requisition');
        }
    }

    /**
     * Delete requisition (soft delete)
     */
    public function deleteRequisition(int $id): bool
    {
        DB::beginTransaction();

        try {
            $requisition = $this->getRequisitionById($id);

            // Only pending requisitions can be deleted
            if (!$requisition->isPending()) {
                throw ApiException::badRequest('Only pending requisitions can be deleted');
            }

            $requisition->delete();

            DB::commit();

            Log::info('Requisition deleted successfully', ['requisition_id' => $id]);
            LogHelper::deleted('requisition', $id, $requisition->company_id);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Requisition deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete requisition');
        }
    }

    /**
     * Calculate item amount
     */
    private function calculateItemAmount(array $item): float
    {
        $quantity = $item['quantity'];
        $price = $item['price'] ?? 0;

        $amount = $quantity * $price;

        return round($amount, 2);
    }

    /**
     * Calculate total amount
     */
    private function calculateTotalAmount(array $items): float
    {
        $totalAmount = 0;

        foreach ($items as $item) {
            $totalAmount += $this->calculateItemAmount($item);
        }

        return round($totalAmount, 2);
    }



    /**
     * Approve requisition
     */
    public function approveRequisition(int $id): Requisition
    {
        DB::beginTransaction();

        try {
            $requisition = $this->getRequisitionById($id);

            if (!$requisition->isPending()) {
                throw ApiException::badRequest('Only pending requisitions can be approved');
            }

            $requisition->update([
                'status' => Status::Approved->value,
                'reject_reason' => null,
            ]);

            DB::commit();

            Log::info('Requisition approved', ['requisition_id' => $id]);
            LogHelper::custom('approved', 'requisition', $id, $requisition->company_id);

            return $requisition;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Requisition approval failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to approve requisition');
        }
    }

    /**
     * Reject requisition
     */
    public function rejectRequisition(int $id, string $reason): Requisition
    {
        DB::beginTransaction();

        try {
            $requisition = $this->getRequisitionById($id);

            if (!$requisition->isPending()) {
                throw ApiException::badRequest('Only pending requisitions can be rejected');
            }

            $requisition->update([
                'status' => Status::Cancelled->value,
                'reject_reason' => $reason,
            ]);

            DB::commit();

            Log::info('Requisition rejected', ['requisition_id' => $id]);
            LogHelper::custom('rejected', 'requisition', $id, $requisition->company_id);

            return $requisition;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Requisition rejection failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to reject requisition');
        }
    }

    /**
     * Complete requisition
     */
    public function completeRequisition(int $id): Requisition
    {
        DB::beginTransaction();

        try {
            $requisition = $this->getRequisitionById($id);

            if (!$requisition->isApproved()) {
                throw ApiException::badRequest('Only approved requisitions can be completed');
            }

            $requisition->update([
                'status' => Status::Completed->value,
            ]);

            DB::commit();

            Log::info('Requisition completed', ['requisition_id' => $id]);
            LogHelper::custom('completed', 'requisition', $id, $requisition->company_id);

            return $requisition;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Requisition completion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to complete requisition');
        }
    }

    /**
     * Change requisition status
     */
    public function changeStatus(int $id, int $status, ?string $rejectReason = null): Requisition
    {
        try {
            $requisition = $this->getRequisitionById($id);

            $updateData = ['status' => $status];

            if ($status === Status::Cancelled->value && $rejectReason) {
                $updateData['reject_reason'] = $rejectReason;
            } elseif ($status !== Status::Cancelled->value) {
                $updateData['reject_reason'] = null;
            }

            $requisition->update($updateData);

            Log::info('Requisition status changed', ['requisition_id' => $id, 'status' => $status]);
            LogHelper::custom('status_changed', 'requisition', $id, $requisition->company_id);

            return $requisition;
        } catch (\Exception $e) {
            Log::error('Requisition status change failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to change status');
        }
    }
    public function restoreRequisition(int $id): Requisition
    {
        DB::beginTransaction();

        try {
            $requisition = Requisition::withTrashed()->find($id);

            if (!$requisition) {
                throw ApiException::notFound('Requisition');
            }

            $requisition->restore();

            DB::commit();

            Log::info('Requisition restored successfully', ['requisition_id' => $id]);
            LogHelper::custom('restored', 'requisition', $id, $requisition->company_id);

            return $requisition->load(['user', 'requisitionDetails.product']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Requisition restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore requisition');
        }
    }

    /**
     * Permanently delete a requisition
     */
    public function forceDeleteRequisition(int $id): bool
    {
        DB::beginTransaction();

        try {
            $requisition = Requisition::withTrashed()->find($id);

            if (!$requisition) {
                throw ApiException::notFound('Requisition');
            }

            // Delete all requisition details first
            RequisitionDetail::where('requisition_id', $id)->delete();

            // Permanently delete the requisition
            $companyId = $requisition->company_id;
            $requisition->forceDelete();

            DB::commit();

            Log::info('Requisition permanently deleted', ['requisition_id' => $id]);
            LogHelper::custom('force_deleted', 'requisition', $id, $companyId);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Permanent requisition deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete requisition');
        }
    }
}
