<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialSetting;
use App\Services\SocialSettingService;
use Illuminate\Http\Request;

class SocialSettingController extends Controller
{
    public function __construct(protected SocialSettingService $service) {}

    public function index(Request $request)
    {
        $data = $this->service->getAll();
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'icon_name'  => 'nullable|string|max:100',
            'icon_image' => 'nullable|image|max:2048',
            'link'       => 'required|url',
            'hover_bg'   => 'required|string|max:20',
        ]);

        $social = $this->service->create($validated, $request->user()->company_id);
        return response()->json(['success' => true, 'data' => $social], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'icon_name'  => 'nullable|string|max:100',
            'icon_image' => 'nullable|image|max:2048',
            'link'       => 'required|url',
            'hover_bg'   => 'required|string|max:20',
        ]);

        $social = $this->service->update($id, $validated);
        return response()->json(['success' => true, 'data' => $social]);
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }
}
