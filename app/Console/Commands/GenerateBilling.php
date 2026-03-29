<?php

namespace App\Console\Commands;

use App\Enums\Status;
use App\Models\Billing;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate {period=monthly} {--start=} {--end=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate billing report for all companies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->argument('period');
        $startDateInput = $this->option('start');
        $endDateInput = $this->option('end');

        // if is it manual date
        if ($startDateInput && $endDateInput) {
            $startDate = Carbon::parse($startDateInput)->startOfDay();
            $endDate = Carbon::parse($endDateInput)->endOfDay();
            $period = 'custom';
        } else {
            [$startDate, $endDate] = match($period) {
                'daily'   => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
                'weekly'  => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
                'monthly' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
                default   => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            };
        }

        $companies = Company::where('status',Status::Active->value)->get();

        foreach ($companies as $company) {
            $periodOrders = $company->orders()
                ->whereBetween('created_at', [$startDate, $endDate]);

            Billing::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'start_date' => $startDate->toDateString(),
                    'end_date'   => $endDate->toDateString(),
                ],
                [
                    'amount'             => $periodOrders->sum('grand_total'),
                    'new_orders_count'   => $periodOrders->count(),
                    'total_orders_count' => $company->orders()->count(),
                    'period'             => $period,
                ]
            );

            $this->info("Report generated: {$company->name} ($period)");
        }
    }
}
