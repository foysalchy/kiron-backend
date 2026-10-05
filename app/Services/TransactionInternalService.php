<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\TransactionTransfer;
use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth, DB, Log};

class TransactionInternalService
{
    /**
     * Get all internal transfers with filters
     */
    public function getAllTransfers(array $filters = [], bool $paginate = true)
    {
        try {
            $query = TransactionTransfer::with(['fromAccount', 'details.transferTo', 'creator']);

            // Filters
            if (!empty($filters['from_account_id'])) {
                $query->where('from_account_id', $filters['from_account_id']);
            }

            // Filter by destination account in details
            if (!empty($filters['to_account_id'])) {
                $query->whereHas('details', function ($q) use ($filters) {
                    $q->where('chart_of_account_id', $filters['to_account_id']);
                });
            }

            if (!empty($filters['status'])) {
                $statusValues = is_array($filters['status']) ? $filters['status'] : [$filters['status']];

                if (in_array('Trashed', $statusValues)) {
                    $query->onlyTrashed();
                } else {
                    $query->whereIn('status', $statusValues);
                }
            }

            $query = $this->applyDateRange($query, $filters);

            // Search
            if (isset($filters['search']) && $filters['search'] !== '') {
                $query->where('reference_number', 'like', "%{$filters['search']}%");
            }

            // Calculate Sum
            $totalAmountSum = (clone $query)->sum('total_amount');

            $results = $paginate
                ? $query->latest('date')->paginate($filters['per_page'] ?? 25)
                : $query->latest('date')->get();

            return [
                'items' => $results,
                'summary' => [
                    'total_amount' => $totalAmountSum
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Transfer Fetch Error: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch filtered transfers');
        }
    }

    private function applyDateRange($query, $filters)
    {
        $range = $filters['range'] ?? null;
        $now = Carbon::now();

        if ($range) {
            switch ($range) {
                case 'Today':
                    $query->whereDate('date', $now->today());
                    break;
                case 'This Week':
                    $query->whereBetween('date', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'This Month':
                    $query->whereMonth('date', $now->month)->whereYear('date', $now->year);
                    break;
                case 'Previous Week':
                    $query->whereBetween('date', [$now->subWeek()->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'Last 7 Days':
                    $query->whereBetween('date', [$now->subDays(7), Carbon::now()]);
                    break;
                case 'Last 15 Days':
                    $query->whereBetween('date', [$now->subDays(15), Carbon::now()]);
                    break;
                case 'Last 30 Days':
                    $query->whereBetween('date', [$now->subDays(30), Carbon::now()]);
                    break;
                case 'Last 60 Days':
                    $query->whereBetween('date', [$now->subDays(60), Carbon::now()]);
                    break;
                case 'Last 90 Days':
                    $query->whereBetween('date', [$now->subDays(90), Carbon::now()]);
                    break;

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
     * Get internal transfer by ID
     */
    public function getTransferById(int $id): TransactionTransfer
    {
        $transfer = TransactionTransfer::with(['fromAccount', 'details.transferTo', 'creator', 'company'])->find($id);
        if (!$transfer) {
            throw ApiException::notFound('Transfer');
        }
        return $transfer;
    }

    /**
     * Create Master-Detail Internal Transfer
     */
    public function createTransfer(array $data): TransactionTransfer
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = Auth::id();
            if (isset($data['file'])) {
                $customFileName = 'internal_voucher_' . time();
                $data['file'] = FileUploadHelper::upload(
                    $data['file'],
                    'transfers/attachments',
                    'r2',
                    false,
                    $customFileName
                );
            }

            // Calculate Total Amount from items array
            $totalAmount = collect($data['items'])->sum('amount');

            // Create Master record
            $transfer = TransactionTransfer::create(array_merge($data, [
                'total_amount' => $totalAmount
            ]));

            // Create Detail records (rows)
            foreach ($data['items'] as $item) {
                $transfer->details()->create([
                    'chart_of_account_id' => $item['chart_of_account_id'],
                    'amount'              => $item['amount'],
                ]);
            }

            LogHelper::created('transaction_transfer', $transfer->id, $transfer->company_id, $transfer->reference_number);
            DB::commit();

            // Auto Double-Entry Contra Voucher
            try {
                \App\Services\AutoAccountingService::postTransferJournal($transfer);
            } catch (\Exception $accErr) {
                Log::warning("Transfer auto-journal failed: " . $accErr->getMessage());
            }

            Log::info('Transaction Internal Transfer Created successfully', ['transfer_id' => $transfer->id]);

            return $transfer->load(['fromAccount', 'details.transferTo']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create transfer');
        }
    }

    /**
     * Update Master-Detail Internal Transfer (Sync Logic)
     */
    public function updateTransfer(int $id, array $data): TransactionTransfer
    {
        DB::beginTransaction();
        try {
            $transfer = $this->getTransferById($id);
            // dd($transfer);
            // Handle File Replacement
            if (isset($data['file'])) {
                $customFileName = 'internal_voucher_' . time();
                $data['file'] = FileUploadHelper::replace(
                    $data['file'],
                    $transfer->file,
                    'transfers/attachments',
                    'r2',
                    $customFileName
                );
            }

            // Sync Logic for Details
            if (isset($data['items'])) {
                $requestedItemIds = [];

                foreach ($data['items'] as $item) {
                    if (!empty($item['id'])) {
                        // Update existing item
                        $transfer->details()->where('id', $item['id'])->update([
                            'chart_of_account_id' => $item['chart_of_account_id'],
                            'amount'              => $item['amount'],
                        ]);
                        $requestedItemIds[] = $item['id'];
                    } else {
                        // Create new item
                        $newDetail = $transfer->details()->create([
                            'chart_of_account_id' => $item['chart_of_account_id'],
                            'amount'              => $item['amount'],
                        ]);
                        $requestedItemIds[] = $newDetail->id;
                    }
                }

                // Delete items not in request
                $transfer->details()->whereNotIn('id', $requestedItemIds)->delete();

                // Recalculate total
                $data['total_amount'] = $transfer->details()->sum('amount');
            }

            $transfer->update($data);

            LogHelper::updated('transaction_transfer', $transfer->id, $transfer->company_id, $transfer->reference_number);
            DB::commit();

            // Auto Double-Entry Contra Voucher
            try {
                \App\Services\AutoAccountingService::postTransferJournal($transfer->fresh());
            } catch (\Exception $accErr) {
                Log::warning("Transfer update auto-journal failed: " . $accErr->getMessage());
            }

            Log::info('Transaction Internal Transfer Updated successfully', ['transfer_id' => $transfer->id]);

            return $transfer->fresh(['fromAccount', 'details.transferTo']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update transfer');
        }
    }

    /**
     * Delete transfer (soft delete)
     */
    public function deleteTransfer(int $id): bool
    {
        try {
            $transfer = $this->getTransferById($id);
            $transfer->delete();

            LogHelper::deleted('transaction_internal_transfer', $transfer->id, $transfer->company_id, $transfer->reference_number);
            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Transfer deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete transfer');
        }
    }

    /**
     * Restore soft deleted transfer
     */
    public function restoreTransfer(int $id): TransactionTransfer
    {
        try {
            $transfer = TransactionTransfer::withTrashed()->find($id);

            if (!$transfer) {
                throw ApiException::notFound('Transfer');
            }

            $transfer->restore();

            LogHelper::restored('transaction_internal_transfer', $transfer->id, $transfer->company_id, $transfer->reference_number);
            return $transfer->load(['fromAccount', 'details.transferTo']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Transfer restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore transfer');
        }
    }

    /**
     * Permanently delete transfer
     */
    public function forceDeleteTransfer(int $id): bool
    {
        DB::beginTransaction();
        try {
            $transfer = TransactionTransfer::withTrashed()->find($id);

            if (!$transfer) {
                throw ApiException::notFound('Transfer');
            }

            // Delete associated file
            if ($transfer->file) {
                FileUploadHelper::delete($transfer->file);
            }

            // Cascade delete details
            $transfer->details()->delete();
            $transfer->forceDelete();

            LogHelper::forceDeleted('transaction_internal_transfer', $transfer->id, $transfer->company_id, $transfer->reference_number);

            DB::commit();

            Log::info('Transaction Internal Transfer Permanently Deleted Successfully', ['transfer_id' => $transfer->id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete transfer');
        }
    }

    /**
     * Update status
     */
    public function updateStatus(int $id, string|int $status): TransactionTransfer
    {
        DB::beginTransaction();
        try {
            $transfer = $this->getTransferById($id);
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

            if (!$newStatus) {
                throw ApiException::badRequest("Invalid status provided: " . $status);
            }

            // Generate reference number if approved and none exists
            if ($newStatus === Status::Approved && empty($transfer->reference_number)) {
                $transfer->generateReferenceNumber();
            }

            $transfer->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('transaction_internal_transfer', $transfer->id, $transfer->company_id, "Status changed to " . $newStatus->name);

            DB::commit();
            Log::info('Transaction internal transfer status updated', ['transfer' => $id, 'new_status' => $newStatus->label()]);
            return $transfer->load(['fromAccount', 'details.transferTo']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Transfer Status Update Error: " . $e->getMessage());
            throw $e;
        }
    }
}
