<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Services\PassChangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PassChangeController extends Controller
{
    public function __construct(protected PassChangeService $pass) {}
    public function update(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $this->pass->updatePass($request->validated());

        return ResponseHelper::success(null, 'Password updated successfully');
    }
}
