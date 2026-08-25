<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\Attendance;
use App\Models\Payslip;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HrmDashboardController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));

        // 1. Summary Metrics
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 1)->count();
        $activeDepartments = Department::active()->count();
        $pendingLeavesCount = LeaveApplication::where('status', 'pending')->count();

        $attendances = Attendance::whereBetween('date', [$dateFrom, $dateTo])->get();
        $todaysPresent = $attendances->whereIn('status', [1, 3])->count();
        $lateToday = $attendances->where('status', 3)->count();
        $absentToday = $attendances->where('status', 0)->count();
        $onLeaveToday = $attendances->where('status', 5)->count(); // Assuming 5 is holiday/leave for now
        
        $attendanceRate = $activeEmployees > 0 ? round(($todaysPresent / $activeEmployees) * 100, 2) : 0;

        $currentMonthPayslips = Payslip::whereMonth('created_at', Carbon::now()->month)->sum('net_payable');

        // 2. Department Distribution
        $departmentDistribution = Department::active()
            ->withCount(['employees' => function($q) {
                $q->where('status', 1);
            }])
            ->get()
            ->map(function($dept) {
                return [
                    'name' => $dept->name,
                    'value' => $dept->employees_count
                ];
            });

        // 3. Payroll Trends (Last 6 months)
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $payslipsData = Payslip::with('items')
            ->select('payslips.*', 'periods.period_name', 'periods.start_date')
            ->join('periods', 'payslips.period_id', '=', 'periods.id')
            ->where('periods.start_date', '>=', $sixMonthsAgo)
            ->get();
        
        $trendsMap = [];
        foreach ($payslipsData as $ps) {
            $month = $ps->period_name;
            if (!isset($trendsMap[$month])) {
                $trendsMap[$month] = [
                    'month' => $month,
                    'net_salary' => 0,
                    'gross_salary' => 0,
                    'overtime' => 0,
                    'bonus' => 0,
                    'start_date' => $ps->start_date
                ];
            }
            $trendsMap[$month]['net_salary'] += $ps->net_payable;
            $trendsMap[$month]['gross_salary'] += $ps->gross_salary;
            
            foreach ($ps->items as $item) {
                if (stripos($item->name, 'overtime') !== false || stripos($item->type, 'overtime') !== false) {
                    $trendsMap[$month]['overtime'] += $item->amount;
                }
                if (stripos($item->name, 'bonus') !== false || stripos($item->type, 'bonus') !== false) {
                    $trendsMap[$month]['bonus'] += $item->amount;
                }
            }
        }
        
        usort($trendsMap, function($a, $b) {
            return strtotime($a['start_date']) <=> strtotime($b['start_date']);
        });
        
        $payrollTrends = array_values($trendsMap);

        // 4. Pending Leave Requests
        $pendingLeavesList = LeaveApplication::with(['employee.department', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get()
            ->map(function($leave) {
                $start = Carbon::parse($leave->start_date);
                $end = Carbon::parse($leave->end_date);
                return [
                    'id' => $leave->id,
                    'employee_name' => trim($leave->employee->first_name . ' ' . $leave->employee->last_name),
                    'avatar' => $leave->employee->image,
                    'department' => $leave->employee->department->name ?? 'N/A',
                    'leave_type' => $leave->leaveType->name ?? 'Leave',
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                    'days' => $start->diffInDays($end) + 1,
                    'status' => $leave->status,
                ];
            });

        // 5. Department Attendance Overview
        $deptAttendance = [];
        $departments = Department::with(['employees' => function($q) { $q->where('status', 1); }])->get();
        foreach ($departments as $dept) {
            $empIds = $dept->employees->pluck('id')->toArray();
            $deptAtts = $attendances->whereIn('employee_id', $empIds);
            $presentCount = $deptAtts->whereIn('status', [1, 3])->count();
            $absentCount = $deptAtts->where('status', 0)->count();
            $leaveCount = $deptAtts->where('status', 5)->count();
            $empCount = count($empIds);
            
            $deptAttendance[] = [
                'id' => $dept->id,
                'department' => $dept->name,
                'employees' => $empCount,
                'present' => $presentCount,
                'absent' => $absentCount,
                'leave' => $leaveCount,
                'attendance_percent' => $empCount > 0 ? round(($presentCount / $empCount) * 100, 1) : 0,
            ];
        }

        // 6. HR Alerts
        $probationEnding = Employee::where('status', 1)
            ->whereNotNull('confirmation_date')
            ->whereBetween('confirmation_date', [Carbon::today(), Carbon::today()->addDays(30)])
            ->count();
            
        $alerts = [
            'pending_leaves' => $pendingLeavesCount,
            'probation_ending' => $probationEnding,
            'contracts_expiring' => 2, // Mocked
            'missing_documents' => 5, // Mocked
            'attendance_corrections' => 7, // Mocked
        ];

        // 7. New Joiners
        $newJoiners = Employee::with(['department', 'jobTitle'])
            ->where('status', 1)
            ->orderBy('joining_date', 'desc')
            ->take(5)
            ->get()
            ->map(function($emp) {
                return [
                    'id' => $emp->id,
                    'name' => trim($emp->first_name . ' ' . $emp->last_name),
                    'avatar' => $emp->image,
                    'designation' => $emp->jobTitle->name ?? 'N/A',
                    'department' => $emp->department->name ?? 'N/A',
                    'joining_date' => Carbon::parse($emp->joining_date)->format('M d, Y'),
                ];
            });

        // 8. Upcoming Birthdays
        $birthdays = Employee::where('status', 1)
            ->whereMonth('dob', Carbon::now()->month)
            ->whereDay('dob', '>=', Carbon::now()->day)
            ->orderByRaw('DAY(dob) ASC')
            ->take(5)
            ->get()
            ->map(function($emp) {
                return [
                    'id' => $emp->id,
                    'name' => trim($emp->first_name . ' ' . $emp->last_name),
                    'avatar' => $emp->image,
                    'dob' => Carbon::parse($emp->dob)->format('M d'),
                ];
            });
            
        // 9. Employee Status Overview
        $statusOverview = [
            'active' => $activeEmployees,
            'probation' => Employee::where('status', 1)->where('confirmation_date', '>', Carbon::today())->count(),
            'resigned' => Employee::where('status', 0)->count(),
            'terminated' => 0,
            'suspended' => 0
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_employees' => $totalEmployees,
                    'active_employees' => $activeEmployees,
                    'active_departments' => $activeDepartments,
                    'attendance_rate' => $attendanceRate,
                    'late_today' => $lateToday,
                    'on_leave_today' => $onLeaveToday,
                    'pending_leaves' => $pendingLeavesCount,
                    'monthly_payroll' => $currentMonthPayslips,
                ],
                'attendance_chart' => [
                    ['name' => 'Present', 'value' => $todaysPresent - $lateToday],
                    ['name' => 'Late', 'value' => $lateToday],
                    ['name' => 'Absent', 'value' => $absentToday],
                    ['name' => 'On Leave', 'value' => $onLeaveToday],
                    ['name' => 'WFH', 'value' => 0],
                ],
                'department_distribution' => $departmentDistribution,
                'payroll_trends' => $payrollTrends,
                'pending_leaves_list' => $pendingLeavesList,
                'department_attendance' => $deptAttendance,
                'alerts' => $alerts,
                'new_joiners' => $newJoiners,
                'upcoming_birthdays' => $birthdays,
                'status_overview' => $statusOverview,
            ]
        ]);
    }
}
