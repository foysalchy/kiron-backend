<?php
namespace App\Services;

use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\Market;
use Illuminate\Support\Facades\{DB,Log};

class MarketService
{
    /**
     * Update Market Tools (Company ID handled by Global Scope)
     */
    public function updateMarketTools(array $data): Market
    {
        DB::beginTransaction();
        try {
            $companyId = $data['company_id'] ?? auth()->user()->company_id;

            $market = Market::forCompany($companyId)->first();

            if (!$market) {
                $data['company_id'] = $companyId;
                $market = Market::create($data);
            } else {
                $market->update($data);
            }

            DB::commit();
            return $market;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
