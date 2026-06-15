<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\CustomerPaymentMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, DB, Log};

class CustomerPaymentMethodService
{
    /**
     * Authenticated user is super admin?
     */
    private function isSuperAdmin(): bool
    {
        return Auth::check() && Auth::user()->is_superadmin == 1;
    }

    /**
     * Normal user-এর company_id রিটার্ন করে।
     * Super admin হলে null।
     */
    private function resolveCompanyId(): ?int
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        return Auth::user()->company_id;
    }

    private function applyCompanyScope($query, array $filters = [])
    {
        if ($this->isSuperAdmin()) {
            $query->whereNull('company_id');
        } else {
            $query->where('company_id', Auth::user()->company_id);
        }

        return $query;
    }

    /**
     * Get all customer payment methods with optional pagination
     */
    public function getAllCustomerPaymentMethods(
        array $filters = [],
        bool $paginate = true
    ): Collection|LengthAwarePaginator {
        try {
            $query = CustomerPaymentMethod::query();

            $this->applyCompanyScope($query, $filters);

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['name'])) {
                $query->where('name', strtolower($filters['name']));
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

            $sortBy    = $filters['sort_by']    ?? 'created_at';
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
     * Normal user শুধু নিজের company-র record access করতে পারবে
     */
    public function getCustomerPaymentMethodById(int $id): CustomerPaymentMethod
    {
        $query = CustomerPaymentMethod::query();
        $this->applyCompanyScope($query);

        $method = $query->find($id);

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
            // ✅ Super admin → null, Normal user → their company_id
            $data['company_id'] = $this->resolveCompanyId();

            if (isset($data['icon'])) {
                $data['icon'] = FileUploadHelper::uploadImage(
                    $data['icon'],
                    'customer_payments/icons'
                );
            }

            $method = CustomerPaymentMethod::create($data);

            DB::commit();
            return $method;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['icon'])) FileUploadHelper::delete($data['icon']);
            Log::error('Creation failed: ' . $e->getMessage());
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

            if (!$this->isSuperAdmin()) {
                unset($data['company_id']);
            }

            if (isset($data['icon'])) {
                $data['icon'] = FileUploadHelper::replace(
                    $data['icon'],
                    $method->icon,
                    'customer_payments/icons'
                );
            }

            $method->update($data);



            DB::commit();
            Log::info('Customer Payment Method updated', ['id' => $method->id]);

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
     * Soft delete
     */
    public function deleteCustomerPaymentMethod(int $id): bool
    {
        DB::beginTransaction();
        try {
            $method = $this->getCustomerPaymentMethodById($id);
            $method->delete();

            LogHelper::deleted(
                'customer_payment_method',
                $method->id,
                $method->company_id,
                $method->account_holder
            );

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete data');
        }
    }

    /**
     * Restore
     */
    public function restoreCustomerPaymentMethod(int $id): CustomerPaymentMethod
    {
        DB::beginTransaction();
        try {
            $query = CustomerPaymentMethod::withTrashed();
            $this->applyCompanyScope($query);

            $method = $query->find($id);
            if (!$method) throw ApiException::notFound('Customer Payment Method');

            $method->restore();

            LogHelper::restored(
                'customer_payment_method',
                $method->id,
                $method->company_id,
                $method->account_holder
            );

            DB::commit();
            return $method;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Restore failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore data');
        }
    }

    /**
     * Force delete
     */
    public function forceDeleteCustomerPaymentMethod(int $id): bool
    {
        DB::beginTransaction();
        try {
            $query = CustomerPaymentMethod::withTrashed();
            $this->applyCompanyScope($query);

            $method = $query->find($id);
            if (!$method) throw ApiException::notFound('Customer Payment Method');

            FileUploadHelper::delete($method->icon);
            $method->forceDelete();

            LogHelper::forceDeleted(
                'customer_payment_method',
                $method->id,
                $method->company_id,
                $method->account_holder
            );

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Force delete failed: ' . $e->getMessage());
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

            LogHelper::statusChanged(
                'customer_payment_method',
                $method->id,
                $method->company_id,
                $method->account_holder . ' → ' . $newStatus->label()
            );

            DB::commit();
            return $method->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }

    public function getPublicCustomerPaymentMethods(): Collection
    {
        try {
            return CustomerPaymentMethod::whereNull('company_id')
                ->where('status', Status::Active->value)
                ->get();
        } catch (\Exception $e) {
            Log::error('Public fetch failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch data');
        }
    }
}
