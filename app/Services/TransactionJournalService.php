<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\TransactionJournal;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log};

class TransactionJournalService
{
    /**
     * Get all journal transactions with filters and totals
     */
    public function getAllJournals(array $filters = [], bool $paginate = true)
    {
        try {
            $query = TransactionJournal::with(['accounts.chartOfAccount.accountGroup', 'party:id,name,phone', 'creator:id,name']);

            // Filter by Voucher Type
            if (!empty($filters['voucher_type']) && $filters['voucher_type'] !== 'all') {
                $query->where('voucher_type', $filters['voucher_type']);
            }

            // Filter by Status
            if (!empty($filters['status'])) {
                $statusValues = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
                if (in_array('Trashed', $statusValues)) {
                    $query->onlyTrashed();
                } else {
                    $query->whereIn('status', $statusValues);
                }
            }

            // Apply Date Filters
            $query = $this->applyDateRange($query, $filters);

            // Search by Reference Number, Voucher No, or Narration
            if (isset($filters['search']) && $filters['search'] !== '') {
                $s = $filters['search'];
                $query->where(function ($q) use ($s) {
                    $q->where('reference_number', 'like', "%{$s}%")
                      ->orWhere('voucher_no', 'like', "%{$s}%")
                      ->orWhere('narration', 'like', "%{$s}%");
                });
            }

            // Calculate Footer Sums
            $totalDebitSum = (clone $query)->sum('total_debit');

            $results = $paginate
                ? $query->latest('date')->latest('id')->paginate($filters['per_page'] ?? 25)
                : $query->latest('date')->latest('id')->get();

            return [
                'items' => $results,
                'summary' => [
                    'total_debit' => $totalDebitSum,
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Journal Fetch Error: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch filtered journals');
        }
    }

    /**
     * Helper for date filtering
     */
    private function applyDateRange($query, $filters)
    {
        $range = $filters['range'] ?? null;
        $now = Carbon::now();

        if ($range) {
            switch ($range) {
                case 'Today': $query->whereDate('date', $now->today()); break;
                case 'This Week': $query->whereBetween('date', [$now->startOfWeek(), $now->endOfWeek()]); break;
                case 'This Month': $query->whereMonth('date', $now->month)->whereYear('date', $now->year); break;
                case 'Last 7 Days': $query->whereBetween('date', [$now->subDays(7), Carbon::now()]); break;
                case 'Last 30 Days': $query->whereBetween('date', [$now->subDays(30), Carbon::now()]); break;
                default:
                    if (strtotime($range)) {
                        $date = Carbon::parse($range);
                        $query->whereMonth('date', $date->month)->whereYear('date', $date->year);
                    }
                    break;
            }
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('date', '<=', $filters['to_date']);
        }

        return $query;
    }

    /**
     * Get journal by ID
     */
    public function getJournalById(int $id): TransactionJournal
    {
        $journal = TransactionJournal::with(['accounts.chartOfAccount.accountGroup', 'party', 'creator'])->find($id);
        if (!$journal) {
            throw ApiException::notFound('Journal record');
        }
        return $journal;
    }

    /**
     * Create Journal with Items
     */
    public function createJournal(array $data): TransactionJournal
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = auth()->id();

            if (empty($data['reference_number'])) {
                $prefix = match(strtolower($data['voucher_type'] ?? 'journal')) {
                    'payment' => 'PV',
                    'receipt' => 'RV',
                    'contra'  => 'CV',
                    'sales'   => 'SV',
                    'purchase'=> 'PB',
                    default   => 'JV',
                };
                $data['reference_number'] = $prefix . '-' . date('Ymd') . '-' . rand(1000, 9999);
            }
            if (empty($data['voucher_no'])) {
                $data['voucher_no'] = $data['reference_number'];
            }
            if (empty($data['voucher_type'])) {
                $data['voucher_type'] = 'journal';
            }
            if (empty($data['narration']) && !empty($data['description'])) {
                $data['narration'] = $data['description'];
            }

            if (isset($data['file'])) {
                $customFileName = 'journal_voucher_' . time();
                $data['file'] = FileUploadHelper::upload(
                    $data['file'],
                    'journals/attachments',
                    'r2',
                    false,
                    $customFileName
                );
            }

            // Calculate Totals from items array
            $totalDebit = collect($data['items'])->sum('debit');
            $totalCredit = collect($data['items'])->sum('credit');

            // Create Master
            $journal = TransactionJournal::create(array_merge($data, [
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
            ]));

            // Create Detail records (accounts)
            foreach ($data['items'] as $item) {
                if (($item['debit'] ?? 0) > 0 || ($item['credit'] ?? 0) > 0) {
                    $journal->accounts()->create([
                        'chart_of_account_id' => $item['chart_of_account_id'],
                        'debit'               => $item['debit'] ?? 0,
                        'credit'              => $item['credit'] ?? 0,
                    ]);
                }
            }

            LogHelper::created('transaction_journal', $journal->id, $journal->company_id, $journal->reference_number);
            DB::commit();
            Log::info('Transaction Journal Created Successfully', ['journal_id' => $journal->id]);

            return $journal->load(['accounts.chartOfAccount']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Journal creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create journal entry');
        }
    }

    /**
     * Update Journal and Sync Items
     */
    public function updateJournal(int $id, array $data): TransactionJournal
    {
        DB::beginTransaction();
        try {
            $journal = $this->getJournalById($id);

            if (isset($data['file'])) {
                $customFileName = 'journal_voucher_' . time();
                $data['file'] = FileUploadHelper::replace(
                    $data['file'],
                    $journal->file,
                    'journals/attachments',
                    'r2',
                    $customFileName
                );
            }

            if (isset($data['items'])) {
                $existingItemIds = $journal->accounts->pluck('id')->toArray();
                $requestItemIds  = collect($data['items'])->pluck('id')->filter()->toArray();

                $itemsToDelete = array_diff($existingItemIds, $requestItemIds);
                foreach ($itemsToDelete as $itemId) {
                    $journal->accounts()->where('id', $itemId)->delete();
                }

                foreach ($data['items'] as $item) {
                    if (!empty($item['id']) && in_array($item['id'], $existingItemIds)) {
                        $journal->accounts()->where('id', $item['id'])->update([
                            'chart_of_account_id' => $item['chart_of_account_id'],
                            'debit'               => $item['debit'] ?? 0,
                            'credit'              => $item['credit'] ?? 0,
                        ]);
                    } else {
                        $journal->accounts()->create([
                            'chart_of_account_id' => $item['chart_of_account_id'],
                            'debit'               => $item['debit'] ?? 0,
                            'credit'              => $item['credit'] ?? 0,
                        ]);
                    }
                }

                $data['total_debit']  = $journal->accounts()->sum('debit');
                $data['total_credit'] = $journal->accounts()->sum('credit');
            }

            $journal->update($data);

            LogHelper::updated('transaction_journal', $journal->id, $journal->company_id, $journal->reference_number);
            DB::commit();
            Log::info('Transaction Journal Updated Successfully', ['journal_id' => $journal->id]);

            return $journal->fresh(['accounts.chartOfAccount']);

        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Journal update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update journal entry');
        }
    }
    /**
     * Delete Journal (Soft Delete)
     */
    public function deleteJournal(int $id): bool
    {
        try {
            $journal = $this->getJournalById($id);
            $journal->delete();
            LogHelper::deleted('transaction_journal', $journal->id, $journal->company_id, $journal->reference_number);
            return true;
        } catch (\Exception $e) {
            Log::error('Journal deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete journal');
        }
    }

    /**
     * Restore Journal
     */
    public function restoreJournal(int $id): TransactionJournal
    {
        try {
            $journal = TransactionJournal::withTrashed()->find($id);
            if (!$journal) throw ApiException::notFound('Journal');

            $journal->restore();
            LogHelper::restored('transaction_journal', $journal->id, $journal->company_id, $journal->reference_number);
            return $journal->load(['accounts.chartOfAccount']);
        } catch (\Exception $e) {
            Log::error('Journal restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore journal');
        }
    }

    /**
     * Permanent Delete
     */
    public function forceDeleteJournal(int $id): bool
    {
        DB::beginTransaction();
        try {
            $journal = TransactionJournal::withTrashed()->find($id);
            if (!$journal) throw ApiException::notFound('Journal');

            if ($journal->file) {
                FileUploadHelper::delete($journal->file);
            }

            $journal->accounts()->delete();
            $journal->forceDelete();

            LogHelper::forceDeleted('transaction_journal', $journal->id, $journal->company_id, $journal->reference_number);
            DB::commit();
            Log::info('Transaction Journal Permanently Deleted Successfully', ['journal_id' => $journal->id]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Journal permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete journal');
        }
    }

    /**
     * Update Status & Generate Ref on Approval
     */
    public function updateStatus(int $id, string|int $status): TransactionJournal
    {
        DB::beginTransaction();
        try {
            $journal = $this->getJournalById($id);
            $newStatus = null;

            if (is_numeric($status)) {
                $newStatus = Status::tryFrom((int)$status);
            } else {
                foreach (Status::cases() as $case) {
                    if (strtolower($case->name) === strtolower($status)) {
                        $newStatus = $case;
                        break;
                    }
                }
            }

            if (!$newStatus) throw ApiException::badRequest("Invalid status provided");

            // Generate JV Number if moving to Approved
            if ($newStatus === Status::Approved && empty($journal->reference_number)) {
                $journal->generateReferenceNumber();
            }

            $journal->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('transaction_journal', $journal->id, $journal->company_id, "Status changed to {$newStatus->name}");

            DB::commit();
            Log::info('Transaction journal status updated', ['journal' => $id,'new_status' => $newStatus->label()]);
            return $journal->load(['accounts.chartOfAccount']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Journal Status Update Error: " . $e->getMessage());
            throw $e;
        }
    }
}
