<?php 
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\IpSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class IpSettingService
{ 
    /**
     * Get all IP settings with optional pagination
     */
    public function getAllIpSettings(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = IpSetting::query();

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching IP settings: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch IP settings');
        }
    }

    /**
     * Get IP setting by ID
     */
    public function getIpSettingById(int $id): IpSetting
    {
        $setting = IpSetting::find($id);

        if (!$setting) {
            throw ApiException::notFound('IP Setting');
        }

        return $setting;
    }

    /**
     * Create a new IP setting
     */
    public function createIpSetting(array $data): IpSetting
    {
        DB::beginTransaction();
        try {
            $setting = IpSetting::create($data);
            LogHelper::created('ip_setting', $setting->id, $setting->company_id);

            DB::commit();
            Log::info('IP setting created successfully', ['setting_id' => $setting->id]);

            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP setting creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create IP setting');
        }
    }

    /**
     * Update IP setting
     */
    public function updateIpSetting(int $id, array $data): IpSetting
    {
        DB::beginTransaction();
        try {
            $setting = $this->getIpSettingById($id);
            $setting->update($data);

            LogHelper::updated('ip_setting', $setting->id, $setting->company_id);

            DB::commit();
            Log::info('IP setting updated successfully', ['setting_id' => $id]);

            return $setting->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP setting update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update IP setting');
        }
    }

    /**
     * Delete IP setting (soft delete)
     */
    public function deleteIpSetting(int $id): bool
    {
        DB::beginTransaction();
        try {
            $setting = $this->getIpSettingById($id);
            $setting->delete();

            LogHelper::deleted('ip_setting', $setting->id, $setting->company_id);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP setting deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete IP setting');
        }
    }
    /**
     * Restore soft deleted IP setting
     */
    public function restoreIpSetting(int $id): IpSetting
    {
        DB::beginTransaction();
        try {
            $setting = IpSetting::withTrashed()->find($id);

            if (!$setting) {
                throw ApiException::notFound('IP Setting');
            }

            $setting->restore();
            LogHelper::restored('ip_setting', $setting->id, $setting->company_id);

            DB::commit();
            Log::info('IP setting restored successfully', ['setting_id' => $id]);

            return $setting;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP setting restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore IP setting');
        }
    }

    /**
     * Permanently delete IP setting
     */
    public function forceDeleteIpSetting(int $id): bool
    {
        DB::beginTransaction();
        try {
            $setting = IpSetting::withTrashed()->find($id);

            if (!$setting) {
                throw ApiException::notFound('IP Setting');
            }

            $setting->forceDelete();
            LogHelper::forceDeleted('ip_setting', $id, $setting->company_id);

            DB::commit();
            Log::info('IP setting permanently deleted', ['setting_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP setting permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete IP setting');
        }
    }

    /**
     * Toggle IP setting status (Active/Inactive)
     */
    public function toggleStatus(int $id): IpSetting
    {
        DB::beginTransaction();
        try {
            $setting = $this->getIpSettingById($id);

            $currentStatus = Status::from($setting->status);
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $setting->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged('ip_setting',$setting->id,$setting->company_id,'IP Limitation new status ' . $newStatus->label());

            DB::commit();
            Log::info('IP setting status toggled', ['setting_id' => $id, 'status' => $newStatus->label()]);

            return $setting;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('IP setting status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}