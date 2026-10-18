<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateProductDescriptionPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-description-price {--dry-run : Preview changes without updating the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update price inside full_description based on regular_price and discount from products table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn("⚠️  Running in DRY-RUN mode. No changes will be saved to the database.\n");
        }

        $tableName = 'products';

        // মোট প্রোডাক্ট সংখ্যা চেক
        $totalProducts = DB::table($tableName)->count();
        if ($totalProducts === 0) {
            $this->error("No products found in '{$tableName}' table.");
            return Command::FAILURE;
        }

        $this->info("Found {$totalProducts} products. Processing...");
        $bar = $this->output->createProgressBar($totalProducts);
        $bar->start();

        $updatedCount = 0;
        $skippedCount = 0;

        // Chunking ব্যবহার করে ৪০০+ প্রোডাক্ট মেমোরি ক্র্যাশ ছাড়া প্রসেস করা হবে
        DB::table($tableName)->orderBy('id')->chunk(100, function ($products) use (&$updatedCount, &$skippedCount, $isDryRun, $tableName, $bar) {
            foreach ($products as $product) {
                $reg = floatval($product->regular_price ?? 0);
                $discount = floatval($product->discount ?? 0);
                $discountType = strtolower(trim($product->discount_type ?? 'flat'));

                // লাইভ ডাটাবেজের মান থেকে সরাসরি ফাইনাল প্রাইস হিসাব
                $finalPrice = 0;
                if ($reg > 0 && $discount > 0) {
                    if ($discountType === 'percent' || $discountType === 'percentage') {
                        $finalPrice = $reg - round(($reg * $discount) / 100);
                    } else {
                        // flat discount (e.g. 259 - 26 = 233)
                        $finalPrice = $reg - $discount;
                    }
                } elseif ($reg > 0) {
                    $finalPrice = $reg;
                }

                $needsUpdate = false;
                $updateData = [];

                // ১. full_description চেক ও আপডেট
                if ($finalPrice > 0 && !empty($product->full_description)) {
                    $newFullDesc = $this->updatePriceInText($product->full_description, $finalPrice);
                    if ($newFullDesc !== $product->full_description) {
                        $updateData['full_description'] = $newFullDesc;
                        $needsUpdate = true;
                    }
                }

                // ২. short_description-এ যদি থেকে থাকে
                if ($finalPrice > 0 && !empty($product->short_description)) {
                    $newShortDesc = $this->updatePriceInText($product->short_description, $finalPrice);
                    if ($newShortDesc !== $product->short_description) {
                        $updateData['short_description'] = $newShortDesc;
                        $needsUpdate = true;
                    }
                }

                // ডাটাবেজ আপডেট
                if ($needsUpdate) {
                    if (!$isDryRun) {
                        DB::table($tableName)->where('id', $product->id)->update($updateData);
                    }
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Process Completed!");
        $this->line("Total Checked: {$totalProducts}");
        $this->info("Updated: {$updatedCount}");
        $this->line("Unchanged/Skipped: {$skippedCount}");

        if ($isDryRun) {
            $this->warn("\nDry-run complete. Run without '--dry-run' to save permanently to database.");
        }

        return Command::SUCCESS;
    }

    /**
     * ডেসক্রিপশনের ভেতর টাকার সাইনসহ থাকা প্রাইস টেক্সট আপডেট করার মেথড
     */
    private function updatePriceInText(string $description, float $finalPrice): string
    {
        $formattedPrice = (floor($finalPrice) == $finalPrice) ? number_format($finalPrice) : number_format($finalPrice, 2);

        // টাকার সাইন/শব্দ থাকা বাধ্যতামূলক (৳, Tk, Tk., Taka, BDT, &#2547;)
        // যাতে টাইটেলের মডেল নম্বর (যেমন 6074761) কোনোভাবেই পরিবর্তিত না হয়
        $currencyPattern = '(?:৳|Tk\.?|Taka|BDT|&#2547;|&amp;#2547;)';

        $pattern = '/(The latest price of\b.*?\bin Bangladesh is\s*' . $currencyPattern . '\s*)([0-9,]+(?:\.[0-9]+)?)/ius';
        if (!preg_match($pattern, $description)) {
            $pattern = '/(The latest price of\b.*?\bis\s*' . $currencyPattern . '\s*)([0-9,]+(?:\.[0-9]+)?)/ius';
        }

        return preg_replace_callback($pattern, function ($matches) use ($formattedPrice) {
            $prefix = preg_replace('/\s*(?:৳|Tk\.?|Taka|BDT|&#2547;|&amp;#2547;)\s*$/iu', '', $matches[1]);
            return $prefix . ' ৳ ' . $formattedPrice;
        }, $description);
    }
}
