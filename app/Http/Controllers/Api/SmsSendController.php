<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sms\SmsSendRequest;
use App\Services\SmsSendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsSendController extends Controller
{
    public function __construct(protected SmsSendService $smsSendService)
    {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->smsSendService->getAllSmsSends($request->all());
        return ResponseHelper::success($data, 'SMS logs retrieved successfully');
    }

    public function store(SmsSendRequest $request): JsonResponse
    {
        $data = $this->smsSendService->createSmsSend($request->validated());
        return ResponseHelper::success($data, 'SMS sent and recorded successfully', 201);
    }

    public function show(int $id): JsonResponse
    {
        $data = $this->smsSendService->getSmsSendById($id);
        return ResponseHelper::success($data, 'SMS details retrieved');
    }
}
