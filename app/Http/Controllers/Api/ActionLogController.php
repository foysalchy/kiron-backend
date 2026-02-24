<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActionLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActionLogController extends Controller
{
    public function __construct(protected ActionLogService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $logs = $this->service->getAll($request);

            return response()->json([
                'success' => true,
                'message' => 'Action logs retrieved successfully',
                'data'    => $logs,
            ]);
        } catch (\Exception $e) {
            Log::error('ActionLog index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load action logs',
            ], 500);
        }
    }

    // Returns distinct modules + action categories for dropdowns
    public function filters(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'modules' => $this->service->getModules(),
                    'actions' => $this->service->getActions(),
                    'users'   => $this->service->getUsersWithLogs(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('ActionLog filters error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load filters',
            ], 500);
        }
    }
}
