<?php
// app/Http/Controllers/Api/PackageUsageController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\{CompanyMonthlyUsage, Product, Order, Domain, Employee};
use App\Models\User;

class PackageUsageController extends Controller
{
    public function index(): JsonResponse
    {
        $user    = Auth::user()->load('company.pricingPackage');
        $company = $user->company;

        if (!$company || !$company->pricingPackage) {
            return response()->json([
                'success' => true,
                'data'    => null, // null মানে unlimited
            ]);
        }

        $package   = $company->pricingPackage;
        $companyId = $company->id;
        $usage = CompanyMonthlyUsage::withoutGlobalScope('company')
            ->where('company_id', $companyId)
            ->where('year_month', now()->format('Y-m'))
            ->first();

        $data = [
            'product' => $this->countLimit(
                Product::withoutGlobalScope('company')
                    ->where('company_id', $companyId)
                    ->count(),
                $package->product_limit
            ),
            'user' => $this->countLimit(
                User::withoutGlobalScope('company')
                    ->where('company_id', $companyId)
                    ->count(),
                $package->user_limit
            ),
            'employee' => $this->countLimit(
                Employee::withoutGlobalScope('company')
                    ->where('company_id', $companyId)
                    ->count(),
                $package->employee_limit
            ),


            'order' => $this->countLimit(
                $usage->order_count ?? 0,
                $package->order_limit,
                true
            ),
            'domain' => $this->countLimit(
                Domain::withoutGlobalScope('company')
                    ->where('company_id', $companyId)
                    ->count(),
                $package->domain_limit
            ),
        ];

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    private function countLimit(int $used, ?int $limit, bool $monthly = false): array
    {
        return [
            'used'      => $used,
            'limit'     => $limit,                                        // null = unlimited
            'remaining' => $limit === null ? null : max(0, $limit - $used),
            'exceeded'  => $limit !== null && $used >= $limit,
            'monthly'   => $monthly,
        ];
    }
}
