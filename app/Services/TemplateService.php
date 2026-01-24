<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\Template;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TemplateService
{
    /**
     * Get all templates with filters
     */
    public function getAllTemplates(array $filters = [], bool $paginate = true)
    {
        try {
            $query = Template::query();

            // Apply filters
            if (isset($filters['status'])) {
                if ($filters['status'] === Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('slug', 'like', "%{$filters['search']}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate ? $query->paginate($filters['per_page'] ?? 15) : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching templates: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch templates');
        }
    }

    /**
     * Get template by ID
     */
    public function getTemplateById(int $id): Template
    {
        $template = Template::find($id);

        if (!$template) {
            throw ApiException::notFound('Template');
        }

        return $template;
    }

    /**
     * Get template by slug
     */
    public function getTemplateBySlug(string $slug): Template
    {
        $template = Template::where('slug', $slug)
            ->first();

        if (!$template) {
            throw ApiException::notFound('Template');
        }

        return $template;
    }

    /**
     * Create a new template
     */
    public function createTemplate(array $data): Template
    {
        DB::beginTransaction();
        try {
            // Handle preview image upload
            if (isset($data['preview_image'])) {
                $data['preview_image'] = FileUploadHelper::uploadImage(
                    $data['preview_image'],
                    'templates/previews',
                    'public',
                    5120 // 5MB
                );
            }

            $template = Template::create($data);

            LogHelper::created('template', $template->id, $template->company_id, $template->name);

            DB::commit();
            Log::info('Template created successfully', ['template_id' => $template->id]);

            return $template;
        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded file
            if (isset($data['preview_image'])) {
                FileUploadHelper::delete($data['preview_image']);
            }

            Log::error('Template creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create template');
        }
    }

    /**
     * Update template
     */
    public function updateTemplate(int $id, array $data): Template
    {
        DB::beginTransaction();
        try {
            $template = $this->getTemplateById($id);

            // Handle preview image upload
            if (isset($data['preview_image'])) {
                $data['preview_image'] = FileUploadHelper::replace(
                    $data['preview_image'],
                    $template->preview_image,
                    'templates/previews'
                );
            }

            $template->update($data);

            LogHelper::updated('template', $template->id, $template->company_id, $template->name);

            DB::commit();
            Log::info('Template updated successfully', ['template_id' => $template->id]);

            return $template;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded file
            if (isset($data['preview_image'])) {
                FileUploadHelper::delete($data['preview_image']);
            }

            Log::error('Template update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update template');
        }
    }

    /**
     * Delete template (soft delete)
     */
    public function deleteTemplate(int $id): bool
    {
        try {
            $template = $this->getTemplateById($id);
            $template->delete();

            LogHelper::deleted('template', $template->id, $template->company_id, $template->name);
            Log::info('Template deleted successfully', ['template_id' => $id]);

            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Template deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete template');
        }
    }

    /**
     * Restore soft deleted template
     */
    public function restoreTemplate(int $id): Template
    {
        try {
            $template = Template::withTrashed()->find($id);

            if (!$template) {
                throw ApiException::notFound('Template');
            }

            $template->restore();

            LogHelper::restored('template', $template->id, $template->company_id, $template->name);
            Log::info('Template restored successfully', ['template_id' => $id]);

            return $template;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Template restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore template');
        }
    }

    /**
     * Permanently delete template
     */
    public function forceDeleteTemplate(int $id): bool
    {
        DB::beginTransaction();
        try {
            $template = Template::withTrashed()->find($id);

            if (!$template) {
                throw ApiException::notFound('Template');
            }

            // Delete preview image
            FileUploadHelper::delete($template->preview_image);

            $template->forceDelete();

            LogHelper::forceDeleted('template', $template->id, $template->company_id, $template->name);

            DB::commit();
            Log::info('Template permanently deleted', ['template_id' => $id]);

            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Template permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete template');
        }
    }

    /**
     * Toggle template status
     */
    public function toggleStatus(int $id): Template
    {
        try {
            $template = $this->getTemplateById($id);

            // Current status as enum
            $currentStatus = Status::from($template->status);

            // Toggle logic
            $newStatus = $currentStatus === Status::Active ? Status::Inactive : Status::Active;

            // Update using enum value
            $template->update([
                'status' => $newStatus->value
            ]);

            LogHelper::statusChanged(
                'template',
                $template->id,
                $template->company_id,
                $template->name . ' new status ' . $newStatus->label()
            );

            Log::info('Template status toggled', [
                'template_id' => $id,
                'new_status' => $newStatus->label()
            ]);

            return $template;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Template status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle template status');
        }
    }
}
