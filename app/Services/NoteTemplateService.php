<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\NoteTemplate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class NoteTemplateService
{
    /**
     * Get all Note Templates with filtering and pagination
     */
    public function getAllTemplates(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = NoteTemplate::query();

            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
            }

            $query->orderBy($filters['sort_by'] ?? 'created_at', $filters['sort_order'] ?? 'desc');

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Throwable $e) {
            Log::error('Error fetching Note Templates: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch note templates');
        }
    }

    /**
     * Find template by ID
     */
    public function getTemplateById(int $id): NoteTemplate
    {
        $template = NoteTemplate::find($id);
        if (!$template) {
            throw ApiException::notFound('Note Template');
        }
        return $template;
    }

    /**
     * Create Template
     */
    public function createTemplate(array $data): NoteTemplate
    {
        DB::beginTransaction();
        try {
            $template = NoteTemplate::create($data);

            LogHelper::created('note_template', $template->id, $template->company_id, $template->title);

            DB::commit();
            return $template;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Note Template creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create note template');
        }
    }

    /**
     * Update Template
     */
    public function updateTemplate(int $id, array $data): NoteTemplate
    {
        DB::beginTransaction();
        try {
            $template = $this->getTemplateById($id);
            $template->update($data);

            LogHelper::updated('note_template', $template->id, $template->company_id, $template->title);

            DB::commit();
            return $template->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Note Template update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update note template');
        }
    }

    /**
     * Soft Delete
     */
    public function deleteTemplate(int $id): bool
    {
        DB::beginTransaction();
        try {
            $template = $this->getTemplateById($id);
            $template->delete();

            LogHelper::deleted('note_template', $template->id, $template->company_id, $template->title);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to delete note template');
        }
    }

    /**
     * Restore Template
     */
    public function restoreTemplate(int $id): NoteTemplate
    {
        DB::beginTransaction();
        try {
            $template = NoteTemplate::withTrashed()->find($id);
            if (!$template) throw ApiException::notFound('Note Template');

            $template->restore();
            LogHelper::restored('note_template', $template->id, $template->company_id, $template->title);

            DB::commit();
            return $template;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to restore note template');
        }
    }
    /**
     * Permanently delete a Note Template
     */
    public function forceDeleteTemplate(int $id): bool
    {
        DB::beginTransaction();
        try {
            $template = NoteTemplate::withTrashed()->find($id);

            if (!$template) {
                throw ApiException::notFound('Note Template');
            }

            $template->forceDelete();

            LogHelper::forceDeleted('note_template', $id, $template->company_id, $template->title);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Note Template permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete template');
        }
    }
    /**
     * Toggle status (Active/Inactive)
     */
    public function toggleStatus(int $id): NoteTemplate
    {
        DB::beginTransaction();
        try {
            $template = $this->getTemplateById($id);
            $newStatus = $template->status == Status::Active->value ? Status::Inactive->value : Status::Active->value;

            $template->update(['status' => $newStatus]);
            LogHelper::statusChanged('note_template', $id, $template->company_id, $template->title);

            DB::commit();
            return $template;
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to toggle status');
        }
    }
}
