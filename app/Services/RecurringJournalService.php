<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\RecurringJournal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB,Log};

class RecurringJournalService
{
    /**
     * Get all journals with advanced filtering and pagination
     */
    public function getAllJournals(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = RecurringJournal::with(['fromAccount', 'toAccount', 'creator']);

            // Filter by Status
            if (!empty($filters['approval_status'])) {
                $query->where('approval_status', $filters['approval_status']);
            }
            if (!empty($filters['operational_status'])) {
                $query->where('operational_status', $filters['operational_status']);
            }

            if (isset($filters['status'])) {
                if ($filters['status'] === 'Trashed' || $filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            $results = $paginate
                ? $query->latest()->paginate($filters['per_page'] ?? 25)
                : $query->latest()->get();

            return $results;
        } catch (\Exception $e) {
            Log::error('Recurring Journal Index Error: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch records');
        }
    }
    /**
     * Get recurring journal by ID
     */
    public function getJournalById(int $id): RecurringJournal
    {
        $journal = RecurringJournal::with(['fromAccount', 'toAccount','creator'])->find($id);
        if (!$journal) {
            throw ApiException::notFound('Recurring Journal');
        }
        return $journal;
    }

    /**
     * Create Recurring Journal
     */
    public function createJournal(array $data): RecurringJournal
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = auth()->id();

            // Create Journal record
            $journal = RecurringJournal::create($data);

            LogHelper::created('recurring_journal', $journal->id, $journal->company_id, $journal->interval_type);
            DB::commit();
            Log::info('Recurring journal crated successfully', ['journal_id' => $journal->id]);

            return $journal->load(['fromAccount', 'toAccount']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recurring Journal creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create recurring journal');
        }
    }
    /**
     * Update Recurring Journal
     */
    public function updateJournal(int $id, array $data): RecurringJournal
    {
        DB::beginTransaction();
        try {
            $journal = $this->getJournalById($id);

            $journal->update($data);

            LogHelper::updated('recurring_journal', $journal->id, $journal->company_id, $journal->interval_type);
            DB::commit();
            Log::info('Recurring journal updated successfully', ['journal_id' => $journal->id]);

            return $journal->fresh(['fromAccount', 'toAccount']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recurring Journal update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update recurring journal');
        }
    }
    /**
     * Delete recurring journal
     */
    public function deleteJournal(int $id): bool
    {
        try {
            $journal = $this->getJournalById($id);
            $journal->delete();

            LogHelper::deleted('recurring_journal', $journal->id, $journal->company_id, $journal->id);
            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Recurring Journal deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete recurring journal');
        }
    }
    /**
     * Restore soft deleted recurring journal
     */
    public function restoreJournal(int $id): RecurringJournal
    {
        try {
            $journal = RecurringJournal::withTrashed()->find($id);

            if (!$journal) {
                throw ApiException::notFound('Recurring Journal');
            }

            $journal->restore();

            LogHelper::restored('recurring_journal', $journal->id, $journal->company_id, $journal->id);

            return $journal->load(['fromAccount', 'toAccount']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Recurring Journal restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore recurring journal');
        }
    }

    /**
     * Permanently delete recurring journal
     */
    public function forceDeleteJournal(int $id): bool
    {
        DB::beginTransaction();
        try {
            $journal = RecurringJournal::withTrashed()->find($id);

            if (!$journal) {
                throw ApiException::notFound('Recurring Journal');
            }

            $journal->forceDelete();

            LogHelper::forceDeleted('recurring_journal', $journal->id, $journal->company_id, $journal->id);

            DB::commit();
            Log::info('Recurring journal permanently successfully', ['journal_id' => $journal->id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recurring Journal permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete recurring journal');
        }
    }
    /**
     * Update Approval Status (Draft to Approved)
     */
    public function updateApprovalStatus(int $id, string $status): RecurringJournal
    {
        DB::beginTransaction();
        try {
            $journal = $this->getJournalById($id);

            $journal->update(['approval_status' => $status]);

            LogHelper::statusChanged('recurring_journal', $journal->id, $journal->company_id, "Approval marked as $status");

            DB::commit();
            Log::info('Recurring journal status updated successfully', ['journal_id' => $journal->id]);
            return $journal->load(['fromAccount', 'toAccount']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw ApiException::serverError('Failed to update approval status');
        }
    }

    /**
     * Toggle status Active <-> Inactive
     */
    public function toggleStatus(int $id): RecurringJournal
    {
        DB::beginTransaction();
        try {
            $journals = RecurringJournal::findOrFail($id);
            $currentStatus = Status::from($journals->status);
            // toggle logic
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;
            // update using enum value
            $journals->update([
                'operational_status' => $newStatus->value
            ]);

            LogHelper::statusChanged('recurringJournal', $journals->id, $journals->company_id, $journals->interval_type . ' new status ' . $newStatus->label());
            DB::commit();

            Log::info('Recurring journal status toggled', ['journals' => $id,'new_status' => $newStatus->label()]);

            return $journals;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recurring Journal status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle recurring journal status');
        }
    }
}
