<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\TransactionExpense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, DB,Log};

class TransactionExpenseService
{
    /**
     * Get all transaction expenses with filters
     */
    public function getAllExpenses(array $filters = [], bool $paginate = true)
    {
        try {
            $query = TransactionExpense::with(['expenseFrom', 'categories.chartOfAccount']);

            // Filters
            if (!empty($filters['expense_from_id'])) {
                $query->where('expense_from_id', $filters['expense_from_id']);
            }
            if (!empty($filters['expense_to_id'])) {
                $query->where('expense_to_id', $filters['expense_to_id']);
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
            Log::error('Expense Fetch Error: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch filtered expenses');
        }
    }

    private function applyDateRange($query, $filters)
    {
        $range = $filters['range'] ?? null;
        $now = Carbon::now();

        // Handle predefined ranges from dropdown
        if ($range) {
            switch ($range) {
                case 'Today': $query->whereDate('date', $now->today()); break;
                case 'This Week': $query->whereBetween('date', [$now->startOfWeek(), $now->endOfWeek()]); break;
                case 'This Month': $query->whereMonth('date', $now->month)->whereYear('date', $now->year); break;
                case 'Previous Week': $query->whereBetween('date', [$now->subWeek()->startOfWeek(), $now->endOfWeek()]); break;
                case 'Last 7 Days': $query->whereBetween('date', [$now->subDays(7), Carbon::now()]); break;
                case 'Last 15 Days': $query->whereBetween('date', [$now->subDays(15), Carbon::now()]); break;
                case 'Last 30 Days': $query->whereBetween('date', [$now->subDays(30), Carbon::now()]); break;
                case 'Last 60 Days': $query->whereBetween('date', [$now->subDays(60), Carbon::now()]); break;
                case 'Last 90 Days': $query->whereBetween('date', [$now->subDays(90), Carbon::now()]); break;

                default:
                    // Handle specific months like "January 2026" or "February 2025"
                    if ($parsedDate = strtotime($range)) {
                        $date = Carbon::parse($range);
                        $query->whereMonth('date', $date->month)->whereYear('date', $date->year);
                    }
                    break;
            }
        }

        // Handle Custom Range
        if (!empty($filters['from_date'])) {
            $query->whereDate('date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('date', '<=', $filters['to_date']);
        }

        return $query;
    }
    /**
     * Get transaction expense by ID
     */
    public function getExpenseById(int $id): TransactionExpense
    {
        $expense = TransactionExpense::with(['expenseFrom', 'categories.chartOfAccount','creator', 'company'])->find($id);
        if (!$expense) {
            throw ApiException::notFound('Expense');
        }
        return $expense;
    }
    /**
     * Create Master-Detail Expense
     */
    public function createExpense(array $data): TransactionExpense
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = Auth::id();
            if (isset($data['file'])) {
                $customFileName = 'expense_voucher_' . time();
                $data['file'] = FileUploadHelper::upload(
                    $data['file'],
                    'expenses/attachments',
                    'r2',
                    false,
                    $customFileName
                );
            }
            //Calculate Total Amount from items array
            $totalAmount = collect($data['items'])->sum('amount');

            // Liquidity & Negative Cash Guard
            if (!empty($data['expense_from_id'])) {
                $companyId = $data['company_id'] ?? Auth::user()->company_id ?? 27;
                \App\Services\RiskManagementService::validatePaymentLiquidity(
                    (int)$companyId,
                    (int)$data['expense_from_id'],
                    (float)$totalAmount
                );
            }

            // Create Master record
            $expense = TransactionExpense::create(array_merge($data, [
                'total_amount' => $totalAmount
            ]));

            // Create Detail records (rows)
            foreach ($data['items'] as $item) {
                $expense->categories()->create([
                    'chart_of_account_id' => $item['chart_of_account_id'],
                    'amount'              => $item['amount'],
                ]);
            }

            LogHelper::created('transaction_expense', $expense->id, $expense->company_id, $expense->reference_number);
            DB::commit();

            // Auto Double-Entry Voucher
            try {
                \App\Services\AutoAccountingService::postExpenseJournal($expense);
            } catch (\Exception $accErr) {
                Log::warning("Expense auto-journal failed: " . $accErr->getMessage());
            }

            Log::info('Transaction Expense created successfully', ['expense_id' => $expense->id]);

            return $expense->load(['expenseFrom', 'categories.chartOfAccount']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Expense creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create expense transaction');
        }
    }
    /**
     * Update Master-Detail Expense
     */
    public function updateExpense(int $id, array $data): TransactionExpense
    {
        DB::beginTransaction();
        try {
            $expense = $this->getExpenseById($id);

            // Handle File Replacement
            if (isset($data['file'])) {
                $customFileName = 'expense_voucher_' . time();
                $data['file'] = FileUploadHelper::replace(
                    $data['file'],
                    $expense->file,
                    'expenses/attachments',
                    'r2',
                    $customFileName
                );
            }

            // Update items if provided (Master-Detail Sync)
            if (isset($data['items'])) {
                $data['total_amount'] = collect($data['items'])->sum('amount');

                $expense->categories()->delete();
                foreach ($data['items'] as $item) {
                    $expense->categories()->create([
                        'chart_of_account_id' => $item['chart_of_account_id'],
                        'amount'              => $item['amount'],
                    ]);
                }
            }

            $expense->update($data);

            LogHelper::updated('transaction_expense', $expense->id, $expense->company_id, $expense->reference_number);
            DB::commit();

            // Auto Double-Entry Voucher
            try {
                \App\Services\AutoAccountingService::postExpenseJournal($expense->fresh());
            } catch (\Exception $accErr) {
                Log::warning("Expense update auto-journal failed: " . $accErr->getMessage());
            }

            Log::info('Transaction Expense updated successfully', ['expense_id' => $expense->id]);

            return $expense->fresh(['expenseFrom', 'categories.chartOfAccount']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Expense update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update expense transaction');
        }
    }

    /**
     * Delete expense (soft delete)
     */
    public function deleteExpense(int $id): bool
    {
        try {
            $expense = $this->getExpenseById($id);
            $expense->delete();

            LogHelper::deleted('transaction_expense', $expense->id, $expense->company_id, $expense->reference_number);
            return true;
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Expense deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete expense');
        }
    }

    /**
     * Restore soft deleted expense
     */
    public function restoreExpense(int $id): TransactionExpense
    {
        try {
            $expense = TransactionExpense::withTrashed()->find($id);

            if (!$expense) {
                throw ApiException::notFound('Expense');
            }

            $expense->restore();

            LogHelper::restored('transaction_expense', $expense->id, $expense->company_id, $expense->reference_number);
            Log::info('Transaction Expense restored successfully', ['expense_id' => $expense->id]);
            return $expense->load(['expenseFrom', 'categories.chartOfAccount']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Expense restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore expense');
        }
    }

    /**
     * Permanently delete expense
     */
    public function forceDeleteExpense(int $id): bool
    {
        DB::beginTransaction();
        try {
            $expense = TransactionExpense::withTrashed()->find($id);

            if (!$expense) {
                throw ApiException::notFound('Expense');
            }

            // Delete associated file
            if ($expense->file) {
                FileUploadHelper::delete($expense->file);
            }

            // Categories usually cascade delete via DB or manual delete
            $expense->categories()->delete();
            $expense->forceDelete();

            LogHelper::forceDeleted('transaction_expense', $expense->id, $expense->company_id, $expense->reference_number);

            DB::commit();
            Log::info('Transaction Expense parmanently deleted successfully', ['expense_id' => $expense->id]);
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Expense permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete expense');
        }
    }
    /**
     * Update status
     */
    public function updateStatus(int $id, string|int $status): TransactionExpense
    {
        DB::beginTransaction();
        try {
            $expense = $this->getExpenseById($id);
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

            // reference number update if status is Approved
            if ($newStatus === Status::Approved && empty($expense->reference_number)) {
                $expense->generateReferenceNumber();
            }

            $expense->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('transaction_expense',$expense->id,$expense->company_id,"Status changed to " . $newStatus->name);

            DB::commit();
            Log::info('Transaction expense status updated', ['expense' => $id,'new_status' => $newStatus->label()]);
            return $expense->load(['expenseFrom', 'categories.chartOfAccount']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Expense Status Update Error: " . $e->getMessage());
            throw $e;
        }
    }
}
