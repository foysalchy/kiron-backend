<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RenameLegacyImagesCommand extends Command
{
    protected $signature = 'dorja:rename-images';
    protected $description = 'Rename legacy images to SEO friendly names on R2 storage';

    public function handle()
    {
        $this->info("Starting legacy images rename process (SEO Friendly)...");

        // 1. Products
        $this->info("Processing Products...");
        $products = DB::table('products')->whereNotNull('thumbnail')->get();
        foreach ($products as $product) {
            $this->renameFile('products', 'thumbnail', $product->id, $product->thumbnail, $product->slug ?? $product->name ?? 'product', 'products/images');

            // Handle galleries (JSON array)
            if (!empty($product->galleries)) {
                $galleries = json_decode($product->galleries, true);
                if (is_array($galleries)) {
                    $newGalleries = [];
                    foreach ($galleries as $index => $oldPath) {
                        $newPath = $this->renameFileReturnPath($oldPath, $product->slug ?? $product->name ?? 'product', 'products/galleries', $index + 1);
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
            $product = DB::table('products')->where('id', $variation->product_id)->first();
            $baseName = ($product->slug ?? 'product') . '-variant-' . $variation->id;
            $this->renameFile('product_variations', 'image', $variation->id, $variation->image, $baseName, 'products/variations');
        }

        // 1.6 Variation Galleries
        $this->info("Processing Variation Galleries...");
        $variationGalleries = DB::table('variation_galleries')->whereNotNull('image')->get();
        foreach ($variationGalleries as $gallery) {
            $variation = DB::table('product_variations')->where('id', $gallery->variation_id)->first();
            $product = $variation ? DB::table('products')->where('id', $variation->product_id)->first() : null;
            $baseName = ($product->slug ?? 'product') . '-variant-gallery-' . ($variation->id ?? $gallery->id);
            $this->renameFile('variation_galleries', 'image', $gallery->id, $gallery->image, $baseName, 'products/variation_galleries');
        }

        // 2. Categories
        $this->info("Processing Categories...");
        $this->processSimpleTable('mega_categories', 'image', 'name', 'categories/images', 'category');
        $this->processSimpleTable('sub_categories', 'image', 'name', 'categories/sub_images', 'sub-category');
        $this->processSimpleTable('mini_categories', 'image', 'name', 'categories/mini_images', 'mini-category');
        $this->processSimpleTable('extra_categories', 'image', 'name', 'categories/extra_images', 'extra-category');

        // 3. Sliders
        $this->info("Processing Sliders...");
        $this->processSimpleTable('sliders', 'image', 'title', 'sliders/images', 'slider');

        // 4. Pages
        $this->info("Processing Pages...");
        $this->processSimpleTable('pages', 'image', 'title', 'pages', 'page');

        // 5. Blogs
        $this->info("Processing Blogs...");
        $blogs = DB::table('blogs')->whereNotNull('images')->get();
        foreach ($blogs as $blog) {
            if (!empty($blog->images)) {
                $images = json_decode($blog->images, true);
                if (is_array($images)) {
                    $newImages = [];
                    foreach ($images as $index => $oldPath) {
                        $newPath = $this->renameFileReturnPath($oldPath, $blog->title ?? 'blog', 'blogs/images', $index + 1);
                        $newImages[] = $newPath;
                    }
                    DB::table('blogs')->where('id', $blog->id)->update(['images' => json_encode($newImages)]);
                }
            }
        }

        // 6. Site Settings
        $this->info("Processing Site Settings...");
        $settings = DB::table('site_settings')->get();
        foreach ($settings as $setting) {
            $shopName = $setting->shop_name ?? 'kiron';
            $this->renameFile('site_settings', 'logo', $setting->id, $setting->logo, $shopName . '_logo', 'site-settings/logos');
            $this->renameFile('site_settings', 'favicon', $setting->id, $setting->favicon, $shopName . '_favicon', 'site-settings/favicons');
            $this->renameFile('site_settings', 'meta_image', $setting->id, $setting->meta_image, $shopName . '_meta', 'site-settings/meta-images');
        }

        $this->info("Renaming completed successfully.");
    }

    private function processSimpleTable($table, $column, $nameColumn, $folder, $fallbackName)
    {
        $records = DB::table($table)->whereNotNull($column)->get();
        foreach ($records as $record) {
            $baseName = $record->$nameColumn ?? $fallbackName;
            $this->renameFile($table, $column, $record->id, $record->$column, $baseName, $folder);
        }
    }

    private function renameFileReturnPath($oldPath, $baseName, $folder, $index = '')
    {
        if (empty($oldPath)) return $oldPath;

        $disk = Storage::disk('r2');
        if (!$disk->exists($oldPath)) return $oldPath;

        $extension = pathinfo($oldPath, PATHINFO_EXTENSION) ?: 'jpg';
        $suffix = $index ? "_{$index}_" : '_';
        $newFileName = Str::slug($baseName) . $suffix . time() . '.' . $extension;
        $newPath = $folder . '/' . $newFileName;

        try {
            // Get S3 Client to perform a server-side copy with Cache-Control
            $client = $disk->getClient();
            $bucket = config('filesystems.disks.r2.bucket');

            $client->copyObject([
                'Bucket' => $bucket,
                'Key' => $newPath,
                'CopySource' => $bucket . '/' . $oldPath,
                'MetadataDirective' => 'REPLACE',
                'CacheControl' => 'public, max-age=31536000, immutable',
                'ContentType' => $disk->mimeType($oldPath) ?: 'image/jpeg',
            ]);

            $disk->delete($oldPath);
            $this->info("Renamed & Cached: {$oldPath} -> {$newPath}");
            return $newPath;
        } catch (\Exception $e) {
            $this->error("Failed to rename {$oldPath}: " . $e->getMessage());
            return $oldPath; // Return old path if failed
        }
    }

    private function renameFile($table, $column, $id, $oldPath, $baseName, $folder)
    {
        if (empty($oldPath)) return;

        $newPath = $this->renameFileReturnPath($oldPath, $baseName, $folder);
        if ($newPath !== $oldPath) {
            DB::table($table)->where('id', $id)->update([$column => $newPath]);
        }
    }
}
