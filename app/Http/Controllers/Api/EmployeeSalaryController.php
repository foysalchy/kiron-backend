<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeSalaryRequest;
use App\Services\EmployeeSalaryService;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class EmployeeSalaryController extends Controller
{
    public function __construct(protected EmployeeSalaryService $service) {}

    public function index(Request $request) {
        return ResponseHelper::success($this->service->getEmployeeSalaryList($request->all()), 'List retrieved');
    }

    public function show($employeeId) {
        return ResponseHelper::success($this->service->getSalarySetupData($employeeId), 'Salary structure retrieved');
    }

    public function store(EmployeeSalaryRequest $request, $employeeId) {
        $this->service->setupSalary($employeeId, $request->validated());
        return ResponseHelper::success(null, 'Salary structure configured successfully');
    }
}