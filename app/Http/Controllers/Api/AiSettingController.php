<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Services\AI\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiSettingController extends Controller
{
    public function show(): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $settings = AiSetting::firstOrCreate(
            ['company_id' => $companyId],
            [
                'default_provider' => 'openai',
                'openai_status' => false,
                'openai_model' => 'gpt-4o-mini',
                'gemini_status' => false,
                'gemini_model' => 'gemini-flash-latest',
            ]
        );

        $data = $settings->toArray();
        
        // Mask API Keys for security
        if (!empty($settings->openai_key)) {
            $data['openai_key'] = 'sk-••••••••••••••••••••••••••••••••';
        }
        if (!empty($settings->gemini_key)) {
            $data['gemini_key'] = 'AIzaSy••••••••••••••••••••••••••••••';
        }

        return ResponseHelper::success($data);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'default_provider' => 'in:openai,gemini',
            'openai_status' => 'boolean',
            'openai_key' => 'nullable|string',
            'openai_model' => 'nullable|string',
            'openai_instructions' => 'nullable|string',
            'gemini_status' => 'boolean',
            'gemini_key' => 'nullable|string',
            'gemini_model' => 'nullable|string',
            'gemini_instructions' => 'nullable|string',
        ]);

        $companyId = auth()->user()->company_id;
        $settings = AiSetting::firstOrCreate(['company_id' => $companyId]);

        $updateData = $request->except(['openai_key', 'gemini_key']);

        // Only update key if it doesn't contain the mask character (meaning it was changed by user)
        if ($request->has('openai_key') && !str_contains($request->openai_key, '••••')) {
            $updateData['openai_key'] = $request->openai_key;
        }
        
        if ($request->has('gemini_key') && !str_contains($request->gemini_key, '••••')) {
            $updateData['gemini_key'] = $request->gemini_key;
        }

        $settings->update($updateData);

        return ResponseHelper::success(null, 'AI Settings updated successfully.');
    }

    public function testConnection(Request $request): JsonResponse
    {
        $request->validate([
            'provider' => 'required|in:openai,gemini',
            'api_key' => 'nullable|string',
            'model' => 'nullable|string',
        ]);

        $companyId = auth()->user()->company_id;
        
        try {
            $success = AiService::testConnection(
                $companyId, 
                $request->provider, 
                $request->api_key, 
                $request->model
            );

            if ($success) {
                return ResponseHelper::success(null, 'Connection successful.');
            }
            
            return ResponseHelper::error('Invalid API key or provider unavailable.');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
}
