<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\KnowledgeBase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KnowledgeBaseService
{
    /**
     * Get all knowledge bases with optional pagination
     */
    public function getAllKnowledgeBases(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = KnowledgeBase::query();

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
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching knowledge bases: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch knowledge bases');
        }
    }

    /**
     * Get knowledge base by ID
     */
    public function getKnowledgeBaseById(int $id): KnowledgeBase
    {
        $knowledgeBase = KnowledgeBase::find($id);
        if (!$knowledgeBase) {
            throw ApiException::notFound('Knowledge Base');
        }
        return $knowledgeBase;
    }

    /**
     * Create a new knowledge base
     */
    public function createKnowledgeBase(array $data): KnowledgeBase
    {
        DB::beginTransaction();
        try {
            // Auto-generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $knowledgeBase = KnowledgeBase::create($data);
            LogHelper::created('knowledge_base', $knowledgeBase->id, $knowledgeBase->company_id);
            DB::commit();

            Log::info('Knowledge base created successfully', ['knowledge_base_id' => $knowledgeBase->id]);

            return $knowledgeBase;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Knowledge base creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create knowledge base');
        }
    }

    /**
     * Update knowledge base
     */
    public function updateKnowledgeBase(int $id, array $data): KnowledgeBase
    {
        DB::beginTransaction();
        try {
            $knowledgeBase = $this->getKnowledgeBaseById($id);

            // Auto-generate slug if title changed and slug not provided explicitly
            if (empty($data['slug']) && isset($data['title'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $knowledgeBase->update($data);

            LogHelper::updated('knowledge_base', $knowledgeBase->id, $knowledgeBase->company_id);
            Log::info('Knowledge base updated successfully', ['knowledge_base_id' => $knowledgeBase->id]);

            DB::commit();
            return $knowledgeBase->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Knowledge base update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update knowledge base');
        }
    }

    /**
     * Delete knowledge base (soft delete)
     */
    public function deleteKnowledgeBase(int $id): bool
    {
        DB::beginTransaction();
        try {
            $knowledgeBase = $this->getKnowledgeBaseById($id);

            $knowledgeBase->delete();

            LogHelper::deleted('knowledge_base', $knowledgeBase->id, $knowledgeBase->company_id);
            Log::info('Knowledge base deleted successfully', ['knowledge_base_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Knowledge base deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete knowledge base');
        }
    }

    /**
     * Restore soft deleted knowledge base
     */
    public function restoreKnowledgeBase(int $id): KnowledgeBase
    {
        DB::beginTransaction();
        try {
            $knowledgeBase = KnowledgeBase::withTrashed()->find($id);
            if (!$knowledgeBase) {
                throw ApiException::notFound('Knowledge Base');
            }
            $knowledgeBase->restore();

            LogHelper::restored('knowledge_base', $knowledgeBase->id, $knowledgeBase->company_id);
            Log::info('Knowledge base restored successfully', ['knowledge_base_id' => $id]);

            DB::commit();
            return $knowledgeBase;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Knowledge base restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore knowledge base');
        }
    }

    /**
     * Permanently delete a knowledge base (Force Delete)
     */
    public function forceDeleteKnowledgeBase(int $id): bool
    {
        DB::beginTransaction();
        try {
            $knowledgeBase = KnowledgeBase::withTrashed()->find($id);

            if (!$knowledgeBase) {
                throw ApiException::notFound('Knowledge Base');
            }

            $knowledgeBase->forceDelete();

            LogHelper::forceDeleted('knowledge_base', $id, $knowledgeBase->company_id);
            Log::info('Knowledge base permanently deleted', ['knowledge_base_id' => $id]);

            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Knowledge base permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete knowledge base');
        }
    }

    /**
     * Toggle knowledge base status (Active/Inactive)
     */
    public function toggleStatus(int $id): KnowledgeBase
    {
        DB::beginTransaction();
        try {
            $knowledgeBase = $this->getKnowledgeBaseById($id);

            $newStatus = $knowledgeBase->status == 1 ? 0 : 1;
            $knowledgeBase->update(['status' => $newStatus]);

            LogHelper::statusChanged('knowledge_base', $knowledgeBase->id, $knowledgeBase->company_id);
            Log::info('Knowledge base status toggled', ['knowledge_base_id' => $id, 'new_status' => $newStatus]);

            DB::commit();
            return $knowledgeBase;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Knowledge base status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle knowledge base status');
        }
    }
}
