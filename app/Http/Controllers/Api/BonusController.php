<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bonus;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function index(Request $request)
    {
        $query = Bonus::with('period');

        if ($request->has('status') && $request->status !== 'All') {
            $query->where('is_active', $request->status == 1);
        }
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return ResponseHelper::success($query->latest()->paginate($request->per_page ?? 15), 'Bonuses fetched.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period_id' => 'required|exists:periods,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'amount' => 'required|numeric|min:0',
        ]);
        $bonus = Bonus::create($data);
        return ResponseHelper::success($bonus, 'Bonus created successfully.');
    }

    public function update(Request $request, $id)
    {
        $bonus = Bonus::findOrFail($id);
        $data = $request->validate([
            'period_id' => 'required|exists:periods,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'amount' => 'required|numeric|min:0',
        ]);
        $bonus->update($data);
        return ResponseHelper::success($bonus, 'Bonus updated successfully.');
    }

    public function toggleStatus($id)
    {
        $bonus = Bonus::findOrFail($id);
        $bonus->update(['is_active' => !$bonus->is_active]);
        return ResponseHelper::success(null, 'Status updated.');
    }

    public function destroy($id)
    {
        Bonus::findOrFail($id)->delete();
        return ResponseHelper::success(null, 'Deleted successfully.');
    }
}
