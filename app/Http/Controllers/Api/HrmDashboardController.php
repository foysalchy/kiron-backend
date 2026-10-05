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

        $attendanceCounts = Attendance::whereBetween('date', [$dateFrom, $dateTo])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $todaysPresent = ($attendanceCounts[1] ?? 0) + ($attendanceCounts[3] ?? 0) + ($attendanceCounts[4] ?? 0);
        $lateToday = $attendanceCounts[3] ?? 0;
        $absentToday = $attendanceCounts[0] ?? 0;
        $onLeaveToday = ($attendanceCounts[5] ?? 0) + ($attendanceCounts[6] ?? 0); // Holiday + Leave
        
        $days = Carbon::parse($dateFrom)->diffInDays(Carbon::parse($dateTo)) + 1;
        $totalExpected = $activeEmployees * $days;
        $attendanceRate = $totalExpected > 0 ? round(($todaysPresent / $totalExpected) * 100, 2) : 0;

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
        
        $payslipsBase = DB::table('payslips')
            ->join('periods', 'payslips.period_id', '=', 'periods.id')
            ->where('periods.start_date', '>=', $sixMonthsAgo)
            ->select(
                'periods.period_name as month',
                'periods.start_date',
                DB::raw('SUM(payslips.net_payable) as net_salary'),
                DB::raw('SUM(payslips.gross_salary) as gross_salary')
            )
            ->groupBy('periods.id', 'periods.period_name', 'periods.start_date')
            ->get();
            
        $payslipsItems = DB::table('payslip_items')
            ->join('payslips', 'payslip_items.payslip_id', '=', 'payslips.id')
            ->join('periods', 'payslips.period_id', '=', 'periods.id')
            ->where('periods.start_date', '>=', $sixMonthsAgo)
            ->select(
                'periods.period_name as month',
                DB::raw("SUM(CASE WHEN payslip_items.name LIKE '%overtime%' OR payslip_items.type LIKE '%overtime%' THEN payslip_items.amount ELSE 0 END) as overtime"),
                DB::raw("SUM(CASE WHEN payslip_items.name LIKE '%bonus%' OR payslip_items.type LIKE '%bonus%' THEN payslip_items.amount ELSE 0 END) as bonus")
            )
            ->groupBy('periods.id', 'periods.period_name')
            ->get()
            ->keyBy('month');
            
        $payrollTrends = $payslipsBase->map(function ($item) use ($payslipsItems) {
            $monthItems = $payslipsItems->get($item->month);
            return [
                'month' => $item->month,
                'net_salary' => $item->net_salary ?? 0,
                'gross_salary' => $item->gross_salary ?? 0,
                'overtime' => $monthItems->overtime ?? 0,
                'bonus' => $monthItems->bonus ?? 0,
                'start_date' => $item->start_date
            ];
        })->sortBy('start_date')->values()->toArray();

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
        $deptAttendanceQuery = DB::table('departments')
            ->leftJoin('employees', function($join) {
                $join->on('departments.id', '=', 'employees.department_id')
                     ->where('employees.status', 1);
            })
            ->leftJoin('attendances', function($join) use ($dateFrom, $dateTo) {
                $join->on('employees.id', '=', 'attendances.employee_id')
                     ->whereBetween('attendances.date', [$dateFrom, $dateTo]);
            })
            ->select(
                'departments.id', 
                'departments.name as department',
                DB::raw('COUNT(DISTINCT employees.id) as employees_count'),
                DB::raw('SUM(CASE WHEN attendances.status IN (1, 3) THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN attendances.status = 0 THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('SUM(CASE WHEN attendances.status = 5 THEN 1 ELSE 0 END) as leave_count')
            )
            ->groupBy('departments.id', 'departments.name')
            ->get();
            
        $deptAttendance = $deptAttendanceQuery->map(function ($dept) {
            $empCount = $dept->employees_count;
            $presentCount = $dept->present_count ?? 0;
            return [
                'id' => $dept->id,
                'department' => $dept->department,
                'employees' => $empCount,
                'present' => $presentCount,
                'absent' => $dept->absent_count ?? 0,
                'leave' => $dept->leave_count ?? 0,
                'attendance_percent' => $empCount > 0 ? round(($presentCount / $empCount) * 100, 1) : 0,
            ];
        })->toArray();

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
