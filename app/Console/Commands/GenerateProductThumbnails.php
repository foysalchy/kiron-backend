<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Helpers\FileUploadHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateProductThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:generate-thumbnails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate 310x310 and 95x95 thumbnails for existing products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting thumbnail generation for existing products...');

        // Only select products that actually have a main thumbnail
        $products = Product::whereNotNull('thumbnail')->get();
        
        $count = 0;
        $total = $products->count();
        $this->info("Found {$total} products with a main thumbnail.");

        foreach ($products as $product) {
            $updated = false;
            $customFileName = Str::slug($product->slug ?? 'product') . '-' . time();

            // Generate 310x310 if missing
            if (empty($product->thumbnail_310)) {
                try {
                    $thumbnail310FileName = $customFileName . '-310x310';
                    $product->thumbnail_310 = FileUploadHelper::generateResizedFromExisting(
                        $product->thumbnail,
                        'products/thumbnails',
                        310,
                        310,
                        'r2',
                        $thumbnail310FileName
                    );
                    $updated = true;
                } catch (\Exception $e) {
                    $this->error("Failed to generate 310x310 for product ID {$product->id}: " . $e->getMessage());
                    Log::error("Failed to generate 310x310 for product ID {$product->id}: " . $e->getMessage());
                }
            }

            // Generate 95x95 if missing
            if (empty($product->thumbnail_95)) {
                try {
                    $thumbnail95FileName = $customFileName . '-95x95';
                    $product->thumbnail_95 = FileUploadHelper::generateResizedFromExisting(
                        $product->thumbnail,
                        'products/thumbnails',
                        95,
                        95,
                        'r2',
                        $thumbnail95FileName
                    );
                    $updated = true;
                } catch (\Exception $e) {
                    $this->error("Failed to generate 95x95 for product ID {$product->id}: " . $e->getMessage());
                    Log::error("Failed to generate 95x95 for product ID {$product->id}: " . $e->getMessage());
                }
            }

            if ($updated) {
                // Save without triggering model events (like caching etc.) to speed up the loop
                $product->saveQuietly();
                $count++;
                $this->info("Generated thumbnails for product ID {$product->id}");
            }
        }

        $this->info("Thumbnail generation completed! Updated {$count} products.");
    }
}
