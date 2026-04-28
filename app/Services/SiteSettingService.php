<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Company;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class SiteSettingService
{
    /**
     * Get all site settings with optional pagination
     */
    public function getAllSiteSettings(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = SiteSetting::query();

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['shop_name'])) {
                $search = $filters['shop_name'];
                $query->where(function ($q) use ($search) {
                    $q->where('shop_name', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching site settings: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch site settings');
        }
    }
    /**
     * Get site setting by ID
     */
    public function getSiteSettingById(int $id): SiteSetting
    {
        $siteSetting = SiteSetting::find($id);
        if (!$siteSetting) {
            throw ApiException::notFound('Site Setting');
        }
        return $siteSetting;
    }
    /**
     * Create a new site setting
     */
    public function createSiteSetting(array $data): SiteSetting
    {
        DB::beginTransaction();

        try {
            // Handle logo upload
            if (isset($data['logo'])) {
                $data['logo'] = FileUploadHelper::uploadImage(
                    $data['logo'],
                    'settings/logos',
                   
                );
            }

            // Handle favicon upload
            if (isset($data['favicon'])) {
                $data['favicon'] = FileUploadHelper::uploadImage(
                    $data['favicon'],
                    'settings/favicons',
                    'public',
                    512
                );
            }

            $setting = SiteSetting::create($data);
            LogHelper::created('site_setting', $setting->id, $setting->company_id, $setting->shop_name);

            DB::commit();
            Log::info('Site setting created successfully', ['id' => $setting->id]);

            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($data['logo'])) FileUploadHelper::delete($data['logo']);
            if (isset($data['favicon'])) FileUploadHelper::delete($data['favicon']);

            Log::error('Site setting creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create site setting');
        }
    }
    /**
     * Update site setting
     */
    public function updateSiteSetting(int $id, array $data): SiteSetting
    {
        DB::beginTransaction();

        try {
            $setting = $this->getSiteSettingById($id);

            // Handle logo replace
            if (isset($data['logo'])) {
                $data['logo'] = FileUploadHelper::replace(
                    $data['logo'],
                    $setting->logo,
                    'settings/logos'
                );
            }

            // Handle favicon replace
            if (isset($data['favicon'])) {
                $data['favicon'] = FileUploadHelper::replace($data['favicon'], $setting->favicon, 'settings/favicons');
            }

            $setting->update($data);
            if ($setting->company_id) {
                Company::where('id', $setting->company_id)
                    ->where('status', '!=', Status::Active->value)
                    ->update(['status' => Status::Active->value]);

                User::where('company_id', $setting->company_id)
                    ->where('status', '!=', Status::Active->value)
                    ->update(['status' => Status::Active->value]);
            }
            LogHelper::updated('site_setting', $setting->id, $setting->company_id ?? null);

            DB::commit();
            Log::info('Site setting updated successfully', ['id' => $setting->id]);

            return $setting;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Site setting update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update site setting');
        }
    }

    /**
     * Delete site setting (soft delete)
     */
    public function deleteSiteSetting(int $id): bool
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSiteSettingById($id);
            $setting->delete();
            LogHelper::deleted('site_setting', $setting->id, $setting->company_id ?? null);

            DB::commit();
            Log::info('Site setting deleted successfully', ['id' => $id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Site setting deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete site setting');
        }
    }

    /**
     * Restore soft deleted site setting
     */
    public function restoreSiteSetting(int $id): SiteSetting
    {
        DB::beginTransaction();
        try {
            $setting = SiteSetting::withTrashed()->find($id);
            if (!$setting) throw ApiException::notFound('Site Setting');

            $setting->restore();
            LogHelper::restored('site_setting', $setting->id, $setting->company_id ?? null);

            DB::commit();
            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore site setting');
        }
    }

    /**
     * Permanently delete site setting
     */
    public function forceDeleteSiteSetting(int $id): bool
    {
        DB::beginTransaction();
        try {
            $setting = SiteSetting::withTrashed()->find($id);
            if (!$setting) throw ApiException::notFound('Site Setting');

            // Delete associated files from storage
            FileUploadHelper::delete($setting->logo);
            FileUploadHelper::delete($setting->favicon);

            $setting->forceDelete();
            LogHelper::forceDeleted('site_setting', $setting->id, $setting->company_id ?? null);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to permanently delete site setting');
        }
    }
    /**
     * Toggle site setting status (Active/Inactive)
     */
    public function toggleStatus(int $id): SiteSetting
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSiteSettingById($id);

            $currentStatus = Status::from($setting->status);
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $setting->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged(
                'site_setting',
                $setting->id,
                $setting->company_id,
                $setting->shop_name . ' new status ' . $newStatus->label()
            );

            DB::commit();
            Log::info('Site setting status toggled', [
                'setting_id' => $id,
                'new_status' => $newStatus->label()
            ]);

            return $setting;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Site setting status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle site setting status');
        }
    }
}
