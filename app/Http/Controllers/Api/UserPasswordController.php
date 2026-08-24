<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordChangeRequestRequest;
use App\Models\User;
use App\Services\UserPasswordService;
use Illuminate\Http\JsonResponse;

class UserPasswordController extends Controller
{
    public function __construct(protected UserPasswordService $userPasswordService)
    {
    }

    public function changePassword(PasswordChangeRequestRequest $request, User $user): JsonResponse
    {
        $data = $this->userPasswordService->changePassword($user, $request->validated()['method']);

        return ResponseHelper::success($data, 'Password changed and sent successfully');
    }
}