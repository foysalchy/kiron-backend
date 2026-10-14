<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ResponseHelper;
use App\Models\StatusMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatusMappingController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $mappings = StatusMapping::where('company_id', $companyId)->get();
        
        return ResponseHelper::success($mappings, 'Status mappings retrieved successfully.');
    }

    public function update(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $mappingsData = $request->input('mappings'); // Array of {self_status: X, mappings: {woo: Y, pathao: Z}}

        DB::beginTransaction();
        try {
            // Remove existing mappings for this company to handle changed/reassigned self_status
            StatusMapping::where('company_id', $companyId)->delete();

            if (is_array($mappingsData)) {
                foreach ($mappingsData as $data) {
                    if (isset($data['self_status'])) {
                        $selfStatus = (int) $data['self_status'];
                        $providerMappings = $data['mappings'] ?? [];

                        StatusMapping::create([
                            'company_id' => $companyId,
                            'self_status' => $selfStatus,
                            'mappings' => $providerMappings,
                        ]);
                    }
                }
            }
            DB::commit();
            
            $updatedMappings = StatusMapping::where('company_id', $companyId)->get();
            return ResponseHelper::success($updatedMappings, 'Status mappings updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error('Failed to update status mappings: ' . $e->getMessage());
        }
    }
}
