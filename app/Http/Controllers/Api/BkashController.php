<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\BkashService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BkashController extends Controller
{
    public function __construct(protected BkashService $bkashService)
    {}
    public function getToken(): JsonResponse
    {
        $result = $this->bkashService->grantToken();
        if (isset($result['id_token'])) {
            Cache::put('bkash_id_token', $result['id_token'], 3600);
            return ResponseHelper::success($result, 'Token Granted');
        }
        return ResponseHelper::error('Grant Token Failed');
    }
}
