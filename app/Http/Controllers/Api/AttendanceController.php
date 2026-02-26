<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService)
    {
        //
    }
    public function index(Request $request)
    {
        $filters = [
            'date'           => $request->query('date'),
            'department_id'  => $request->query('department_id'),
            'status'         => $request->query('status'),
            'search'         => $request->query('search'),
            'sort_by'        => $request->query('sort_by', 'date'),
            'sort_order'     => $request->query('sort_order', 'desc'),
            'per_page'       => $request->query('per_page', 25),
        ];

        $data = $this->attendanceService->getAllAttendances($filters, true);

        return ResponseHelper::success($data, 'Attendance sheet retrieved successfully');
    }
    //create
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $data = $this->attendanceService->createAttendance($request->validated());

        return ResponseHelper::success($data, 'Attendance record created successfully', 201);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $request->validate([
            'records'               => ['required', 'array', 'min:1'],
            'records.*.employee_id' => ['required', 'integer', 'exists:employees,id'],
            'records.*.date'        => ['required', 'date'],
            'records.*.in_time'     => ['nullable', 'date_format:H:i'],
            'records.*.out_time'    => ['nullable', 'date_format:H:i', 'after:records.*.in_time'],
            'records.*.grace_time'  => ['nullable', 'integer', 'min:0'],
            'records.*.status'      => ['required', 'integer', 'in:0,1,2,3,4,5'],
            'records.*.is_late'     => ['nullable', 'boolean'],
            'records.*.is_early_out' => ['nullable', 'boolean'],
        ]);

        $results = $this->attendanceService->bulkCreateAttendance($request->input('records'));

        return response()->json([
            'success' => true,
            'message' => "{$results['saved']} record(s) saved, {$results['failed']} failed.",
            'data'    => $results,
        ], 201);
    }
    //show
    public function show(int $id): JsonResponse
    {
        $data = $this->attendanceService->getAttendanceById($id);

        return ResponseHelper::success($data, 'Attendance record retrieved successfully');
    }
    //update
    public function update(UpdateAttendanceRequest $request, int $id): JsonResponse
    {
        $data = $this->attendanceService->updateAttendance($id, $request->validated());

        return ResponseHelper::success($data, 'Attendance record updated successfully');
    }
    //delete
    public function destroy(int $id): JsonResponse
    {
        $this->attendanceService->deleteAttendance($id);
        return ResponseHelper::success(null, 'Attendance record deleted successfully');
    }
    //restore
    public function restore(int $id): JsonResponse
    {
        $data = $this->attendanceService->restoreAttendance($id);
        return ResponseHelper::success($data, 'Attendance record restored successfully');
    }
    //force delete
    public function forceDestroy(int $id): JsonResponse
    {
        $this->attendanceService->forceDeleteAttendance($id);
        return ResponseHelper::success(null, 'Attendance record permanently deleted');
    }
    //change status
    public function changeStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2,3,4,5',
        ]);

        $data = $this->attendanceService->changeAttendanceStatus($id, (int) $request->input('status'));

        return ResponseHelper::success($data, 'Attendance status updated successfully');
    }
}
