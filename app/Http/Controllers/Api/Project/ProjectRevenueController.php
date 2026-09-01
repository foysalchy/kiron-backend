<?php

namespace App\Http\Controllers\Api\Project;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\ProjectRevenue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectRevenueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projectId = $request->get('project_id');
        $query = ProjectRevenue::with(['phase', 'milestone']);
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        return ResponseHelper::success($query->latest('date')->get(), 'Revenues retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'phase_id' => 'nullable|exists:project_phases,id',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'title' => 'required|string',
            'amount' => 'required|numeric',
            'received_amount' => 'nullable|numeric',
            'status' => 'nullable|string',
            'date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $amount = (float)($validated['amount'] ?? 0);
        $received = (float)($validated['received_amount'] ?? 0);
        $validated['outstanding_amount'] = max(0, $amount - $received);

        $revenue = ProjectRevenue::create($validated);
        return ResponseHelper::created($revenue, 'Revenue recorded');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $revenue = ProjectRevenue::findOrFail($id);
        $data = $request->all();
        if (isset($data['amount']) || isset($data['received_amount'])) {
            $amount = (float)($data['amount'] ?? $revenue->amount);
            $received = (float)($data['received_amount'] ?? $revenue->received_amount);
            $data['outstanding_amount'] = max(0, $amount - $received);
        }

        $revenue->update($data);
        return ResponseHelper::success($revenue, 'Revenue updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $revenue = ProjectRevenue::findOrFail($id);
        $revenue->delete();
        return ResponseHelper::success(null, 'Revenue deleted');
    }
}
