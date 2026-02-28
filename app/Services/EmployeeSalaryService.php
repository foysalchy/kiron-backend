<?php

namespace App\Services;

use App\Models\{Employee, EmployeeSalary};
use Illuminate\Support\Facades\DB;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Log;

class EmployeeSalaryService
{
    public function getEmployeeSalaryList(array $filters)
    {
        $query = Employee::with('salaries');

        if (!empty($filters['search'])) {
            $query->where('first_name', 'like', "%{$filters['search']}%");
        }

        $employees = $query->paginate($filters['per_page'] ?? 15);

        // Map Gross, Deductions, Net for the frontend table
        $employees->getCollection()->transform(function ($emp) {
            $gross = $emp->salaries->where('type', 'addition')->sum('amount');
            $deduction = $emp->salaries->where('type', 'deduction')->sum('amount');

            $emp->gross_salary = $gross;
            $emp->total_deductions = $deduction;
            $emp->net_salary = $gross - $deduction;
            $emp->is_setup = $emp->salaries->count() > 0;
            return $emp;
        });

        return $employees;
    }

    public function getSalaryByEmployee(int $employeeId)
    {
        return EmployeeSalary::where('employee_id', $employeeId)->get();
    }
    public function getSalarySetupData(int $employeeId)
    {
        $employee = Employee::with(['position.payRoll.payRollPayHeads.payHead'])->find($employeeId);

        if (!$employee) {
            throw ApiException::notFound('Employee not found');
        }

        // 1. Get already saved salaries (if any)
        $existingSalaries = EmployeeSalary::where('employee_id', $employeeId)->get();

        // 2. Get the default rules from their Payroll
        $payRollRules = [];
        $payRollId = $employee->position->pay_roll_id ?? null;

        if ($payRollId && $employee->position->payRoll) {
            $payRollRules = $employee->position->payRoll->payRollPayHeads->map(function ($rule) {
                return [
                    'pay_head_id' => $rule->pay_head_id,
                    'pay_head_name' => $rule->payHead->name,
                    'pay_head_type' => $rule->payHead->type, // addition / deduction
                    'calculation_type' => $rule->type, // amount / percentage
                    'rule_value' => $rule->amount // either fixed amount or percentage value (e.g. 50)
                ];
            });
        }

        return [
            'employee' => [
                'id' => $employee->id,
                'name' => trim($employee->first_name . ' ' . $employee->last_name),
                'position' => $employee->position->name ?? 'N/A',
                'payroll_name' => $employee->position->payRoll->name ?? 'No Payroll Assigned'
            ],
            'existing_salaries' => $existingSalaries,
            'payroll_rules' => $payRollRules,
            'all_pay_heads' => \App\Models\PayHead::all() // Fallback if they want to add extra manual heads
        ];
    }

    public function setupSalary(int $employeeId, array $data)
    {
        DB::beginTransaction();
        try {

            // Delete existing salary setup for a clean insert (Sync)
            EmployeeSalary::where('employee_id', $employeeId)->delete();
            $companyId = auth()->user()->company_id;

            $insertData = [];
            foreach ($data['salary_data'] as $item) {
                if ($item['amount'] > 0) {
                    $insertData[] = [
                        'employee_id' => $employeeId,
                        'company_id' => $companyId,
                        'pay_head_id' => $item['pay_head_id'],
                        'type' => $item['type'],
                        'amount' => $item['amount'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($insertData)) {
                EmployeeSalary::insert($insertData);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to setup salary: ' . $e->getMessage());
            throw ApiException::serverError('Failed to setup salary');
        }
    }
}
