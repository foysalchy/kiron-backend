<?php

namespace App\Http\Controllers\Api;

use App\Helpers\LogHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogActionController extends Controller
{


    public function index(Request $request): JsonResponse
    {
        $data = LogHelper::getRecent(
        $request->input('company_id'),
        $request->input('user_id'),
        (int) $request->input('days', 7),
        (int) $request->input('limit', 100)
    );

        return ResponseHelper::success($data, 'Recent logs retrived successfully');
    }
    public function stats(Request $request): JsonResponse
    {
        $data = LogHelper::getStats(
        $request->input('company_id'),
        $request->input('user_id'),
        (int) $request->input('days', 30),
     
    );

        return ResponseHelper::success($data, 'Logs stats retrived successfully');
    }
    public function logByModule(string $module, int $companyId, $limit = 20): JsonResponse
    {
        $data = LogHelper::getByModule($module, $companyId, $limit);

        return ResponseHelper::success($data, 'logs by module retrived successfully');
    }
    public function logByAction(int $actionId, $limit = 20): JsonResponse
    {
        $data = LogHelper::getByRecord($actionId, $limit);

        return ResponseHelper::success($data, 'logs by  actions  retrived successfully');
    }

   
}
