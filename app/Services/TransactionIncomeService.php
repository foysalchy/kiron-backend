<?php
namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use App\Models\TransactionIncome;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB,Log};

class TransactionIncomeService
{
    /**
     * Get all transaction incomes with filters and summary
     */
    public function getAllIncomes(array $filters = [], bool $paginate = true)
    {
        try {
            $query = TransactionIncome::with(['incomeTo', 'categories.chartOfAccount', 'creator']);

            // Filter
            if (!empty($filters['income_to_id'])) {
                $query->where('income_to_id', $filters['income_to_id']);
            }

            if (!empty($filters['income_from_id'])) {
                $query->whereHas('categories', function ($q) use ($filters) {
                    $q->where('chart_of_account_id', $filters['income_from_id']);
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
            Log::error('Income Fetch Error: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch filtered incomes');
        }
    }

    /**
     * Internal helper for date filtering
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
     * Get transaction income by ID
     */
    public function getIncomeById(int $id): TransactionIncome
    {
        $income = TransactionIncome::with(['incomeTo', 'categories.chartOfAccount', 'creator'])->find($id);
        if (!$income) {
            throw ApiException::notFound('Income record');
        }
        return $income;
    }

    /**
     * Create Master-Detail Income
     */
    public function createIncome(array $data): TransactionIncome
    {
        DB::beginTransaction();
        try {
            $data['created_by'] = auth()->id();

            if (isset($data['file'])) {
                $data['file'] = FileUploadHelper::upload(
                    $data['file'],
                    'incomes/attachments',
                    'public'
                );
            }

            // Calculate Total Amount
            $totalAmount = collect($data['items'])->sum('amount');

            // Create Master record
            $income = TransactionIncome::create(array_merge($data, [
                'total_amount' => $totalAmount
            ]));

            // Create Detail records
            foreach ($data['items'] as $item) {
                $income->categories()->create([
                    'chart_of_account_id' => $item['chart_of_account_id'],
                    'amount'              => $item['amount'],
                ]);
            }

            LogHelper::created('transaction_income', $income->id, $income->company_id, $income->reference_number);
            DB::commit();
            Log::info('Transaction Income created successfully', ['income_id' => $income->id]);

            return $income->load(['incomeTo', 'categories.chartOfAccount']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Income creation failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to create income transaction');
        }
    }

    /**
     * Update Master-Detail Income
     */
    public function updateIncome(int $id, array $data): TransactionIncome
    {
        DB::beginTransaction();
        try {
            $income = $this->getIncomeById($id);

            if (isset($data['file'])) {
                $data['file'] = FileUploadHelper::replace(
                    $data['file'],
                    $income->file,
                    'incomes/attachments'
                );
            }

            if (isset($data['items'])) {
                $data['total_amount'] = collect($data['items'])->sum('amount');
                $income->categories()->delete();
                foreach ($data['items'] as $item) {
                    $income->categories()->create([
                        'chart_of_account_id' => $item['chart_of_account_id'],
                        'amount'              => $item['amount'],
                    ]);
                }
            }

            $income->update($data);

            LogHelper::updated('transaction_income', $income->id, $income->company_id, $income->reference_number);
            DB::commit();
            Log::info('Transaction Income updated successfully', ['income_id' => $income->id]);
            return $income->fresh(['incomeTo', 'categories.chartOfAccount']);
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Income update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update income transaction');
        }
    }
    /**
     * Delete income (soft delete)
     */
    public function deleteIncome(int $id): bool
    {
        try {
            $income = $this->getIncomeById($id);
            $income->delete();
            LogHelper::deleted('transaction_income', $income->id, $income->company_id, $income->reference_number);
            return true;
        } catch (\Exception $e) {
            Log::error('Income deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete income');
        }
    }

    /**
     * Restore soft deleted income
     */
    public function restoreIncome(int $id): TransactionIncome
    {
        try {
            $income = TransactionIncome::withTrashed()->find($id);
            if (!$income) throw ApiException::notFound('Income');

            $income->restore();
            LogHelper::restored('transaction_income', $income->id, $income->company_id, $income->reference_number);
            return $income->load(['incomeTo', 'categories.chartOfAccount']);
        } catch (\Exception $e) {
            Log::error('Income restoration failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore income');
        }
    }

    /**
     * Permanently delete income
     */
    public function forceDeleteIncome(int $id): bool
    {
        DB::beginTransaction();
        try {
            $income = TransactionIncome::withTrashed()->find($id);
            if (!$income) throw ApiException::notFound('Income');

            if ($income->file) {
                FileUploadHelper::delete($income->file);
            }

            $income->categories()->delete();
            $income->forceDelete();

            LogHelper::forceDeleted('transaction_income', $income->id, $income->company_id, $income->reference_number);
            DB::commit();
            Log::info('Transaction Income parmanently deleted successfully', ['income_id' => $income->id]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Income permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete income');
        }
    }

    /**
     * Update status
     */
    public function updateStatus(int $id, string|int $status): TransactionIncome
    {
        DB::beginTransaction();
        try {
            $income = $this->getIncomeById($id);
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

            //Status Approved reference number generate
            if ($newStatus === Status::Approved && empty($income->reference_number)) {
                $income->generateReferenceNumber();
            }

            $income->update(['status' => $newStatus->value]);

            LogHelper::statusChanged('transaction_income', $income->id, $income->company_id, "Status changed to {$newStatus->name}");

            DB::commit();
            Log::info('Transaction income status updated', ['expense' => $id,'new_status' => $newStatus->label()]);
            return $income->load(['incomeTo', 'categories.chartOfAccount']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
