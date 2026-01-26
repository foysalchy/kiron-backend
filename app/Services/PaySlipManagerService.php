<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Employee;
use App\Models\GeneratePayslip;
use App\Models\PayRollPayHead;
use App\Models\PaySlipManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};

class PaySlipManagerService
{
    /**
     * Get all Pay Slip Managers 
     */
    public function getAllPaySlipManagers(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = Employee::with([ 'paySlipManager.payroll', 'paySlipManager.position']);

            // Filter by Status
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('first_name', 'like', "%{$filters['search']}%")
                      ->orWhere('last_name', 'like', "%{$filters['search']}%");
                });
            }

            // Sorting
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();
        } catch (\Exception $e) {
            Log::error('Error fetching Pay Slip Managers: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch Pay Slip Managers');
        }
    }
/**
 * Get Specific Payslip details based on input
 */
    public function getGeneratePayslipDetails(int $id, array $filters = []): GeneratePayslip
    {
        if (empty($filters['period'])) {
            throw ApiException::badRequest('Period is required to fetch payslip details.');
        }

        $record = GeneratePayslip::with([
            'employee', 
            'paySlipManager.position', 
            'paySlipManager.payroll.payRollPayHeads.payHead'
        ])
        ->where('employee_id', $id) 
        ->where('period', $filters['period'])
        ->first();

        if (!$record) {
            throw ApiException::notFound('No payslip found for this employee in the selected period.');
        }

        return $record;
    }
    /**
     * Bulk Generate or Regenerate Payslips
     */
public function processBatchGeneration(array $data, bool $isRegenerate = false): array
{
    DB::beginTransaction();
    try {
        $processedCount = 0;

        foreach ($data['employee_ids'] as $employeeId) {
            $employee = Employee::find($employeeId);
            if (!$employee) continue;

            // 1. Manager record khunja ba default toiri kora
            $manager = PaySlipManager::where('employee_id', $employeeId)->first();
            
            if (!$manager) {
                $manager = PaySlipManager::create([
                    'company_id'  => $employee->company_id,
                    'employee_id' => $employee->id,
                    'payroll_id'  => 1, // Default payroll ID check korun database-e
                    'position_id' => $employee->job_title_id ?? 1,
                    'status'      => 1,
                    'total_amount'=> 0.00
                ]);
            }

            // 2. Pay Heads calculation (Migration onujayi 'payroll_id')
            $payRollPayHeads = PayRollPayHead::with('payHead')
                ->where('pay_roll_id', $manager->payroll_id) 
                ->get();

            $grossSalary = 0;
            $totalDeduction = 0;

            foreach ($payRollPayHeads as $payHeadRecord) {
                $amount = (float)$payHeadRecord->amount;
                if ($payHeadRecord->payHead && $payHeadRecord->payHead->type === 'Addition') {
                    $grossSalary += $amount;
                } else {
                    $totalDeduction += $amount;
                }
            }

            $netSalary = $grossSalary - $totalDeduction;

            // 3. Regenerate logic
            if ($isRegenerate) {
                GeneratePayslip::where('employee_id', $employeeId)
                    ->where('period', $data['period'])
                    ->delete();
            }

            // 4. Final Insert (pay_roll_pay_head_id ekhon nullable)
            $payslip = GeneratePayslip::create([
                'employee_id'          => $employeeId,
                'pay_slip_id'          => $manager->id,
                'pay_roll_pay_head_id' => $payRollPayHeads->first()?->id, 
                'period'               => $data['period'],
                'generated_date'       => $data['generated_date'],
                'gross_salary'         => $grossSalary,
                'total_deduction'      => $totalDeduction,
                'net_salary'           => $netSalary,
                'status'               => $data['status'] ?? 1,
            ]);

            $processedCount++;
        }

        DB::commit();
        return ['success' => true, 'message' => "Successfully generated $processedCount payslips."];

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Payroll Processing Error: ' . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
}
