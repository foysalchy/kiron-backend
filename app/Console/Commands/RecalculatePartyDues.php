<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Party;
use App\Services\PartyDueService;
use Illuminate\Support\Facades\Log;

class RecalculatePartyDues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parties:recalculate-dues {--type= : Filter by party type (1 for customer, 2 for supplier)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate due amounts for parties (customers and suppliers)';

    /**
     * Execute the console command.
     */
    public function handle(PartyDueService $partyDueService): int
    {
        $this->info('Starting party due recalculation...');

        $query = Party::query();
        $type = $this->option('type');

        if ($type !== null) {
            $query->where('type', (int) $type);
        }

        $total = $query->count();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updatedCount = 0;

        $query->chunkById(100, function ($parties) use ($partyDueService, &$updatedCount, $bar) {
            foreach ($parties as $party) {
                $partyDueService->recalculatePartyDue($party->id);
                $updatedCount++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        $this->info("Successfully recalculated dues for {$updatedCount} parties.");
        Log::info("Command 'parties:recalculate-dues' completed. Total updated: {$updatedCount}");

        return Command::SUCCESS;
    }
}
