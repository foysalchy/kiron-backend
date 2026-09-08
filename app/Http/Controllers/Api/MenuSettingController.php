<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class MenuSettingController extends Controller
{
    protected function cacheKey(int|string $id): string
    {
        return "menu_setting_resolved_{$id}";
    }

    protected function activeFeatureCategoryCacheKey(int|string $companyId): string
    {
        return "active_feature_category_{$companyId}";
    }

    /* ------------------------------- admin CRUD ------------------------------ */

    public function index(Request $request)
    {
        $menus = MenuSetting::query()
            ->select('id', 'name', 'type', 'status', 'updated_at')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Menu settings fetched successfully',
            'data' => $menus,
        ]);
    }

    public function show(MenuSetting $menuSetting)
    {
        return response()->json([
            'message' => 'Menu setting fetched successfully',
            'data' => $menuSetting,
        ]);
    }

    public function store(Request $request)
    {
        $menu = MenuSetting::create($this->validateMenu($request));

        if ($menu->isFeatureCategory()) {
            Cache::forget($this->activeFeatureCategoryCacheKey($menu->company_id));
        }

        return response()->json([
            'message' => 'Menu setting created successfully',
            'data' => $menu,
        ], 201);
    }

    public function update(Request $request, MenuSetting $menuSetting)
    {
        // type is locked after creation — a menu can't turn into a feature
        // category (or vice versa) since the items shape is different.
        $menuSetting->update($this->validateMenu($request, $menuSetting->type));

        Cache::forget($this->cacheKey($menuSetting->id));
        if ($menuSetting->isFeatureCategory()) {
            Cache::forget($this->activeFeatureCategoryCacheKey($menuSetting->company_id));
        }

        return response()->json([
            'message' => 'Menu setting updated successfully',
            'data' => $menuSetting,
        ]);
    }

    public function toggleStatus(MenuSetting $menuSetting)
    {
        $menuSetting->update(['status' => ! $menuSetting->status]);

        Cache::forget($this->cacheKey($menuSetting->id));
        if ($menuSetting->isFeatureCategory()) {
            Cache::forget($this->activeFeatureCategoryCacheKey($menuSetting->company_id));
        }

        return response()->json([
            'message' => 'Menu status updated successfully',
            'data' => $menuSetting,
        ]);
    }

    public function destroy(MenuSetting $menuSetting)
    {
        $wasFeatureCategory = $menuSetting->isFeatureCategory();
        $companyId = $menuSetting->company_id;

        $menuSetting->delete();

        Cache::forget($this->cacheKey($menuSetting->id));
        if ($wasFeatureCategory) {
            Cache::forget($this->activeFeatureCategoryCacheKey($companyId));
        }

        return response()->json(['message' => 'Menu setting deleted successfully']);
    }

    /* --------------------------- public/storefront read --------------------------- */

    public function resolved(MenuSetting $menuSetting)
    {
        $data = Cache::remember(
            $this->cacheKey($menuSetting->id),
            now()->addMinutes(30),
            fn () => [
                'id'     => $menuSetting->id,
                'name'   => $menuSetting->name,
                'type'   => $menuSetting->type,
                'status' => $menuSetting->status,
                'items'  => $menuSetting->resolvedItems(),
            ],
        );

        return response()->json([
            'message' => 'Menu setting items fetched successfully',
            'data' => $data,
        ]);
    }

    /**
     * Latest active feature_category for a company, cached — mirrors the
     * home_categories cache pattern so the frontend can fetch it the same way.
     */
    public function latestActiveFeatureCategory(Request $request)
    {
        $companyId = $request->user()->company_id ?? $request->query('company_id');
        $ttl = now()->addMinutes(30);

        $data = Cache::remember(
            $this->activeFeatureCategoryCacheKey($companyId),
            $ttl,
            function () use ($companyId) {
                $featureCategory = MenuSetting::where('company_id', $companyId)
                    ->where('type', MenuSetting::TYPE_FEATURE_CATEGORY)
                    ->where('status', true)
                    ->latest()
                    ->first();

                if (! $featureCategory) {
                    return null;
                }

                return [
                    'id'     => $featureCategory->id,
                    'name'   => $featureCategory->name,
                    'type'   => $featureCategory->type,
                    'status' => $featureCategory->status,
                    'items'  => $featureCategory->resolvedItems(),
                ];
            },
        );

        return response()->json([
            'message' => $data ? 'Active feature category fetched successfully' : 'No active feature category found',
            'data' => $data,
        ]);
    }

    /* ---------------------------------- validation ---------------------------------- */

    /**
     * $lockedType: when updating, force the existing record's type so it
     * can't be switched between 'menu' and 'feature_category' after creation.
     */
    protected function validateMenu(Request $request, ?string $lockedType = null): array
    {
        $type = $lockedType ?? $request->input('type', MenuSetting::TYPE_MENU);

        $rules = [
            'name'    => 'required|string|max:255',
            'status'  => 'required',
            'type'    => 'required|string|in:menu,feature_category',
            'items'   => 'present|array',
            'items.*.id'      => 'required|string',
            'items.*.visible' => 'boolean',
            'items.*.order'   => 'nullable|integer',
            'items.*.label'   => 'nullable|string|max:255',
        ];

        if ($type === MenuSetting::TYPE_FEATURE_CATEGORY) {
            $rules['items.*.label'] = 'nullable|string|max:255';
            $rules['items.*.level']   = 'required|string|in:mega_category,sub_category,mini_category,extra_category';
            $rules['items.*.ref_id']  = 'required';
        } else {
            $rules['items.*.label']   = 'required|string|max:255';
            $rules['items.*.type']    = 'required|string|in:static,custom,category,brand';
            $rules['items.*.key']     = 'nullable|string';
            $rules['items.*.link']    = 'nullable|string|max:255';
            $rules['items.*.ref_id']  = 'nullable';
        }

        // force the resolved type into the payload so it's always validated
        // against the correct rule set and always saved consistently
        $merged = array_merge($request->all(), ['type' => $type]);

        $validator = Validator::make($merged, $rules);

        if ($validator->fails()) {
            abort(response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422));
        }

        return $validator->validated();
    }
}