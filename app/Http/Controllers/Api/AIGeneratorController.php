<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\AI\AiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIGeneratorController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'language' => 'nullable|string|in:en,bn',
        ]);

        $companyId = auth()->user()->company_id;
        $params = $request->all();

        try {
            $content = AiService::generate($companyId, $params);
            
            return ResponseHelper::success(['content' => $content], 'Content generated successfully.');
        } catch (\Exception $e) {
            // Distinguish user-friendly errors
            $msg = $e->getMessage();
            if (str_contains($msg, 'not configured') || str_contains($msg, 'API Error')) {
                return ResponseHelper::error($msg);
            }
            return ResponseHelper::error("Unable to generate content. Please try again.");
        }
    }
}
