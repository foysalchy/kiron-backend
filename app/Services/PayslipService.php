<?php

namespace App\Services;

use App\Models\{Employee, Period, Payslip, PayslipItem, Attendance, Bonus, LeaveApplication, EmployeeSalary, PayrollSetting};
use App\Enums\Status;
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

        // If settings don't exist, set some defaults so the code doesn't crash
        if (!$settings) {
            $settings = (object) [
                'late_days_for_penalty' => 3,
                'penalty_amount_in_days' => 1.0,
                'has_overtime_allowance' => false,
                'overtime_rate_multiplier' => 1.0,
                'standard_working_hours' => 8
            ];
        }

        $startDate = Carbon::parse($period->start_date);
        $endDate = Carbon::parse($period->end_date);
        $totalDaysInMonth = $startDate->diffInDays($endDate) + 1; // e.g. 31

        DB::beginTransaction();
        try {
            $generatedCount = 0;

            foreach ($employeeIds as $empId) {

                // 1. Fetch Employee and their static Salary Structure
                $employee = Employee::find($empId);
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

                // Per Day Salary (Based on Fixed Gross)
                $perDaySalary = $fixedGross / $totalDaysInMonth;
                $perHourSalary = $perDaySalary / $settings->standard_working_hours;

                // 3. Fetch Attendance Stats for this period
                // Assuming status: 1=Present, 0=Absent, 2=Weekend, 3=Late, 4=EarlyOut, 5=Holiday
                $attendances = Attendance::where('employee_id', $empId)
                    ->whereBetween('date', [$period->start_date, $period->end_date])
                    ->get();

                $presentDays = $attendances->whereIn('status', [1, 3, 4])->count(); // Present, Late, Early Out
                $absentDays = $attendances->where('status', 0)->count();
                $lateDays = $attendances->where('is_late', 1)->count();

                // Fetch Approved Leaves during this period
                $approvedLeaves = LeaveApplication::where('employee_id', $empId)
                    ->where('status', Status::Approved->value) // Approved
                    ->where(function ($q) use ($period) {
                        $q->whereBetween('from_date', [$period->start_date, $period->end_date])
                            ->orWhereBetween('to_date', [$period->start_date, $period->end_date]);
                    })
                    ->get();

                $totalLeaveDaysTaken = 0;
                // Currently assuming all approved leaves are Paid Leaves. 
                // If you have LOP (Loss of Pay) leave types, you would calculate them here.
                foreach ($approvedLeaves as $leave) {
                    $totalLeaveDaysTaken += $leave->duration;
                }

                // 4. Calculate Dynamic Deductions & Additions

                // A. Absent Deduction
                $absentDeductionAmount = $absentDays * $perDaySalary;

                // B. Late Penalty Deduction
                $latePenaltyAmount = 0;
                if ($lateDays >= $settings->late_days_for_penalty) {
                    $penaltyOccurrences = floor($lateDays / $settings->late_days_for_penalty);
                    $penaltyDaysToDeduct = $penaltyOccurrences * $settings->penalty_amount_in_days;
                    $latePenaltyAmount = $penaltyDaysToDeduct * $perDaySalary;
                }

                // C. Overtime Addition
                $overtimeAllowanceAmount = 0;
                if ($settings->has_overtime_allowance) {
                    // Assuming you save over_time as "HH:MM:SS". We need to convert it to decimal hours.
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
                // [NEW] Fetch Active Bonuses for this Period
                $activeBonuses = Bonus::where('company_id', $companyId)
                    ->where('period_id', $periodId)
                    ->where('is_active', true)
                    ->get();

                // Calculate Bonus Amount for this Employee
                $totalBonusAllowance = 0;
                $bonusItems = []; // To keep track of which bonuses applied

                if ($activeBonuses->isNotEmpty()) {
                    // We need the employee's basic salary to calculate percentage bonuses
                    $basicSalaryAmount = 0;

                    // Find the basic salary from their salary structure
                    $basicItem = $salaryStructure->first(function ($sal) {
                        // Looking for "Basic" or "basic" in the pay head name
                        return stripos(strtolower($sal->payHead->name), 'basic') !== false;
                    });

                    if ($basicItem) {
                        $basicSalaryAmount = $basicItem->amount;
                    }

                    foreach ($activeBonuses as $bonus) {
                        $calculatedBonus = 0;
                        if ($bonus->type === 'percentage') {
                            // e.g. 100% of Basic = $basicSalaryAmount * (100/100)
                            $calculatedBonus = $basicSalaryAmount * ($bonus->amount / 100);
                        } else {
                            // Fixed amount
                            $calculatedBonus = $bonus->amount;
                        }

                        $totalBonusAllowance += $calculatedBonus;

                        // Store name and amount so we can insert it as a PayslipItem later
                        $bonusItems[] = [
                            'name' => $bonus->name,
                            'amount' => $calculatedBonus
                        ];
                    }
                }
                // 5. Final Math
                $finalGross = $fixedGross + $overtimeAllowanceAmount;
                $finalDeduction = $fixedDeduction + $absentDeductionAmount + $latePenaltyAmount;
                $netPayable = $finalGross - $finalDeduction;

                // Prevent negative salary
                if ($netPayable < 0) $netPayable = 0;

                // 6. Delete old payslip for this period if re-generating
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
                    'working_days' => $presentDays + $absentDays + $totalLeaveDaysTaken, // Rough estimate
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'late_days' => $lateDays,
                    'leave_days' => $totalLeaveDaysTaken,
                    'gross_salary' => $finalGross,
                    'total_deductions' => $finalDeduction,
                    'net_payable' => $netPayable,
                    'status' => 1 // Generated/Unpaid
                ]);

                // 8. Save Line Items (The immutable breakdown)

                // Fixed Items
                foreach ($salaryStructure as $sal) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'name' => $sal->payHead->name,
                        'type' => $sal->type,
                        'amount' => $sal->amount
                    ]);
                }

                // Dynamic Deductions
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

                // Dynamic Additions
                if ($overtimeAllowanceAmount > 0) {
                    PayslipItem::create([
                        'payslip_id' => $payslip->id,
                        'name' => "Overtime Allowance (" . round($totalOtHours, 1) . " Hrs)",
                        'type' => 'addition',
                        'amount' => $overtimeAllowanceAmount
                    ]);
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
