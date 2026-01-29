<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\WocommerceSetting;
use App\Models\WooCommerceIntegration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class WocommerceSettingService
{
    /**
     * Get all WooCommerce settings with optional pagination
     */
    public function getAllSettings(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = WocommerceSetting::query();

            // Status filter (Handling Trashed logic like Period)
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Domain URL
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('domain_url', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Throwable $e) {
            Log::error('Error fetching WooCommerce settings: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch settings');
        }
    }

    /**
     * Get setting by ID
     */
    public function getSettingById(int $id): WocommerceSetting
    {
        $setting = WocommerceSetting::find($id);
        if (!$setting) {
            throw ApiException::notFound('WooCommerce Setting');
        }
        return $setting;
    }

    /**
     * Create a new WooCommerce setting
     */
    public function createSetting(array $data): WocommerceSetting
    {
        DB::beginTransaction();
        try {
            $setting = WocommerceSetting::create($data);

            LogHelper::created('woocommerce_setting', $setting->id, $setting->company_id, $setting->domain_url);

            DB::commit();
            Log::info('WooCommerce setting created successfully', ['id' => $setting->id]);

            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce setting creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create setting: ' . $e->getMessage());
        }
    }

    /**
     * Update WooCommerce setting
     */
    public function updateSetting(int $id, array $data): WocommerceSetting
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSettingById($id);
            $setting->update($data);

            LogHelper::updated('woocommerce_setting', $setting->id, $setting->company_id, $setting->domain_url);

            DB::commit();
            return $setting->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce setting update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update setting');
        }
    }

    /**
     * Delete setting (soft delete)
     */
    public function deleteSetting(int $id): bool
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSettingById($id);
            $setting->delete();

            LogHelper::deleted('woocommerce_setting', $setting->id, $setting->company_id, $setting->domain_url);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce setting deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete setting');
        }
    }

    /**
     * Restore soft deleted WooCommerce setting
     */
    public function restoreSetting(int $id): WocommerceSetting
    {
        DB::beginTransaction();
        try {
            $setting = WocommerceSetting::withTrashed()->find($id);

            if (!$setting) {
                throw ApiException::notFound('WooCommerce Setting');
            }

            $setting->restore();

            LogHelper::restored('woocommerce_setting', $setting->id, $setting->company_id, $setting->domain_url);

            DB::commit();
            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce setting restore failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore setting');
        }
    }

    /**
     * Permanently delete a WooCommerce setting
     */
    public function forceDeleteSetting(int $id): bool
    {
        DB::beginTransaction();
        try {
            $setting = WocommerceSetting::withTrashed()->find($id);

            if (!$setting) {
                throw ApiException::notFound('WooCommerce Setting');
            }

            $setting->forceDelete();

            LogHelper::forceDeleted('woocommerce_setting', $id, $setting->company_id, $setting->domain_url);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce setting permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete setting');
        }
    }

    /**
     * Toggle status (Active/Inactive)
     */
    public function toggleStatus(int $id): WocommerceSetting
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSettingById($id);

            $currentStatus = Status::from($setting->status);
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $setting->update(['status' => $newStatus->value]);

            LogHelper::statusChanged(
                'woocommerce_setting',
                $setting->id,
                $setting->company_id,
                $setting->domain_url . ' new status ' . $newStatus->label()
            );

            DB::commit();
            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
