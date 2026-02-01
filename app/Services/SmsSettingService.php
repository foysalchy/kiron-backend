<?php 
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\SmsSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class SmsSettingService
{
    
    /**
     * Get all SMS settings with optional pagination
     */
    public function getAllSmsSettings(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = SmsSetting::query();

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (isset($filters['event_name']) && $filters['event_name'] !== '') {
                $query->where('event_name', 'like', "%{$filters['event_name']}%");
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('event_name', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching SMS settings: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch SMS settings');
        }
    }
    /**
     * Get SMS setting by ID
     */
    public function getSmsSettingById(int $id): SmsSetting
    {
        $setting = SmsSetting::find($id);

        if (!$setting) {
            throw ApiException::notFound('SMS Setting');
        }

        return $setting;
    }

    /**
     * Create a new SMS setting
     */
    public function createSmsSetting(array $data): SmsSetting
    {
        DB::beginTransaction();
        try {
            $setting = SmsSetting::create($data);
            LogHelper::created('sms_setting', $setting->id, $setting->company_id,$setting->event_name);

            DB::commit();
            Log::info('SMS setting created successfully', ['setting_id' => $setting->id]);

            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS setting creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create SMS setting');
        }
    }

    /**
     * Update SMS setting
     */
    public function updateSmsSetting(int $id, array $data): SmsSetting
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSmsSettingById($id);
            $setting->update($data);

            LogHelper::updated('sms_setting', $setting->id, $setting->company_id,$setting->event_name);

            DB::commit();
            Log::info('SMS setting updated successfully', ['setting_id' => $setting->id]);

            return $setting->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS setting update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update SMS setting');
        }
    }

    /**
     * Delete SMS setting (soft delete)
     */
    public function deleteSmsSetting(int $id): bool
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSmsSettingById($id);
            $setting->delete();

            LogHelper::deleted('sms_setting', $setting->id, $setting->company_id);

            DB::commit();
            Log::info('SMS setting deleted successfully', ['setting_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS setting deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete SMS setting');
        }
    }

    /**
     * Restore soft deleted SMS setting
     */
    public function restoreSmsSetting(int $id): SmsSetting
    {
        DB::beginTransaction();
        try {
            $setting = SmsSetting::withTrashed()->find($id);

            if (!$setting) {
                throw ApiException::notFound('SMS Setting');
            }

            $setting->restore();
            LogHelper::restored('sms_setting', $setting->id, $setting->company_id);

            DB::commit();
            Log::info('SMS setting restored successfully', ['setting_id' => $id]);

            return $setting;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS setting restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore SMS setting');
        }
    }

    /**
     * Permanently delete SMS setting
     */
    public function forceDeleteSmsSetting(int $id): bool
    {
        DB::beginTransaction();
        try {
            $setting = SmsSetting::withTrashed()->find($id);

            if (!$setting) {
                throw ApiException::notFound('SMS Setting');
            }

            $setting->forceDelete();
            LogHelper::forceDeleted('sms_setting', $setting->id, $setting->company_id);

            DB::commit();
            Log::info('SMS setting permanently deleted', ['setting_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS setting permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete SMS setting');
        }
    }

    /**
     * Toggle SMS setting status
     */
/**
     * Toggle SMS setting status (Active/Inactive)
     */
    public function toggleStatus(int $id): SmsSetting
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSmsSettingById($id);

            // আপনার Status Enum ব্যবহার করে লজিক
            $currentStatus = Status::from($setting->status);
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $setting->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged(
                'sms_setting', $setting->id, $setting->company_id,$setting->event_name . ' new status ' . $newStatus->label()
            );

            DB::commit();
            Log::info('SMS setting status toggled', [
                'setting_id' => $id,
                'new_status' => $newStatus->label()
            ]);

            return $setting;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS setting status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle SMS setting status');
        }
    }
}