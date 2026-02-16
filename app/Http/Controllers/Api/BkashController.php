<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\BkashRequest;
use App\Services\BkashService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BkashController extends Controller
{
    public function __construct(protected BkashService $bkashService)
    {}
    public function grantToken(): JsonResponse
    {
        $result = $this->bkashService->grantToken();
        return ResponseHelper::success($result, 'Token granted successfully...');
    }

}
