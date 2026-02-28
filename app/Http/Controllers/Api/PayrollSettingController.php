<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollSettingRequest;
use App\Services\PayrollSettingService;
use App\Helpers\ResponseHelper;

class PayrollSettingController extends Controller
{
    public function __construct(protected PayrollSettingService $service) {}

    public function index() {
        return ResponseHelper::success($this->service->getSettings(), 'Settings retrieved');
    }

    public function store(PayrollSettingRequest $request) {
        $setting = $this->service->updateSettings($request->validated());
        return ResponseHelper::success($setting, 'Settings updated successfully');
    }
}