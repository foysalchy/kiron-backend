<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReferralCommission;
use App\Models\ReferralPartner;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReleasePendingCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'referral:release-commissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Release pending affiliate commissions that are older than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting pending commission release process...');

        $sevenDaysAgo = Carbon::now()->subDays(7);

        $pendingCommissions = ReferralCommission::where('status', 'pending')
            ->where('created_at', '<=', $sevenDaysAgo)
            ->get();

        if ($pendingCommissions->isEmpty()) {
            $this->info('No pending commissions to release.');
            return;
        }

        $count = 0;

        foreach ($pendingCommissions as $commission) {
            DB::beginTransaction();
            try {
                // Change status to approved
                $commission->status = 'approved';
                $commission->notes = $commission->notes . ' | Released after 7-day hold';
                $commission->save();

                $partner = ReferralPartner::find($commission->referral_partner_id);
                if ($partner) {
                    $partner->decrement('pending_balance', $commission->commission_amount);
                    $partner->increment('wallet_balance', $commission->commission_amount);
                    $partner->increment('total_earned', $commission->commission_amount);
                }

                DB::commit();
                $count++;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to release commission #{$commission->id}: " . $e->getMessage());
                $this->error("Failed to release commission #{$commission->id}. Check logs.");
            }
        }

        $this->info("Successfully released {$count} commissions.");
    }
}
