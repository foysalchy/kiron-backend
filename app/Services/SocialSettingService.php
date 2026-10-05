<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\SocialSetting;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SocialSettingService
{
    /**
     * Get all social settings for a company
     */
    public function getAll(): Collection
    {
        try {
            return SocialSetting::latest()->get();
        } catch (\Exception $e) {
            Log::error('Error fetching social settings: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch social settings');
        }
    }

    /**
     * Get social setting by ID
     */
    public function getById(int $id): SocialSetting
    {
        $social = SocialSetting::find($id);

        if (!$social) {
            throw ApiException::notFound('SocialSetting');
        }

        return $social;
    }

    /**
     * Create a new social setting
     */
    public function create(array $data): SocialSetting
    {
        DB::beginTransaction();

        try {

            if (isset($data['icon_image'])) {
                $customFileName = Str::slug($data['icon_name'] ?? 'social-icon') . '_' . time();
                $data['icon_image'] = FileUploadHelper::uploadImage(
                    $data['icon_image'],
                    'social-settings/icons',
                    'r2',
                    2048,
                    $customFileName
                );
            }

            $social = SocialSetting::create($data);
            LogHelper::created('social_setting', $social->id, $social->company_id ?? null, $social->icon_name ?? $social->link);

            DB::commit();

            Log::info('Social setting created successfully', ['social_setting_id' => $social->id]);

            return $social;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['icon_image'])) {
                FileUploadHelper::delete($data['icon_image']);
            }

            Log::error('Social setting creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create social setting');
        }
    }

    /**
     * Update social setting
     */
    public function update(int $id, array $data): SocialSetting
    {
        DB::beginTransaction();

        try {
            $social = $this->getById($id);

            if (isset($data['icon_image'])) {
                $customFileName = Str::slug($data['icon_name'] ?? $social->icon_name ?? 'social-icon') . '_' . time();
                $data['icon_image'] = FileUploadHelper::replace(
                    $data['icon_image'],
                    $social->icon_image,
                    'social-settings/icons',
                    'r2',
                    $customFileName
                );
            }

            $social->update($data);
            LogHelper::updated('social_setting', $social->id, $social->company_id, $social->icon_name ?? $social->link);

            DB::commit();

            Log::info('Social setting updated successfully', ['social_setting_id' => $social->id]);

            return $social->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['icon_image'])) {
                FileUploadHelper::delete($data['icon_image']);
            }

            Log::error('Social setting update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update social setting');
        }
    }

    /**
     * Soft delete social setting
     */
    public function delete(int $id): bool
    {
        try {
            $social = $this->getById($id);
            $social->delete();
            LogHelper::deleted('social_setting', $social->id, $social->company_id, $social->icon_name ?? $social->link);

            Log::info('Social setting deleted successfully', ['social_setting_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Social setting deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete social setting');
        }
    }

    /**
     * Restore soft deleted social setting
     */
    public function restore(int $id): SocialSetting
    {
        try {
            $social = SocialSetting::withTrashed()->find($id);

            if (!$social) {
                throw ApiException::notFound('SocialSetting');
            }

            $social->restore();
            LogHelper::restored('social_setting', $social->id, $social->company_id, $social->icon_name ?? $social->link);

            Log::info('Social setting restored successfully', ['social_setting_id' => $id]);

            return $social;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Social setting restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore social setting');
        }
    }

    /**
     * Permanently delete social setting
     */
    public function forceDelete(int $id): bool
    {
        DB::beginTransaction();

        try {
            $social = SocialSetting::withTrashed()->find($id);

            if (!$social) {
                throw ApiException::notFound('SocialSetting');
            }

            FileUploadHelper::delete($social->icon_image);

            $social->forceDelete();
            LogHelper::forceDeleted('social_setting', $social->id, $social->company_id, $social->icon_name ?? $social->link);

            DB::commit();

            Log::info('Social setting permanently deleted', ['social_setting_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Social setting permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete social setting');
        }
    }

    /**
     * Toggle social setting status
     */
    public function toggleStatus(int $id): SocialSetting
    {
        try {
            $social = $this->getById($id);

            $currentStatus = Status::from($social->status);

            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $social->update(['status' => $newStatus->value]);

            LogHelper::statusChanged(
                'social_setting',
                $social->id,
                $social->company_id,
                ($social->icon_name ?? $social->link) . ' new status ' . $newStatus->label()
            );

            Log::info('Social setting status toggled', [
                'social_setting_id' => $id,
                'new_status'        => $newStatus->label(),
            ]);

            return $social;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Social setting status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle social setting status');
        }
    }
}
