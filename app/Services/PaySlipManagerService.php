<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Employee;
use App\Models\GeneratePayslip;
use App\Models\PayRoll;
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
     * Get Periods assigned to a Payroll
     */
    public function getPeriodsByPayroll(int $payrollId)
    {
        $payroll = PayRoll::with('periods')->find($payrollId);
        if (!$payroll) {
            throw ApiException::notFound('Payroll settings not found for this manager.');
        }
        return $payroll->periods;
    }
    /**
     * Get Specific Payslip details based on input
     */
    public function getGeneratePayslipDetails(int $id, array $filters = []): GeneratePayslip
    {
        if (empty($filters['period_id'])) {
            throw ApiException::badRequest('Period ID is required to fetch payslip details.');
        }

        $record = GeneratePayslip::with([
            'employee', 
            'period', // Relationship added
            'paySlipManager.position', 
            'paySlipManager.payroll.payRollPayHeads.payHead'
        ])
        ->where('period_id', $filters['period_id']) 
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
            
            foreach ($data['employees'] as $entry) {
                $employeeId = $entry['employee_id'];
                $periodId   = $entry['period_id'];
                
                // load employee with position
                $employee = Employee::with('position')->find($employeeId);
                
                if (!$employee) {
                    Log::error("Employee not found: ID $employeeId");
                    continue;
                }

                // get current payroll from position
                $currentPayrollId = $employee->position->pay_roll_id ?? null; 

                if (!$currentPayrollId) {
                    Log::warning("Pay Roll ID missing in position for Employee: $employeeId");
                    continue; 
                }

                // slip manager create or update
                $manager = PaySlipManager::updateOrCreate(
                    ['employee_id' => $employeeId],
                    [
                        'company_id'  => $employee->company_id,
                        'payroll_id'  => $currentPayrollId,
                        'position_id' => $employee->position_id,
                        'status'      => 1,
                        'total_amount'=> 0.00
                    ]
                );

                // Pay Heads calculation
                $payRollPayHeads = PayRollPayHead::with('payHead')
                    ->where('pay_roll_id', $currentPayrollId) 
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

                // Regenerate handle
                if ($isRegenerate) {
                    GeneratePayslip::where('employee_id', $employeeId)
                        ->where('period_id', $periodId)
                        ->delete();
                }

                GeneratePayslip::create([
                    'company_id'      => $employee->company_id,
                    'employee_id'     => $employeeId,
                    'pay_roll_id'     => $currentPayrollId,
                    'pay_slip_id'     => $manager->id,
                    'period_id'       => $periodId,
                    'generated_date'  => $data['generated_date'],
                    'gross_salary'    => $grossSalary,
                    'total_deduction' => $totalDeduction,
                    'net_salary'      => $grossSalary - $totalDeduction,
                    'status'          => $data['status'] ?? 0,
                ]);

                $processedCount++;
            }

            DB::commit();
            return ['success' => true, 'message' => "Successfully generated $processedCount payslips."];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    /**
     * Get Salary Sheet Data
     */
    public function getSalarySheetData(array $filters = [])
    {
        $query = GeneratePayslip::with([
            'employee', 
            'period', 
            'payRollPayHeads.payHead'
        ]);

        if (!empty($filters['payroll_id'])) {
            $query->where('pay_roll_id', $filters['payroll_id']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['period_id'])) {
            $query->where('period_id', $filters['period_id']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}
