<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\SmsTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Log,DB};

class SmsTemplateService
{
    /**
     * Get all SMS templates with optional pagination and filters
     */
    public function getAllTemplates(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = SmsTemplate::query();

            // Filter by Status (including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Title
            if (!empty($filters['search'])) {
                $query->where('title', 'like', "%{$filters['search']}%");
            }

            $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching SMS templates: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch SMS templates');
        }
    }

    /**
     * Get SMS template by ID
     */
    public function getTemplateById(int $id): SmsTemplate
    {
        $template = SmsTemplate::find($id);

        if (!$template) {
            throw ApiException::notFound('SMS template');
        }
        return $template;
    }

    /**
     * Create a new SMS template
     */
    public function createTemplate(array $data): SmsTemplate
    {
        DB::beginTransaction();
        try {
            $template = SmsTemplate::create($data);

            LogHelper::created('sms_template', $template->id, $template->company_id, $template->title);
            DB::commit();

            Log::info('SMS template created successfully', ['template_id' => $template->id]);

            return $template;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS template creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create SMS template');
        }
    }

    /**
     * Update SMS template
     */
    public function updateTemplate(int $id, array $data): SmsTemplate
    {
        DB::beginTransaction();
        try {
            $template = $this->getTemplateById($id);
            $template->update($data);

            LogHelper::updated('sms_template', $template->id, $template->company_id, $template->title);
            DB::commit();

            Log::info('SMS template updated successfully', ['template_id' => $template->id]);
            return $template->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SMS template update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update SMS template');
        }
    }

    /**
     * Delete SMS template (Soft Delete)
     */
    public function deleteTemplate(int $id): bool
    {
        DB::beginTransaction();
        try {
            $template = $this->getTemplateById($id);
            $template->delete();

            LogHelper::deleted('sms_template', $template->id, $template->company_id, $template->title);
            DB::commit();
            Log::info('SMS template deleted successfully', ['template_id' => $template->id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete SMS template');
        }
    }

    /**
     * Restore soft deleted template
     */
    public function restoreTemplate(int $id): SmsTemplate
    {
        DB::beginTransaction();
        try {
            $template = SmsTemplate::withTrashed()->find($id);
            if (!$template) throw ApiException::notFound('SMS template');

            $template->restore();
            LogHelper::restored('sms_template', $template->id, $template->company_id, $template->title);
            DB::commit();

            Log::info('SMS template restore successfully', ['template_id' => $template->id]);
            return $template;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore SMS template');
        }
    }

    /**
     * Permanently delete a template
     */
    public function forceDeleteTemplate(int $id): bool
    {
        DB::beginTransaction();
        try {
            $template = SmsTemplate::withTrashed()->find($id);
            if (!$template) throw ApiException::notFound('SMS template');

            $template->forceDelete();
            LogHelper::forceDeleted('sms_template', $id, $template->company_id, $template->title);
            DB::commit();

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to permanently delete SMS template');
        }
    }
    /**
     * Toggle SMS template status (Active/Inactive)
     */
    public function toggleStatus(int $id): SmsTemplate
    {
        DB::beginTransaction();
        try {
            $template = $this->getTemplateById($id);

            $newStatusValue = $template->status == Status::Active->value
                ? Status::Inactive->value
                : Status::Active->value;

            $template->update(['status' => $newStatusValue]);

            $statusEnum = Status::from($newStatusValue);

            LogHelper::statusChanged('sms_template', $template->id, $template->company_id, "Status toggled to: " . $statusEnum->label());

            DB::commit();

            Log::info('SMS template status toggled', [
                'template_id' => $id,
                'new_status'  => $statusEnum->label()
            ]);

            return $template;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}

