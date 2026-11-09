<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanAbandonedCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carts:clean-abandoned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up abandoned carts older than 1 month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneMonthAgo = Carbon::now()->subDays(30);

        // Delete abandoned carts older than 30 days across all companies
        $deletedCount = Cart::withoutGlobalScopes()
            ->where('status', Cart::ADDED)
            ->where('updated_at', '<=', $oneMonthAgo)
            ->delete();

        $this->info("Successfully deleted {$deletedCount} abandoned cart item(s) older than 1 month.");

        return Command::SUCCESS;
    }
}

