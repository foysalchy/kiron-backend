<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReferralGroup;
use App\Models\ReferralGroupTier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReferralGroupController extends Controller
{
    public function index(Request $request)
    {
        $groups = ReferralGroup::with(['tiers'])
            ->withCount('partners')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $groups,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'default_commission_rate' => 'required|numeric|min:0|max:100',
            'buyer_discount_rate' => 'nullable|numeric|min:0|max:100',
            'cookie_lifetime_days' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'tiers' => 'nullable|array',
            'tiers.*.name' => 'required|string|max:255',
            'tiers.*.min_sales' => 'required|integer|min:0',
            'tiers.*.max_sales' => 'nullable|integer|min:0',
            'tiers.*.commission_rate' => 'required|numeric|min:0|max:100',
            'tiers.*.badge_color' => 'nullable|string|max:50',
        ]);

        return DB::transaction(function () use ($request) {
            $group = ReferralGroup::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name) . '-' . Str::random(4),
                'default_commission_rate' => $request->default_commission_rate,
                'buyer_discount_rate' => $request->buyer_discount_rate ?? 0,
                'cookie_lifetime_days' => $request->cookie_lifetime_days ?? 30,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
            ]);

            if ($request->has('tiers') && is_array($request->tiers)) {
                foreach ($request->tiers as $index => $tierData) {
                    ReferralGroupTier::create([
                        'referral_group_id' => $group->id,
                        'name' => $tierData['name'],
                        'min_sales' => $tierData['min_sales'],
                        'max_sales' => !empty($tierData['max_sales']) ? $tierData['max_sales'] : null,
                        'commission_rate' => $tierData['commission_rate'],
                        'badge_color' => $tierData['badge_color'] ?? '#6366f1',
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Referral group created successfully',
                'data' => $group->load('tiers'),
            ], 201);
        });
    }

    public function show($id)
    {
        $group = ReferralGroup::with(['tiers', 'partners'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $group,
        ]);
    }

    public function update(Request $request, $id)
    {
        $group = ReferralGroup::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'default_commission_rate' => 'required|numeric|min:0|max:100',
            'buyer_discount_rate' => 'nullable|numeric|min:0|max:100',
            'cookie_lifetime_days' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'tiers' => 'nullable|array',
            'tiers.*.name' => 'required|string|max:255',
            'tiers.*.min_sales' => 'required|integer|min:0',
            'tiers.*.max_sales' => 'nullable|integer|min:0',
            'tiers.*.commission_rate' => 'required|numeric|min:0|max:100',
            'tiers.*.badge_color' => 'nullable|string|max:50',
        ]);

        return DB::transaction(function () use ($request, $group) {
            $group->update([
                'name' => $request->name,
                'default_commission_rate' => $request->default_commission_rate,
                'buyer_discount_rate' => $request->buyer_discount_rate ?? 0,
                'cookie_lifetime_days' => $request->cookie_lifetime_days ?? 30,
                'description' => $request->description,
                'status' => $request->status ?? $group->status,
            ]);

            if ($request->has('tiers')) {
                // Remove existing tiers and recreate
                $group->tiers()->delete();
                foreach ($request->tiers as $index => $tierData) {
                    ReferralGroupTier::create([
                        'referral_group_id' => $group->id,
                        'name' => $tierData['name'],
                        'min_sales' => $tierData['min_sales'],
                        'max_sales' => !empty($tierData['max_sales']) ? $tierData['max_sales'] : null,
                        'commission_rate' => $tierData['commission_rate'],
                        'badge_color' => $tierData['badge_color'] ?? '#6366f1',
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Referral group updated successfully',
                'data' => $group->load('tiers'),
            ]);
        });
    }

    public function destroy($id)
    {
        $group = ReferralGroup::findOrFail($id);
        $group->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Referral group deleted successfully',
        ]);
    }
}
