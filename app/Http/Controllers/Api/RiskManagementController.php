<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Services\RiskManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiskManagementController extends Controller
{
    /**
     * Get Company Risk Management Executive Overview
     */
    public function overview(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id ?? 27;
        $data = RiskManagementService::getCompanyRiskOverview($companyId);

        return response()->json([
            'success' => true,
            'message' => 'Company risk intelligence overview retrieved successfully',
            'data'    => $data,
        ]);
    }

    /**
     * Get Individual Customer Risk Assessment & Credit Profile
     */
    public function customerProfile(Request $request, int $id): JsonResponse
    {
        $companyId = $request->user()->company_id ?? 27;
        $customer = Party::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('id', $id)
            ->firstOrFail();

        $newDue = (float)$request->query('new_due', 0);
        $data = RiskManagementService::evaluateCustomerRisk($customer, $newDue);

        return response()->json([
            'success' => true,
            'message' => 'Customer risk assessment retrieved successfully',
            'data'    => $data,
        ]);
    }

    /**
     * Pre-flight Check during POS / Checkout
     */
    public function checkCredit(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id ?? 27;
        $customerId = $request->query('customer_id');
        $newDue = (float)$request->query('due_amount', 0);

        if (!$customerId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'allow_credit_sale' => true,
                    'is_credit_exceeded' => false,
                ]
            ]);
        }

        $customer = Party::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->find($customerId);

        $data = RiskManagementService::evaluateCustomerRisk($customer, $newDue);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Get Dead Stock & Capital Analytics
     */
    public function deadStock(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id ?? 27;
        $data = RiskManagementService::getDeadStockAnalytics($companyId);

        return response()->json([
            'success' => true,
            'message' => 'Dead stock analytics retrieved successfully',
            'data'    => $data,
        ]);
    }

    /**
     * Get Liquidity Risk Analytics
     */
    public function liquidity(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id ?? 27;
        $data = RiskManagementService::getLiquidityRiskAnalytics($companyId);

        return response()->json([
            'success' => true,
            'message' => 'Liquidity risk analytics retrieved successfully',
            'data'    => $data,
        ]);
    }

    /**
     * Get Company Risk Policy Settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id ?? 27;
        $settings = RiskManagementService::getSettings($companyId);

        return response()->json([
            'success' => true,
            'message' => 'Risk settings retrieved successfully',
            'data'    => $settings,
        ]);
    }

    /**
     * Update Company Risk Policy Settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $companyId = $request->user()->company_id ?? 27;
        $validated = $request->validate([
            'block_over_credit_orders'   => 'nullable|boolean',
            'block_negative_cash'        => 'nullable|boolean',
            'unusual_expense_threshold'  => 'nullable|numeric|min:0',
            'block_below_cost_sale'      => 'nullable|boolean',
            'lock_backdated_entries'     => 'nullable|boolean',
            'backdated_entry_lock_days'  => 'nullable|integer|min:0',
            'high_return_threshold_pct'  => 'nullable|integer|min:0|max:100',
            'block_negative_stock'       => 'nullable|boolean',
            'dead_stock_threshold_days'  => 'nullable|integer|min:1',
        ]);

        $settings = RiskManagementService::updateSettings($companyId, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Risk policy settings updated successfully',
            'data'    => $settings,
        ]);
    }
}
