<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanySubscription;
use App\Models\ReferralPartner;
use App\Models\ReferralAttribution;
use App\Models\ReferralWithdrawal;
use App\Models\Lead;
use App\Models\SubscriptionPayment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SaasDashboardController extends Controller
{
    /**
     * Resolve the date range from request params (period / start_date / end_date).
     * Mirrors the same logic used in the seller Business Dashboard.
     */
    private function resolveDateRange(Request $request): array
    {
        $period    = $request->input('period', 'this_month');
        $now       = Carbon::now();

        switch ($period) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end   = $now->copy()->endOfDay();
                break;
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth();
                $end   = $now->copy()->subMonth()->endOfMonth();
                break;
            case 'last_7_days':
                $start = $now->copy()->subDays(6)->startOfDay();
                $end   = $now->copy()->endOfDay();
                break;
            case 'custom':
                $start = $request->input('start_date')
                    ? Carbon::parse($request->input('start_date'))->startOfDay()
                    : $now->copy()->startOfMonth();
                $end = $request->input('end_date')
                    ? Carbon::parse($request->input('end_date'))->endOfDay()
                    : $now->copy()->endOfDay();
                break;
            case 'this_month':
            default:
                $start = $now->copy()->startOfMonth();
                $end   = $now->copy()->endOfDay();
                break;
        }

        return [$start, $end];
    }

    public function getStats(Request $request): JsonResponse
    {
        [$start, $end] = $this->resolveDateRange($request);

        // ── Seller / Company Stats ──
        $totalSellers  = Company::count();
        $newSellers    = Company::whereBetween('created_at', [$start, $end])->count();

        $activeSellers = Company::whereHas('currentSubscription', function ($q) {
            $q->where('ends_at', '>=', Carbon::now());
        })->count();

        $freeTrialSellers = Company::whereHas('currentSubscription', function ($q) {
            $q->whereNotNull('trial_ends_at')->where('trial_ends_at', '>=', Carbon::now());
        })->count();

        // Paid = active subscription AND not on free trial
        $paidSellers = Company::whereHas('currentSubscription', function ($q) {
            $q->where('ends_at', '>=', Carbon::now())
              ->where(function ($q2) {
                  $q2->whereNull('trial_ends_at')
                     ->orWhere('trial_ends_at', '<', Carbon::now());
              });
        })->count();

        $inactiveSellers = $totalSellers - $activeSellers;

        // ── Financial Stats (period-scoped) ──
        $totalCollection = SubscriptionPayment::where('status', 'success')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $totalDue = SubscriptionPayment::where('status', 'pending')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $totalFreeTrialAmount = CompanySubscription::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>=', Carbon::now())
            ->sum('amount_paid');

        // ── Affiliate Stats ──
        $totalAffiliates    = ReferralPartner::count();
        $totalCommission    = ReferralAttribution::sum('total_commission_earned');
        $totalPaidCommission = ReferralWithdrawal::where('status', 'approved')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
        $totalDueCommission = ReferralWithdrawal::where('status', 'pending')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        // ── Lead Stats ──
        $totalLeads = Lead::whereNull('leads.company_id')->count();

        $leadsByStatus = DB::table('leads')
            ->leftJoin('lead_statuses', 'leads.lead_status_id', '=', 'lead_statuses.id')
            ->whereNull('leads.company_id')
            ->select('lead_statuses.name as status_name', DB::raw('count(leads.id) as count'))
            ->groupBy('lead_statuses.id', 'lead_statuses.name')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'period' => [
                    'start' => $start->format('d M Y'),
                    'end'   => $end->format('d M Y'),
                ],
                'sellers' => [
                    'total'      => $totalSellers,
                    'new'        => $newSellers,
                    'active'     => $activeSellers,
                    'paid'       => $paidSellers,
                    'inactive'   => $inactiveSellers,
                    'free_trial' => $freeTrialSellers,
                ],
                'financials' => [
                    'collection'        => $totalCollection,
                    'due'               => $totalDue,
                    'free_trial_amount' => $totalFreeTrialAmount,
                ],
                'affiliates' => [
                    'total_members'    => $totalAffiliates,
                    'total_commission' => $totalCommission,
                    'paid_commission'  => $totalPaidCommission,
                    'due_commission'   => $totalDueCommission,
                ],
                'leads' => [
                    'total'     => $totalLeads,
                    'by_status' => $leadsByStatus,
                ],
            ],
        ]);
    }

    public function getCharts(Request $request): JsonResponse
    {
        [$start, $end] = $this->resolveDateRange($request);

        // ── All new accounts created in range ──
        $accounts = Company::whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as total'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // ── Free trial accounts (have an active trial on that registration date) ──
        $trialAccounts = Company::whereBetween('companies.created_at', [$start, $end])
            ->join('company_subscriptions', function ($join) {
                $join->on('companies.id', '=', 'company_subscriptions.company_id')
                     ->whereNotNull('company_subscriptions.trial_ends_at')
                     ->where('company_subscriptions.trial_ends_at', '>=', Carbon::now());
            })
            ->select(DB::raw('DATE(companies.created_at) as date'), DB::raw('count(companies.id) as count'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // ── Inactive accounts (no active subscription) registered in range ──
        $inactiveAccounts = Company::whereBetween('companies.created_at', [$start, $end])
            ->whereDoesntHave('currentSubscription', function ($q) {
                $q->where('ends_at', '>=', Carbon::now());
            })
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as count'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // ── Collections by date ──
        $collections = SubscriptionPayment::where('status', 'success')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as amount'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartData   = [];
        $currentDate = $start->copy();

        while ($currentDate <= $end) {
            $dateStr = $currentDate->format('Y-m-d');

            $total    = $accounts->has($dateStr)         ? (int) $accounts[$dateStr]->total         : 0;
            $trial    = $trialAccounts->has($dateStr)    ? (int) $trialAccounts[$dateStr]->count    : 0;
            $inactive = $inactiveAccounts->has($dateStr) ? (int) $inactiveAccounts[$dateStr]->count : 0;
            // "new_active" = total registered that day minus trial minus inactive
            $newActive = max(0, $total - $trial - $inactive);

            $chartData[] = [
                'date'         => $currentDate->format('d M'),
                'full_date'    => $dateStr,
                'new_accounts' => $total,
                'new_active'   => $newActive,
                'trial'        => $trial,
                'inactive'     => $inactive,
                'collection'   => $collections->has($dateStr) ? (float) $collections[$dateStr]->amount : 0,
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'status' => 'success',
            'data'   => $chartData,
        ]);
    }

    public function getOverdueInvoices(Request $request): JsonResponse
    {
        $overdue = SubscriptionPayment::with(['company:id,name,email', 'subscription.pricingPackage:id,name'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $overdue,
        ]);
    }
}
