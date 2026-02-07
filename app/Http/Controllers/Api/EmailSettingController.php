<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailSettingRequest;
use App\Services\EmailSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailSettingController extends Controller
{
    public function __construct(protected EmailSettingService $emailSettingService)
    {
    }

    /**
     * Get the email settings for the current company.
     */
    public function index(): JsonResponse
    {
        $data = $this->emailSettingService->getEmailSettings();

        return ResponseHelper::success($data, 'Email settings retrieved successfully');
    }

    /**
     * Save or Update the email settings.
     */
    public function store(EmailSettingRequest $request): JsonResponse
    {
        $data = $this->emailSettingService->saveEmailSettings($request->validated());

        return ResponseHelper::success($data, 'Email settings updated successfully');
    }
}
