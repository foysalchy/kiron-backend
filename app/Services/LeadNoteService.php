<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\LeadNote;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class LeadNoteService
{
    /**
     * Get all notes for a specific lead or all notes with filtering
     */
    public function getAllLeadNotes(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = LeadNote::with(['user:id,name', 'lead:id,full_name']);

            if (isset($filters['status']) && $filters['status'] !== '') {
                if ($filters['status'] === 'trashed' || (int)$filters['status'] === Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }
            // Search in note content
            if (!empty($filters['search'])) {
                $query->where('note', 'like', "%{$filters['search']}%");
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Exception $e) {
            Log::error('Error fetching lead notes: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch notes');
        }
    }
    /**
     * Get Note by ID
     */
    public function getNoteById(int $id): LeadNote
    {
        $note = LeadNote::with('lead','user')->find($id);

        if (!$note) {
            throw ApiException::notFound('Lead Note');
        }

        return $note;
    }
    /**
     * Create a new Lead Note
     */
    public function createLeadNote(array $data): LeadNote
    {
        DB::beginTransaction();
        try {
            $data['user_id'] = $data['user_id'] ?? auth()->id();

            $note = LeadNote::create($data);

            LogHelper::created('lead_note', $note->id, $note->company_id, 'Note added to lead #' . $note->lead_id);

            DB::commit();
            return $note;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Note creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create note');
        }
    }

    /**
     * Update Lead Note
     */
    public function updateLeadNote(int $id, array $data): LeadNote
    {
        DB::beginTransaction();
        try {
            $note = $this->getNoteById($id);
            $note->update($data);

            LogHelper::updated('lead_note', $note->id, $note->company_id, 'Note updated');

            DB::commit();
            return $note->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Note update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update note');
        }
    }

    /**
     * Soft Delete Note
     */
    public function deleteLeadNote(int $id): bool
    {
        DB::beginTransaction();
        try {
            $note = $this->getNoteById($id);
            $note->delete();

            LogHelper::deleted('lead_note', $id, $note->company_id, 'Note soft deleted');

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Note deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete note');
        }
    }

    /**
     * Restore Soft Deleted Note
     */
    public function restoreLeadNote(int $id): LeadNote
    {
        DB::beginTransaction();
        try {
            $note = LeadNote::onlyTrashed()->find($id);
            if (!$note) throw ApiException::notFound('Trashed Note');

            $note->restore();

            LogHelper::restored('lead_note', $id, $note->company_id, 'Note restored');

            DB::commit();
            return $note;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Note restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore note');
        }
    }

    /**
     * Permanently Delete Note
     */
    public function forceDeleteLeadNote(int $id): bool
    {
        DB::beginTransaction();
        try {
            $note = LeadNote::withTrashed()->find($id);
            if (!$note) throw ApiException::notFound('Note');

            $note->forceDelete();

            LogHelper::forceDeleted('lead_note', $id, $note->company_id, 'Note permanently deleted');

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead Note permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete note');
        }
    }
}
