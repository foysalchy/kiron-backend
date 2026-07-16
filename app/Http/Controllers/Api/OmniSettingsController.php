<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\OmniSetting;
use App\Models\QuikReply;
use Illuminate\Http\Request;

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

        $quickReplies = QuikReply::get();

        return response()->json([
            'settings' => $settings,
            'quick_replies' => $quickReplies
        ]);
    }

    public function saveSettings(Request $request)
    {

        $settings = OmniSetting::firstOrFail();

        $data = $request->validate([
            'company_name' => 'nullable|string|max:150',
            'auto_assign' => 'boolean',
            'auto_assign_agents' => 'nullable|array',
            'auto_assign_agents.*' => 'integer|exists:users,id',
            'away_mode_active' => 'boolean',
            'away_message' => 'nullable|string',
            'welcome_mode_active' => 'boolean',
            'welcome_message' => 'nullable|string'
        ]);

        $settings->update($data);
        return response()->json($settings);
    }

    public function storeQuickReply(Request $request)
    {
        $data = $request->validate([
            'shortcut' => 'required|string|max:50',
            'text' => 'required|string'
        ]);

        $qr = QuikReply::create([
            'shortcut' => $data['shortcut'],
            'text' => $data['text'],

        ]);

        return response()->json($qr, 201);
    }

    public function destroyQuickReply(Request $request, $id)
    {
        $qr = QuikReply::findOrFail($id);
        $qr->delete();
        return response()->json(['message' => 'Quick reply deleted']);
    }
}
