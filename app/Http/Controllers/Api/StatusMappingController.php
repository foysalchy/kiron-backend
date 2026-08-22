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
        $mappingsData = $request->input('mappings'); // Array of {kiron_status: X, mappings: {woo: Y, pathao: Z}}

        DB::beginTransaction();
        try {
            foreach ($mappingsData as $data) {
                StatusMapping::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'kiron_status' => $data['kiron_status']
                    ],
                    [
                        'mappings' => $data['mappings']
                    ]
                );
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
