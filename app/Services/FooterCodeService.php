<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\FooterCode;
use Illuminate\Support\Facades\{DB, Log};

class FooterCodeService
{
    /**
     * Get Footer Code for the current company
     */
    public function getFooterCode(): ?FooterCode
    {
        try {
            return FooterCode::first();
        } catch (\Exception $e) {
            Log::error('Error fetching footer code: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch footer code');
        }
    }
    /**
     * Save or Update Footer Code
     */
    public function saveFooterCode(array $data): FooterCode
    {
        DB::beginTransaction();
        try {
            $footerCode = FooterCode::updateOrCreate(
                [],
                [
                    'code'   => $data['code'],
                    'status' => $data['status'] ?? 1,
                ]
            );

            $action = $footerCode->wasRecentlyCreated ? 'created' : 'updated';
            LogHelper::$action('header_code', $footerCode->id, $footerCode->company_id, 'Code scripts updated');

            Log::info("Footer code {$action} successfully", ['id' => $footerCode->id]);

            DB::commit();
            return $footerCode->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Code save failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to save  code');
        }
    }
}
