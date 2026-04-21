<?php

namespace App\Services;

use App\Exceptions\PackageLimitExceededException;
use App\Models\Company;
use App\Models\ExtraOrderCharge;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class LimitService
{
    private const MONTHLY_LIMITS = ['order'];

    public static function check(object $model): void
    {
        $user    = Auth::user();
        $company = null;

        if ($user) {
            // Dashboard user
            if ($user->role === 'super_admin') return;
            $company = $user->load('company.pricingPackage')->company;
        } else {
            $domainSetup = getCurrentCompany();
            if (!$domainSetup) return;

            $company = Company::with('pricingPackage')
                ->find($domainSetup->company_id);
        }

        if (!$company?->pricingPackage) return;

        $package  = $company->pricingPackage;
        $limitKey = $model->limitKey;
        $column   = $limitKey . '_limit';
        $limit    = $package->$column;

        // null মানে unlimited
        if ($limit === null) return;

        $isMonthly = in_array($limitKey, self::MONTHLY_LIMITS);
        $current   = self::getCurrentCount($model, $limitKey, $company->id, $isMonthly);

        if ($current >= $limit) {
            // Order এর জন্য extra charge check, বাকিগুলো block
            if ($limitKey === 'order') {
                // extra_order_charge null মানে extra order allow নেই
                if ($package->extra_order_charge === null) {
                    throw new PackageLimitExceededException('order', $limit, true);
                }
                // charge আছে → block করবো না, continue
                // actual charge record হবে Order created hook এ
                return;
            }

            throw new PackageLimitExceededException($limitKey, $limit, $isMonthly);
        }
    }

    private static function getCurrentCount(
        object $model,
        string $limitKey,
        int $companyId,
        bool $isMonthly
    ): int {
        $query = $model::withoutGlobalScope('company')
            ->where('company_id', $companyId);

        // Order এর জন্য শুধু sales type
        if ($limitKey === 'order') {
            $query->where('type', 'sales');
        }

        if ($isMonthly) {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }

        return $query->count();
    }
}
