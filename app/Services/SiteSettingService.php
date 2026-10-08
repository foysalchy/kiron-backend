<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Company;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Support\Str;
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
            $query = SiteSetting::with('company');

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
        $siteSetting = SiteSetting::with('company')->find($id);
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
                $customFileName = Str::slug($data['shop_name'] ?? $data['title'] ?? 'site-logo') . '_logo_' . time();
                $data['logo'] = FileUploadHelper::uploadImage(
                    $data['logo'],
                    'settings/logos',
                    'r2',
                    2048,
                    $customFileName
                );
            }

            // Handle favicon upload
            if (isset($data['favicon'])) {
                $customFileName = Str::slug($data['shop_name'] ?? $data['title'] ?? 'site-favicon') . '_favicon_' . time();
                $data['favicon'] = FileUploadHelper::uploadImage(
                    $data['favicon'],
                    'settings/favicons',
                    'r2',
                    2048,
                    $customFileName
                );
            }
            if (isset($data['meta_image'])) {
                $customFileName = Str::slug($data['shop_name'] ?? $data['title'] ?? 'site-meta') . '_meta_' . time();
                $data['meta_image'] = FileUploadHelper::uploadImage(
                    $data['meta_image'],
                    'settings/meta_image',
                    'r2',
                    2048,
                    $customFileName
                );
            }
            if (isset($data['watermark_logo'])) {
                $customFileName = Str::slug($data['shop_name'] ?? $data['title'] ?? 'site-watermark') . '_watermark_' . time();
                $data['watermark_logo'] = FileUploadHelper::uploadImage(
                    $data['watermark_logo'],
                    'settings/watermarks',
                    'r2',
                    2048,
                    $customFileName
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
                $customFileName = Str::slug($data['shop_name'] ?? $data['title'] ?? $setting->shop_name ?? 'site-logo') . '_logo_' . time();
                $data['logo'] = FileUploadHelper::replace(
                    $data['logo'],
                    $setting->logo,
                    'settings/logos',
                    'r2',
                    $customFileName
                );
            }

            // Handle favicon replace
            if (isset($data['favicon'])) {
                $customFileName = Str::slug($data['shop_name'] ?? $data['title'] ?? $setting->shop_name ?? 'site-favicon') . '_favicon_' . time();
                $data['favicon'] = FileUploadHelper::replace(
                    $data['favicon'],
                    $setting->favicon,
                    'settings/favicons',
                    'r2',
                    $customFileName
                );
            }
            if (isset($data['meta_image'])) {
                $customFileName = Str::slug($data['shop_name'] ?? $data['title'] ?? $setting->shop_name ?? 'site-meta') . '_meta_' . time();
                $data['meta_image'] = FileUploadHelper::replace(
                    $data['meta_image'],
                    $setting->meta_image,
                    'settings/meta_image',
                    'r2',
                    $customFileName
                );
            }
            if (isset($data['watermark_logo'])) {
                $customFileName = Str::slug($data['shop_name'] ?? $data['title'] ?? $setting->shop_name ?? 'site-watermark') . '_watermark_' . time();
                $data['watermark_logo'] = FileUploadHelper::replace(
                    $data['watermark_logo'],
                    $setting->watermark_logo,
                    'settings/watermarks',
                    'r2',
                    $customFileName
                );
            }

            $setting->update($data);

            if ($setting->company_id) {
                $company = Company::find($setting->company_id);

                // Status update
                if ($company->status != Status::Active->value) {
                    $company->update(['status' => Status::Active->value]);

                    User::where('company_id', $company->id)
                        ->where('status', '!=', Status::Active->value)
                        ->update(['status' => Status::Active->value]);
                }

                if (isset($data['manage_warehouse'])) {
                    $manageWarehouse = (bool) $data['manage_warehouse'];

                    $company->update(['manage_warehouse' => $manageWarehouse]);

                    if (!$manageWarehouse) {
                        $exists = Warehouse::where('company_id', $company->id)
                            ->where('is_default', 1)
                            ->exists();

                        if (!$exists) {
                            $warehouse = Warehouse::create([
                                'company_id' => $company->id,
                                'name'       => 'Default Warehouse',
                                'location'   => null,
                                'is_default' => 1,
                                'status'     => Status::Active->value,
                            ]);

                            $company->update(['default_warehouse_id' => $warehouse->id]);
                        }
                    }
                }
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
