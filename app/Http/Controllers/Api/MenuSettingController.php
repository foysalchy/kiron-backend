<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuSettingController extends Controller
{

    public function index()
    {
        $menus = MenuSetting::query()
            ->select('id', 'name', 'status', 'updated_at')
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

        return response()->json([
            'message' => 'Menu setting created successfully',
            'data' => $menu,
        ], 201);
    }

    public function update(Request $request, MenuSetting $menuSetting)
    {
        $menuSetting->update($this->validateMenu($request));

        return response()->json([
            'message' => 'Menu setting updated successfully',
            'data' => $menuSetting,
        ]);
    }

    public function toggleStatus(MenuSetting $menuSetting)
    {
        $menuSetting->update(['status' => ! $menuSetting->status]);

        return response()->json([
            'message' => 'Menu status updated successfully',
            'data' => $menuSetting,
        ]);
    }

    public function destroy(MenuSetting $menuSetting)
    {
        $menuSetting->delete();

        return response()->json(['message' => 'Menu setting deleted successfully']);
    }

    protected function validateMenu(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'status'           => 'required',
            'items'            => 'present|array',
            'items.*.id'       => 'required|string',
            'items.*.type'     => 'required|string|in:static,custom,category,brand',
            'items.*.key'      => 'nullable|string',
            'items.*.label'    => 'required|string|max:255',
            'items.*.link'     => 'nullable|string|max:255',
            'items.*.ref_id'   => 'nullable',
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
