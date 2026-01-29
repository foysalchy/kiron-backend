<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\PaymentMethodType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class PaymentMethodTypeService
{
    /**
     * Get all payment method types with optional filtering and pagination.
     */
    public function getAllPaymentMethodTypes(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = PaymentMethodType::query();

            if (!empty($filters['search'])) {
                $query->where('payment_method', 'like', "%{$filters['search']}%");
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
            Log::error('Error fetching payment method types: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch payment method types');
        }
    }
    /**
     * Get method type by ID
     */
    public function getMethodTypeById(int $id): PaymentMethodType
    {
        $type = PaymentMethodType::find($id);
        if (!$type) {
            throw ApiException::notFound('Payment Method Type');
        }
        return $type;
    }
     /**
     * Create method type
     */
    public function createMethodType(array $data): PaymentMethodType
    {
        DB::beginTransaction();
        try {
           $type = PaymentMethodType::create($data);

            LogHelper::created('paymentMethodType', $type->id, $type->company_id, $type->name);
            DB::commit();
            Log::info('Payment method type created successfully', ['id' => $type->id]);

            return $type;
            } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment method type creation failed: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw ApiException::serverError('Failed to create payment method type');
        }
    }
    /**
     * Update an existing payment method type.
     */
    public function updatePaymentMethodType(int $id, array $data): PaymentMethodType
    {
        DB::beginTransaction();
        try {
            $type = $this->getMethodTypeById($id);

            $type->update($data);

            LogHelper::updated('paymentMethodType', $type->id, $type->company_id, $type->name);

            DB::commit();
            Log::info('Payment method type updated successfully', ['id' => $id]);

            return $type->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment method type update failed: ' . $e->getMessage(), ['id' => $id]);
            throw ApiException::serverError('Failed to update payment method type');
        }
    }

    /**
     * Delete payment method type (Soft Delete).
     */
    public function deletePaymentMethodType(int $id): bool
    {
        try {
            $type = $this->getMethodTypeById($id);

            $type->delete();

            LogHelper::deleted('paymentMethodType', $type->id, $type->company_id, $type->name);
            Log::info('Payment method type deleted successfully', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Payment method type deletion failed: ' . $e->getMessage(), ['id' => $id]);
            throw ApiException::serverError('Failed to delete payment method type');
        }
    }

    /**
     * Restore a soft-deleted payment method type.
     */
    public function restorePaymentMethodType(int $id): PaymentMethodType
    {
        try {
            $type = PaymentMethodType::withTrashed()->find($id);

            if (!$type) {
                throw ApiException::notFound('Payment Method Type');
            }

            $type->restore();

            LogHelper::restored('paymentMethodType', $type->id, $type->company_id, $type->name);
            Log::info('Payment method type restored successfully', ['id' => $id]);

            return $type;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Payment method type restoration failed: ' . $e->getMessage(), ['id' => $id]);
            throw ApiException::serverError('Failed to restore payment method type');
        }
    }

    /**
     * Permanently delete a payment method type.
     */
    public function forceDeletePaymentMethodType(int $id): bool
    {
        DB::beginTransaction();
        try {
            $type = PaymentMethodType::withTrashed()->find($id);

            if (!$type) {
                throw ApiException::notFound('Payment Method Type');
            }

            $type->forceDelete();

            LogHelper::forceDeleted('paymentMethodType', $type->id, $type->company_id, $type->name);

            DB::commit();
            Log::info('Payment method type permanently deleted', ['id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment method type permanent deletion failed: ' . $e->getMessage(), ['id' => $id]);
            throw ApiException::serverError('Failed to permanently delete payment method type');
        }
    }
}
