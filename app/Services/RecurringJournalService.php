<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\RecurringJournal;
use Illuminate\Support\Facades\{DB,Log};

class RecurringJournalService
{
    /**
     * Get recurring journal by ID
     */
    public function getJournalById(int $id): RecurringJournal
    {
        $journal = RecurringJournal::with(['fromAccount', 'toAccount'])->find($id);
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

            return $journal->load(['fromAccount', 'toAccount']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Recurring Journal creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create recurring journal');
        }
    }
}
