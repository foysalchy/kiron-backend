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
    public function getStats(): JsonResponse
    {
        // ── Seller / Company Stats ──
        $totalSellers = Company::count();
        $newSellers = Company::whereMonth('created_at', Carbon::now()->month)->count();
        
        // Active = has a subscription with ends_at > now or status active
        $activeSellers = Company::whereHas('currentSubscription', function($q) {
            $q->where('ends_at', '>=', Carbon::now());
        })->count();
        
        $freeTrialSellers = Company::whereHas('currentSubscription', function($q) {
            $q->whereNotNull('trial_ends_at')->where('trial_ends_at', '>=', Carbon::now());
        })->count();
        
        $inactiveSellers = $totalSellers - $activeSellers;

        // ── Financial Stats ──
        $totalCollection = SubscriptionPayment::where('status', 'success')->sum('amount');
        $totalDue = SubscriptionPayment::where('status', 'pending')->sum('amount');
        
        // Free Trial Amount (potential revenue being trialed)
        $totalFreeTrialAmount = CompanySubscription::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>=', Carbon::now())
            ->sum('amount_paid');

        // ── Affiliate Stats ──
        $totalAffiliates = ReferralPartner::count();
        $totalCommission = ReferralAttribution::sum('total_commission_earned');
        $totalPaidCommission = ReferralWithdrawal::where('status', 'approved')->sum('amount');
        $totalDueCommission = ReferralWithdrawal::where('status', 'pending')->sum('amount');

        // ── Lead Stats (SaaS Leads where company_id is null) ──
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
                'sellers' => [
                    'total' => $totalSellers,
                    'new' => $newSellers,
                    'active' => $activeSellers,
                    'inactive' => $inactiveSellers,
                    'free_trial' => $freeTrialSellers,
                ],
                'financials' => [
                    'collection' => $totalCollection,
                    'due' => $totalDue,
                    'free_trial_amount' => $totalFreeTrialAmount,
                ],
                'affiliates' => [
                    'total_members' => $totalAffiliates,
                    'total_commission' => $totalCommission,
                    'paid_commission' => $totalPaidCommission,
                    'due_commission' => $totalDueCommission,
                ],
                'leads' => [
                    'total' => $totalLeads,
                    'by_status' => $leadsByStatus,
                ]
            ]
        ]);
    }

    public function getCharts(Request $request): JsonResponse
    {
        // 30 days data
        $startDate = Carbon::now()->subDays(29)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // New Accounts by date
        $accounts = Company::whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as count'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Collections by date
        $collections = SubscriptionPayment::where('status', 'success')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(amount) as amount'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartData = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $chartData[] = [
                'date' => $currentDate->format('d M'),
                'full_date' => $dateStr,
                'new_accounts' => $accounts->has($dateStr) ? $accounts[$dateStr]->count : 0,
                'collection' => $collections->has($dateStr) ? (float) $collections[$dateStr]->amount : 0,
            ];
            $currentDate->addDay();
        }

        return response()->json([
            'status' => 'success',
            'data' => $chartData
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
            'data' => $overdue
        ]);
    }
}
