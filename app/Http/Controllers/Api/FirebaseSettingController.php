<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\FirebaseSettingRequest;
use App\Services\FirebaseSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FirebaseSettingController extends Controller
{
    public function __construct(protected FirebaseSettingService $firebaseSettingService)
    {
    }
    /**
     * Display the firebase settings for the current company.
     */
    public function index(): JsonResponse
    {
        $data = $this->firebaseSettingService->getFirebaseSettings();
        return ResponseHelper::success($data,'Firebase settings retrieved successfully');
    }
    /**
     * Store or Update the firebase settings.
     */
    public function store(FirebaseSettingRequest $request): JsonResponse
    {
        $data = $this->firebaseSettingService->saveFirebaseSettings($request->validated());

        return ResponseHelper::success($data,'Firebase settings saved successfully...');
    }
}
