<?php

namespace App\Services;

use App\Models\{Employee, Period, Payslip, PayslipItem, Attendance, Bonus, LeaveApplication, EmployeeSalary, PayrollSetting};
use App\Enums\Status;
use App\Exceptions\ApiException;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log};

class PayslipService
{
    public function generatePayslips(int $periodId, array $employeeIds): array
    {
        $period = Period::find($periodId);
        if (!$period) return ['success' => false, 'message' => 'Period not found.'];

        $companyId = auth()->user()->company_id;
        $settings = PayrollSetting::where('company_id', $companyId)->first();

        // Defaults if not set
        if (!$settings) {
            $settings = (object) [
                'late_days_for_penalty' => 10, // Standard 3 days
                'penalty_amount_in_days' => 1.0,
                'has_overtime_allowance' => false,
                'overtime_rate_multiplier' => 1.0,
                'standard_working_hours' => 8
            ];
        }

        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);
        $totalDaysInMonth = $startDate->diffInDays($endDate) + 1;

        DB::beginTransaction();
        try {
            $generatedCount = 0;

            foreach ($employeeIds as $empId) {

                // 1. Fetch Employee and their static Salary Structure
                $salaryStructure = EmployeeSalary::with('payHead')
                    ->where('employee_id', $empId)
                    ->get();

                if ($salaryStructure->isEmpty()) {
                    Log::warning("Skipped ID {$empId}: No salary structure defined.");
                    continue;
                }

                // 2. Calculate Default Fixed Totals
                $fixedGross = 0;
                $fixedDeduction = 0;

                foreach ($salaryStructure as $sal) {
                    if ($sal->type === 'addition') {
                        $fixedGross += $sal->amount;
                    } else {
                        $fixedDeduction += $sal->amount;
                    }
                }

                // Per Day Salary
                $perDaySalary = $fixedGross / $totalDaysInMonth;
                $perHourSalary = $perDaySalary / $settings->standard_working_hours;

                // 3. Fetch Attendance Stats
                // 1=Present, 0=Absent, 2=Weekend, 3=Late, 4=EarlyOut, 5=Holiday
                $attendances = Attendance::where('employee_id', $empId)
                    ->whereBetween('date', [$period->start_date, $period->end_date])
                    ->get();

                $presentDays = $attendances->whereIn('status', [1, 3, 4])->count(); // Count Late/Early as Present
                $absentDays = $attendances->where('status', 0)->count();
                $weekendDays = $attendances->where('status', 2)->count();
                $holidayDays = $attendances->where('status', 5)->count();

                $lateDays = $attendances->where('is_late', 1)->count();

                // Fetch Approved Leaves
                $approvedLeaves = LeaveApplication::where('employee_id', $empId)
                    ->where('status', Status::Approved->value)
                    ->where(function ($q) use ($period) {
                        $q->whereBetween('from_date', [$period->start_date, $period->end_date])
                            ->orWhereBetween('to_date', [$period->start_date, $period->end_date]);
                    })
                    ->get();

                $totalLeaveDaysTaken = 0;
                foreach ($approvedLeaves as $leave) {
                    $totalLeaveDaysTaken += $leave->duration;
                }

                // 4. Dynamic Deductions & Additions

                // A. Absent Deduction (Weekends/Holidays are NOT deducted)
                $absentDeductionAmount = $absentDays * $perDaySalary;

                // B. Late Penalty
                $latePenaltyAmount = 0;
                if ($lateDays >= $settings->late_days_for_penalty) {
                    $penaltyOccurrences = floor($lateDays / $settings->late_days_for_penalty);
                    $penaltyDaysToDeduct = $penaltyOccurrences * $settings->penalty_amount_in_days;
                    $latePenaltyAmount = $penaltyDaysToDeduct * $perDaySalary;
                }

                // C. Overtime Addition
                $overtimeAllowanceAmount = 0;
                $totalOtHours = 0;
                if ($settings->has_overtime_allowance) {
                    $totalOtSeconds = 0;
                    foreach ($attendances as $att) {
                        if ($att->over_time) {
                            $parsed = Carbon::parse($att->over_time);
                            $totalOtSeconds += ($parsed->hour * 3600) + ($parsed->minute * 60);
                        }
                    }
                    $totalOtHours = $totalOtSeconds / 3600;
                    $otRate = $perHourSalary * $settings->overtime_rate_multiplier;
                    $overtimeAllowanceAmount = $totalOtHours * $otRate;
                }

                // D. Bonus Calculation
                $activeBonuses = Bonus::where('company_id', $companyId)
                    ->where('period_id', $periodId)
                    ->where('is_active', true)
                    ->get();

                $totalBonusAllowance = 0;
                $bonusItems = [];

                if ($activeBonuses->isNotEmpty()) {

                    // SMART BASIC SALARY FINDER (SaaS Proof)
                    // 1. Try to find 'basic' or 'base'
                    $basicItem = $salaryStructure->first(function ($sal) {
                        $name = strtolower($sal->payHead->name);
                        return str_contains($name, 'basic') || str_contains($name, 'base');
                    });

                    // 2. If not found, fallback to the largest "addition" amount (which is usually the basic salary)
                    if (!$basicItem) {
                        $basicItem = $salaryStructure->where('type', 'addition')->sortByDesc('amount')->first();
                    }

                    $basicSalaryAmount = $basicItem ? $basicItem->amount : 0;

                    foreach ($activeBonuses as $bonus) {
                        $calculatedBonus = 0;
                        if ($bonus->type === 'percentage') {
                            $calculatedBonus = $basicSalaryAmount * ($bonus->amount / 100);
                        } else {
                            $calculatedBonus = $bonus->amount;
                        }

                        $totalBonusAllowance += $calculatedBonus;
                        $bonusItems[] = [
                            'name' => $bonus->name,
                            'amount' => $calculatedBonus
                        ];
                    }
                }

                // 5. Final Math
                $finalGross = $fixedGross + $overtimeAllowanceAmount + $totalBonusAllowance;
                $finalDeduction = $fixedDeduction + $absentDeductionAmount + $latePenaltyAmount;
                $netPayable = $finalGross - $finalDeduction;

                if ($netPayable < 0) $netPayable = 0;

                // 6. Delete old payslip if re-generating
                $existingPayslip = Payslip::where('employee_id', $empId)->where('period_id', $periodId)->first();
                if ($existingPayslip) {
                    $existingPayslip->items()->delete();
                    $existingPayslip->delete();
                }

                // 7. Save Master Payslip Snapshot
                $payslip = Payslip::create([
                    'company_id' => $companyId,
                    'employee_id' => $empId,
                    'period_id' => $periodId,
                    'total_days' => $totalDaysInMonth,
                    'working_days' => $presentDays + $absentDays  + $weekendDays + $holidayDays,
                    'present_days' => $presentDays - $totalLeaveDaysTaken, // Show actual present days excluding approved leaves
                    'absent_days' => $absentDays,
                    'late_days' => $lateDays,
                    'leave_days' => $totalLeaveDaysTaken,
                    'weekend_days' => $weekendDays,
                    'holiday_days' => $holidayDays,
                    'gross_salary' => $finalGross,
                    'total_deductions' => $finalDeduction,
                    'net_payable' => $netPayable,
                    'status' => 1
                ]);

                // 8. Save Line Items (Immutable breakdown)
                foreach ($salaryStructure as $sal) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'name' => $sal->payHead->name,
                        'type' => $sal->type,
                        'amount' => $sal->amount
                    ]);
                }

                if ($absentDeductionAmount > 0) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'name' => "Absent Deduction ({$absentDays} Days)",
                        'type' => 'deduction',
                        'amount' => $absentDeductionAmount
                    ]);
                }

                if ($latePenaltyAmount > 0) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'name' => "Late Penalty ({$lateDays} Lates)",
                        'type' => 'deduction',
                        'amount' => $latePenaltyAmount
                    ]);
                }

                if ($overtimeAllowanceAmount > 0) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'name' => "Overtime Allowance (" . round($totalOtHours, 1) . " Hrs)",
                        'type' => 'addition',
                        'amount' => $overtimeAllowanceAmount
                    ]);
                }

                if (!empty($bonusItems)) {
                    foreach ($bonusItems as $bItem) {
                        if ($bItem['amount'] > 0) {
                            PayslipItem::create([
                                'payslip_id' => $payslip->id,
                                'name' => $bItem['name'],
                                'type' => 'addition',
                                'amount' => $bItem['amount']
                            ]);
                        }
                    }
                }

                $generatedCount++;
            }

            DB::commit();
            return ['success' => true, 'message' => "Successfully generated {$generatedCount} payslips."];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Generate Payslip Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to generate payslips. ' . $e->getMessage()];
        }
    }

    /**
     * Preview summary data for selected period and employees (for confirmation before generation)
     */
    public function previewSummary(int $periodId, array $employeeIds): array
    {
        $period = Period::find($periodId);

        // Get summary for selected employees
        $summary = [];
        $employees = Employee::whereIn('id', $employeeIds)->get();

        foreach ($employees as $emp) {
            $attendances = Attendance::where('employee_id', $emp->id)
                ->whereBetween('date', [$period->start_date, $period->end_date])
                ->get();

            $presentDays = $attendances->whereIn('status', [1, 3, 4])->count();
            $absentDays = $attendances->where('status', 0)->count();
            $lateDays = $attendances->where('is_late', 1)->count();
            $earlyOutDays = $attendances->where('is_early_out', 1)->count();
            $weekendDays = $attendances->where('status', 2)->count();
            $holidayDays = $attendances->where('status', 5)->count();

            // Approved leaves
            $leaveDays = LeaveApplication::where('employee_id', $emp->id)
                ->where('status', \App\Enums\Status::Approved->value)
                ->where(function ($q) use ($period) {
                    $q->whereBetween('from_date', [$period->start_date, $period->end_date])
                        ->orWhereBetween('to_date', [$period->start_date, $period->end_date]);
                })->sum('duration');

            $summary[] = [
                'employee_id' => $emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'present' => $presentDays - $leaveDays, // Show actual present days excluding approved leaves
                'absent' => $absentDays,
                'late' => $lateDays,
                'early_out' => $earlyOutDays,
                'weekend' => $weekendDays,
                'holiday' => $holidayDays,
                'leaves' => $leaveDays,
            ];
        }
        return ['summary' => $summary];
    }
    /**
     * Get all generated payslips for listing
     */
    public function getAllPayslips(array $filters = [])
    {
        $query = Payslip::with(['employee', 'period']);

        if (!empty($filters['period_id'])) {
            $query->where('period_id', $filters['period_id']);
        }

        if (!empty($filters['search'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('first_name', 'like', "%{$filters['search']}%");
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get specific payslip details for printing/viewing
     */
    public function getPayslipDetails(int $id)
    {
        $payslip = Payslip::with(['employee.position', 'period', 'items'])->find($id);
        if (!$payslip) {
            throw ApiException::notFound('Payslip not found.');
        }

        // Segregate items for the frontend PDF view
        $payslip->additions = $payslip->items->where('type', 'addition')->values();
        $payslip->deductions = $payslip->items->where('type', 'deduction')->values();

        return $payslip;
    }
}
