<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\CustomerPaymentMethod;
use App\Models\PaymentMethodType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class CustomerPaymentMethodService
{
    /**
     * Get all customer payment methods with optional pagination
     */
    public function getAllCustomerPaymentMethods(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = CustomerPaymentMethod::query()->with(['paymentMethodType']);

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['name'])) {
                $query->where('name', $filters['name']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('account_holder', 'like', "%{$search}%")
                        ->orWhere('account_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching customer payment methods: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch data');
        }
    }

    /**
     * Get customer payment method by ID
     */
    public function getCustomerPaymentMethodById(int $id): CustomerPaymentMethod
    {
        $method = CustomerPaymentMethod::find($id);

        if (!$method) {
            throw ApiException::notFound('Customer Payment Method');
        }

        return $method;
    }

    /**
     * Create a new customer payment method
     */
    public function createCustomerPaymentMethod(array $data): CustomerPaymentMethod
    {
        DB::beginTransaction();

        try {

            if (isset($data['icon'])) {
                $data['icon'] = FileUploadHelper::uploadImage(
                    $data['icon'],
                    'customer_payments/icons',
                   
                );
            }

            $method = CustomerPaymentMethod::create($data);
            LogHelper::created('customer_payment_method', $method->id, $method->company_id, $method->contact_name );

            DB::commit();
            Log::info('Customer Payment Method created successfully', ['id' => $method->id]);

            return $method;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['icon'])) {
                FileUploadHelper::delete($data['icon']);
            }
            Log::error('Customer Payment Method creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create customer payment method');
        }
    }

    /**
     * Update customer payment method
     */
    public function updateCustomerPaymentMethod(int $id, array $data): CustomerPaymentMethod
    {
        DB::beginTransaction();

        try {
            $method = $this->getCustomerPaymentMethodById($id);

            if (isset($data['icon'])) {
                $data['icon'] = FileUploadHelper::replace(
                    $data['icon'],
                    $method->icon,
                    'customer_payments/icons'
                );
            }

            $method->update($data);
            LogHelper::updated('customer_payment_method', $method->id, $method->company_id, $method->contact_name);

            DB::commit();
            Log::info('Customer Payment Method updated successfully', ['id' => $method->id]);

            return $method->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['icon'])) {
                FileUploadHelper::delete($data['icon']);
            }
            Log::error('Customer Payment Method update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update customer payment method');
        }
    }

    /**
     * Delete customer payment method (soft delete)
     */
    public function deleteCustomerPaymentMethod(int $id): bool
    {
        DB::beginTransaction();
        try {
            $method = $this->getCustomerPaymentMethodById($id);
            $method->delete();
            LogHelper::deleted('customer_payment_method', $method->id, $method->company_id, $method->account_holder);

            DB::commit();
            Log::info('Customer Payment Method deleted successfully', ['id' => $id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Customer Payment Method deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete data');
        }
    }

    /**
     * Restore record
     */
    public function restoreCustomerPaymentMethod(int $id): CustomerPaymentMethod
    {
        DB::beginTransaction();
        try {
            $method = CustomerPaymentMethod::withTrashed()->find($id);
            if (!$method) throw ApiException::notFound('Customer Payment Method');

            $method->restore();
            LogHelper::restored('customer_payment_method', $method->id, $method->company_id, $method->account_holder);

            DB::commit();
            return $method;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }

    /**
     * Permanently delete record
     */
    public function forceDeleteCustomerPaymentMethod(int $id): bool
    {
        DB::beginTransaction();
        try {
            $method = CustomerPaymentMethod::withTrashed()->find($id);
            if (!$method) throw ApiException::notFound('Customer Payment Method');

            // Delete icon permanently
            FileUploadHelper::delete($method->icon);

            $method->forceDelete();
            LogHelper::forceDeleted('customer_payment_method', $method->id, $method->company_id, $method->account_holder);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete data');
        }
    }

    /**
     * Toggle status
     */
    public function toggleStatus(int $id): CustomerPaymentMethod
    {
        DB::beginTransaction();
        try {
            $method = $this->getCustomerPaymentMethodById($id);
            $currentStatus = Status::from($method->status);
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            $method->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('customer_payment_method', $method->id, $method->company_id, $method->account_holder . ' new status '.$newStatus->label());

            DB::commit();
            return $method;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
