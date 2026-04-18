<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        protected PermissionService $permissionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'search' => $request->query('search'),
            'grouped' => $request->query('grouped'), // This is what React uses
            'sort_by' => $request->query('sort_by', 'created_at'),
            'sort_order' => $request->query('sort_order', 'desc'),
            'per_page' => $request->query('per_page', 15),
        ];

        // If the frontend asks for grouped data, we should not paginate it
        $paginate = !isset($filters['grouped']);

        $data = $this->permissionService->getAllPermissions($filters, $paginate);

        return ResponseHelper::success($data, 'Permissions retrieved successfully');
    }

    // Usually, Permissions are system-defined via Seeders. 
    // You rarely need store/update/delete methods for Permissions in a SaaS, 
    // but if you do, they follow the exact same pattern as the BrandController.
}