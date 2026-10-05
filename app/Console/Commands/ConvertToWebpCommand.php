<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ConvertToWebpCommand extends Command
{
    protected $signature = 'dorja:convert-to-webp';
    protected $description = 'Download legacy images from R2, convert them to WebP, upload back, and update database';

    public function handle()
    {
        $this->info("Starting WebP Conversion process...");

        // 1. Products
        $this->info("Processing Products...");
        $products = DB::table('products')->whereNotNull('thumbnail')->get();
        foreach ($products as $product) {
            $this->convertAndReplace('products', 'thumbnail', $product->id, $product->thumbnail, 'products/images');
            
            // Handle galleries (JSON array)
            if (!empty($product->galleries)) {
                $galleries = json_decode($product->galleries, true);
                if (is_array($galleries)) {
                    $newGalleries = [];
                    foreach ($galleries as $oldPath) {
                        $newPath = $this->convertAndReplaceReturnPath($oldPath, 'products/galleries');
                        $newGalleries[] = $newPath;
                    }
                    DB::table('products')->where('id', $product->id)->update(['galleries' => json_encode($newGalleries)]);
                }
            }
        }

        // 1.5 Product Variations
        $this->info("Processing Product Variations...");
        $variations = DB::table('product_variations')->whereNotNull('image')->get();
        foreach ($variations as $variation) {
            $this->convertAndReplace('product_variations', 'image', $variation->id, $variation->image, 'products/variations');
        }

        // 1.6 Variation Galleries
        $this->info("Processing Variation Galleries...");
        $variationGalleries = DB::table('variation_galleries')->whereNotNull('image')->get();
        foreach ($variationGalleries as $gallery) {
            $this->convertAndReplace('variation_galleries', 'image', $gallery->id, $gallery->image, 'products/variation_galleries');
        }

        // 2. Categories
        $this->info("Processing Categories...");
        $this->processSimpleTable('mega_categories', 'image', 'categories/images');
        $this->processSimpleTable('sub_categories', 'image', 'categories/images');
        $this->processSimpleTable('mini_categories', 'image', 'categories/mini_images');
        $this->processSimpleTable('extra_categories', 'image', 'categories/extra_images');

        // 2.5 Sliders
        $this->info("Processing Sliders...");
        $this->processSimpleTable('sliders', 'image', 'sliders/images');

        // 3. Pages
        $this->info("Processing Pages...");
        $this->processSimpleTable('pages', 'image', 'pages');

        // 4. Blogs (JSON)
        $this->info("Processing Blogs...");
        $blogs = DB::table('blogs')->whereNotNull('images')->get();
        foreach ($blogs as $blog) {
            if (!empty($blog->images)) {
                $images = json_decode($blog->images, true);
                if (is_array($images)) {
                    $newImages = [];
                    foreach ($images as $oldPath) {
                        $newPath = $this->convertAndReplaceReturnPath($oldPath, 'blogs/images');
                        $newImages[] = $newPath;
                    }
                    DB::table('blogs')->where('id', $blog->id)->update(['images' => json_encode($newImages)]);
                }
            }
        }

        // 5. Site Settings
        $this->info("Processing Site Settings...");
        $settings = DB::table('site_settings')->get();
        foreach ($settings as $setting) {
            $this->convertAndReplace('site_settings', 'logo', $setting->id, $setting->logo, 'settings');
            $this->convertAndReplace('site_settings', 'favicon', $setting->id, $setting->favicon, 'settings');
        }

        // 6. Users
        $this->info("Processing Users Profile...");
        $this->processSimpleTable('users', 'profile', 'users/profiles');

        $this->info("Conversion process completed!");
    }

    private function processSimpleTable($table, $column, $folder)
    {
        $records = DB::table($table)->whereNotNull($column)->get();
        foreach ($records as $record) {
            $this->convertAndReplace($table, $column, $record->id, $record->{$column}, $folder);
        }
    }

    private function convertAndReplace($table, $column, $id, $oldPath, $folder)
    {
        if (empty($oldPath)) return;

        $newPath = $this->convertAndReplaceReturnPath($oldPath, $folder);
        if ($newPath !== $oldPath) {
            DB::table($table)->where('id', $id)->update([$column => $newPath]);
        }
    }

    private function convertAndReplaceReturnPath($oldPath, $folder)
    {
        if (empty($oldPath)) return $oldPath;

        $extension = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));
        // If it's already webp, skip
        if ($extension === 'webp' || $extension === 'svg') return $oldPath;

        $disk = Storage::disk('r2');
        if (!$disk->exists($oldPath)) return $oldPath;

        try {
            // 1. Download image content to memory
            $imageContent = $disk->get($oldPath);
            if (!$imageContent) return $oldPath;

            // 2. Decode and encode to WebP
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->decode($imageContent);
            $encoded = $image->encode(new \Intervention\Image\Encoders\WebpEncoder(85));

            // 3. Prepare new path
            $fileNameWithoutExt = pathinfo($oldPath, PATHINFO_FILENAME);
            $newFileName = $fileNameWithoutExt . '.webp';
            $newPath = $folder . '/' . $newFileName;

            // 4. Upload new WebP to R2
            $disk->put($newPath, (string) $encoded, [
                'CacheControl' => 'public, max-age=31536000, immutable'
            ]);

            // 5. Delete old JPG/PNG
            $disk->delete($oldPath);

            $this->info("Converted: {$oldPath} -> {$newPath}");
            return $newPath;

        } catch (\Exception $e) {
            $this->error("Failed to convert {$oldPath}: " . $e->getMessage());
            return $oldPath;
        }
    }
}
