<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeatureCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class FeatureCategoryController extends Controller
{
    protected function cacheKey(int|string $id): string
    {
        return "feature_category_resolved_{$id}";
    }

    /* ------------------------------- admin CRUD ------------------------------ */

    public function index()
    {
        $featureCategories = FeatureCategory::query()
            ->select('id', 'name', 'status', 'updated_at')
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Feature categories fetched successfully',
            'data' => $featureCategories,
        ]);
    }

    public function show(FeatureCategory $featureCategory)
    {
        return response()->json([
            'message' => 'Feature category fetched successfully',
            'data' => $featureCategory,
        ]);
    }

    public function store(Request $request)
    {
        $featureCategory = FeatureCategory::create($this->validateFeatureCategory($request));

        return response()->json([
            'message' => 'Feature category created successfully',
            'data' => $featureCategory,
        ], 201);
    }

    public function update(Request $request, FeatureCategory $featureCategory)
    {
        $featureCategory->update($this->validateFeatureCategory($request));

        Cache::forget($this->cacheKey($featureCategory->id));

        return response()->json([
            'message' => 'Feature category updated successfully',
            'data' => $featureCategory,
        ]);
    }

    public function toggleStatus(FeatureCategory $featureCategory)
    {
        $featureCategory->update(['status' => ! $featureCategory->status]);

        Cache::forget($this->cacheKey($featureCategory->id));

        return response()->json([
            'message' => 'Feature category status updated successfully',
            'data' => $featureCategory,
        ]);
    }

    public function destroy(FeatureCategory $featureCategory)
    {
        $featureCategory->delete();

        Cache::forget($this->cacheKey($featureCategory->id));

        return response()->json(['message' => 'Feature category deleted successfully']);
    }

    /* --------------------------- public/storefront read --------------------------- */

    public function resolved(FeatureCategory $featureCategory)
    {
        $data = Cache::remember(
            $this->cacheKey($featureCategory->id),
            now()->addMinutes(30),
            fn () => [
                'id'     => $featureCategory->id,
                'name'   => $featureCategory->name,
                'status' => $featureCategory->status,
                'items'  => $featureCategory->resolvedItems(),
            ],
        );

        return response()->json([
            'message' => 'Feature category items fetched successfully',
            'data' => $data,
        ]);
    }

    /* ---------------------------------- validation ---------------------------------- */

    protected function validateFeatureCategory(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'status'           => 'required',
            'items'            => 'present|array',
            'items.*.id'       => 'required|string',
            'items.*.level'    => 'required|string|in:mega_category,sub_category,mini_category,extra_category',
            'items.*.ref_id'   => 'required',
            'items.*.label'    => 'nullable|string|max:255',
            'items.*.visible'  => 'boolean',
            'items.*.order'    => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            abort(response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422));
        }

        return $validator->validated();
    }
}