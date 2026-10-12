<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use App\Models\OmniSetting;
use App\Models\QuikReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OmniSettingsController extends Controller
{
    public function getSettings(Request $request)
    {

        $companyId = $request->user()->company_id;

        $settings = OmniSetting::firstOrCreate(
            ['company_id' => $companyId],

            [
                'auto_assign' => true,
                'away_mode_active' => true,
                'away_message' => 'Thanks for reaching out! We are currently away.',
                'welcome_mode_active' => true,
                'welcome_message' => "Hi there!Thanks for messaging us. We're happy to help!Please tell us how we can assist you, and we'll reply shortly."
            ]
        );

        $quickReplies = QuikReply::orderBy('group_name')->orderBy('created_at', 'desc')->get();
        $adMappings = \App\Models\OmniAdMapping::where('company_id', $companyId)
            ->with('product:id,title,regular_price')
            ->orderBy('created_at', 'desc')
            ->get();

        $products = \App\Models\Product::select('id', 'title', 'regular_price')
            ->where('status', \App\Enums\Status::Active->value)
            ->orderBy('title', 'asc')
            ->get();

        return response()->json([
            'settings' => $settings,
            'quick_replies' => $quickReplies,
            'ad_mappings' => $adMappings,
            'products' => $products,
        ]);
    }

    public function saveSettings(Request $request)
    {
        $settings = OmniSetting::firstOrFail();

        $data = $request->validate([
            'company_name' => 'nullable|string|max:150',
            'auto_assign' => 'boolean',
            'auto_assign_agents' => 'nullable|array',
            'auto_assign_agents.*' => 'integer',
            'away_mode_active' => 'boolean',
            'away_message' => 'nullable|string',
            'welcome_mode_active' => 'boolean',
            'welcome_message' => 'nullable|string',
            'business_hours_start' => 'nullable',
            'business_hours_end'   => 'nullable|after:business_hours_start',

            'off_days'   => 'nullable|array|max:7',
            'off_days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'ai_auto_response' => 'boolean',
            'ai_custom_instructions' => 'nullable|string',
        ]);

        $settings->update($data);
        return response()->json($settings);
    }

    public function storeQuickReply(Request $request)
    {
        $data = $request->validate([
            'shortcut' => 'required|string|max:50',
            'group_name' => 'nullable|string|max:100',
            'type' => 'nullable|string|in:text,image',
            'text' => 'nullable|string',
            'image' => 'nullable|file|image|max:10240',
            'images' => 'nullable|array',
            'images.*' => 'file|image|max:10240',
        ]);

        $hasImages = $request->hasFile('image') || $request->hasFile('images');
        $type = $data['type'] ?? ($hasImages ? 'image' : 'text');

        if ($type === 'text' && empty($data['text'])) {
            return response()->json(['error' => 'Text response is required for text shortcuts.'], 422);
        }

        if ($type === 'image' && !$hasImages) {
            return response()->json(['error' => 'At least one image file is required for image shortcuts.'], 422);
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imagePaths[] = FileUploadHelper::upload($file, 'quick-replies', 'r2');
            }
        } elseif ($request->hasFile('image')) {
            $imagePaths[] = FileUploadHelper::upload($request->file('image'), 'quick-replies', 'r2');
        }

        $qr = QuikReply::create([
            'shortcut' => $data['shortcut'],
            'group_name' => !empty($data['group_name']) ? trim($data['group_name']) : null,
            'type' => $type,
            'text' => $data['text'] ?? null,
            'image_path' => $imagePaths[0] ?? null,
            'image_paths' => !empty($imagePaths) ? $imagePaths : null,
        ]);

        return response()->json($qr, 201);
    }

    public function updateQuickReply(Request $request, $id)
    {
        $qr = QuikReply::findOrFail($id);

        $data = $request->validate([
            'shortcut' => 'required|string|max:50',
            'group_name' => 'nullable|string|max:100',
            'type' => 'nullable|string|in:text,image',
            'text' => 'nullable|string',
            'image' => 'nullable|file|image|max:10240',
            'images' => 'nullable|array',
            'images.*' => 'file|image|max:10240',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'string',
        ]);

        $type = $data['type'] ?? $qr->type;
        $imagePaths = $request->input('existing_images', []);

        // Upload any newly added images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imagePaths[] = FileUploadHelper::upload($file, 'quick-replies', 'r2');
            }
        } elseif ($request->hasFile('image')) {
            $imagePaths[] = FileUploadHelper::upload($request->file('image'), 'quick-replies', 'r2');
        }

        // If no images retained and none uploaded, but was previously image type:
        if ($type === 'image' && empty($imagePaths)) {
            // Keep existing if nothing passed
            if (!empty($qr->image_paths)) {
                $imagePaths = $qr->image_paths;
            } elseif ($qr->image_path) {
                $imagePaths = [$qr->image_path];
            }
        }

        $qr->update([
            'shortcut' => $data['shortcut'],
            'group_name' => !empty($data['group_name']) ? trim($data['group_name']) : null,
            'type' => $type,
            'text' => $data['text'] ?? null,
            'image_path' => $imagePaths[0] ?? null,
            'image_paths' => !empty($imagePaths) ? array_values($imagePaths) : null,
        ]);

        return response()->json($qr);
    }

    public function destroyQuickReply(Request $request, $id)
    {
        $qr = QuikReply::findOrFail($id);

        $allImages = array_filter(array_unique(array_merge(
            $qr->image_paths ?? [],
            $qr->image_path ? [$qr->image_path] : []
        )));

        foreach ($allImages as $img) {
            try {
                Storage::disk('r2')->delete($img);
            } catch (\Exception $e) {
                // Ignore delete failures
            }
        }

        $qr->delete();
        return response()->json(['message' => 'Quick reply deleted']);
    }

    public function storeAdMapping(Request $request)
    {
        $companyId = $request->user()->company_id;

        $data = $request->validate([
            'ad_id' => 'required|string|max:100',
            'title' => 'nullable|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'ad_info' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['company_id'] = $companyId;
        $data['is_active'] = $data['is_active'] ?? true;

        $mapping = \App\Models\OmniAdMapping::updateOrCreate(
            ['company_id' => $companyId, 'ad_id' => $data['ad_id']],
            $data
        );

        $mapping->load('product:id,title,regular_price');

        return response()->json($mapping, 201);
    }

    public function destroyAdMapping(Request $request, $id)
    {
        $companyId = $request->user()->company_id;
        $mapping = \App\Models\OmniAdMapping::where('company_id', $companyId)->findOrFail($id);
        $mapping->delete();

        return response()->json(['message' => 'Ad mapping deleted']);
    }
}
