<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class EmailTemplateService
{
    /**
     * Get all Email templates with optional pagination and filters
     */
    public function getAllTemplates(array $filters, bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = EmailTemplate::query();

            // Filter by Status (including Trashed)
            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Subject
            if (!empty($filters['search'])) {
                $query->where('subject', 'like', "%{$filters['search']}%");
            }

            $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Throwable $e) {
            Log::error('Error fetching Email templates: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch Email templates');
        }
    }

    /**
     * Get Email template by ID
     */
    public function getTemplateById(int $id): EmailTemplate
    {
        $template = EmailTemplate::find($id);

        if (!$template) {
            throw ApiException::notFound('Email template');
        }
        return $template;
    }

    /**
     * Create a new Email template
     */
    public function createTemplate(array $data): EmailTemplate
    {
        DB::beginTransaction();
        try {
            $template = EmailTemplate::create($data);

            LogHelper::created('email_template', $template->id, $template->company_id, $template->subject);
            DB::commit();
            Log::info('Email template created successfully', ['template_id' => $template->id]);
            return $template;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email template creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create Email template');
        }
    }

    /**
     * Update Email template
     */
    public function updateTemplate(int $id, array $data): EmailTemplate
    {
        DB::beginTransaction();
        try {
            $template = $this->getTemplateById($id);
            $template->update($data);

            LogHelper::updated('email_template', $template->id, $template->company_id, $template->subject);
            DB::commit();

            Log::info('Email template updated successfully', ['template_id' => $template->id]);
            return $template->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email template update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update Email template');
        }
    }

    /**
     * Delete Email template (Soft Delete)
     */
    public function deleteTemplate(int $id): bool
    {
        DB::beginTransaction();
        try {
            $template = $this->getTemplateById($id);
            $template->delete();

            LogHelper::deleted('email_template', $template->id, $template->company_id, $template->subject);
            DB::commit();
            Log::info('Email template deleted successfully', ['template_id' => $template->id]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete Email template');
        }
    }
    /**
     * Restore soft deleted template
     */
    public function restoreTemplate(int $id): EmailTemplate
    {
        DB::beginTransaction();
        try {
            $template = EmailTemplate::withTrashed()->find($id);
            if (!$template) {
                throw ApiException::notFound('Email template');
            }

            $template->restore();

            LogHelper::restored('email_template', $template->id, $template->company_id, $template->subject);

            DB::commit();
            Log::info('Email template restored successfully', ['template_id' => $template->id]);

            return $template;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email template restore failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore Email template');
        }
    }

    /**
     * Permanently delete a template
     */
    public function forceDeleteTemplate(int $id): bool
    {
        DB::beginTransaction();
        try {
            $template = EmailTemplate::withTrashed()->find($id);
            if (!$template) {
                throw ApiException::notFound('Email template');
            }

            $template->forceDelete();

            LogHelper::forceDeleted('email_template', $id, $template->company_id, $template->subject);

            DB::commit();
            Log::info('Email template permanently deleted', ['template_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Email template force delete failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete Email template');
        }
    }

    /**
     * Toggle status (Active/Inactive)
     */
    public function toggleStatus(int $id): EmailTemplate
    {
        DB::beginTransaction();
        try {
            $template = $this->getTemplateById($id);
            $newStatus = $template->status == Status::Active->value ? Status::Inactive->value : Status::Active->value;

            $template->update(['status' => $newStatus]);

            LogHelper::statusChanged('email_template', $template->id, $template->company_id, "Status changed to: " . Status::from($newStatus)->label());

            DB::commit();
            return $template;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
