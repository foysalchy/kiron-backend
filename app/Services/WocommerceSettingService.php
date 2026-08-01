<?php

namespace App\Services;

use App\Enums\Status;
use App\Exceptions\ApiException;
use App\Helpers\LogHelper;
use App\Models\WocommerceSetting;
use App\Models\WooCommerceIntegration;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
class WocommerceSettingService
{
    /**
     * Get all WooCommerce settings with optional pagination
     */
    public function getAllSettings(array $filters = [], bool $paginate = true): Collection|LengthAwarePaginator
    {
        try {
            $query = WocommerceSetting::query();

            // Status filter (Handling Trashed logic like Period)
            if (isset($filters['status'])) {
                if ($filters['status'] == Status::Trashed->value) {
                    $query->onlyTrashed();
                } else {
                    $query->where('status', $filters['status']);
                }
            }

            // Search by Domain URL
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('domain_url', 'like', "%{$search}%");
                });
            }

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            return $paginate
                ? $query->paginate($filters['per_page'] ?? 15)
                : $query->get();

        } catch (\Throwable $e) {
            Log::error('Error fetching WooCommerce settings: ' . $e->getMessage());
            throw ApiException::serverError('Failed to fetch settings');
        }
    }

    /**
     * Get setting by ID
     */
    public function getSettingById(int $id): WocommerceSetting
    {
        $setting = WocommerceSetting::find($id);
        if (!$setting) {
            throw ApiException::notFound('WooCommerce Setting');
        }
        return $setting;
    }

    /**
     * Create a new WooCommerce setting
     */
    public function createSetting(array $data): WocommerceSetting
    {
        DB::beginTransaction();

        $existingSetting = WocommerceSetting::where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($data) {
                $query->where('consumer_key', $data['consumer_key'])
                    ->orWhere('consumer_secret', $data['consumer_secret']);
            })
            ->exists();

            if ($existingSetting) {
                throw ApiException::badRequest(
                    'A WooCommerce setting with this consumer key or secret already exists.'
                );
            }
        try {

            $storeInfo = $this->fetchStoreInfo($data);

            $data['name'] = $storeInfo['name'];
            $data['logo'] = $storeInfo['logo'];

            $setting = WocommerceSetting::create($data);

            LogHelper::created(
                'woocommerce_setting',
                $setting->id,
                $setting->company_id,
                $setting->domain_url
            );

            DB::commit();

            return $setting;

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('WooCommerce setting creation failed', [
                'message' => $e->getMessage(),
            ]);

            throw ApiException::serverError(
                $e->getMessage()
            );
        }
    }

    private function fetchStoreInfo(array $data): array
    {
        $domain = trim($data['domain_url']);

        // Add https:// if missing
        if (!preg_match('/^https?:\/\//i', $domain)) {
            $domain = 'https://' . $domain;
        }

        $domain = rtrim($domain, '/');

        // Validate WooCommerce credentials
        $wcResponse = Http::timeout(15)
            ->withBasicAuth(
                $data['consumer_key'],
                $data['consumer_secret']
            )
            ->get($domain . '/wp-json/wc/v3/products', [
                'per_page' => 1,
            ]);

        if (!$wcResponse->successful()) {

            $message = $wcResponse->json()['message'] ?? 'Invalid WooCommerce credentials.';

            throw new \Exception($message);
        }

        // Fetch WordPress information
        $wpResponse = Http::timeout(15)
            ->get($domain . '/wp-json');

        if (!$wpResponse->successful()) {
            throw new \Exception('Unable to fetch store information.');
        }

        $wp = $wpResponse->json();

        $name = $wp['name'] ?? 'WooCommerce Store';

        // Default logo
        $logo = $domain . '/favicon.ico';

        // WordPress Site Icon
        if (!empty($wp['site_icon_url'])) {
            $logo = $wp['site_icon_url'];
        }

        return [
            'name' => $name,
            'logo' => $logo,
        ];
    }

    /**
     * Update WooCommerce setting
     */
    public function updateSetting(int $id, array $data): WocommerceSetting
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSettingById($id);
            $setting->update($data);

            LogHelper::updated('woocommerce_setting', $setting->id, $setting->company_id, $setting->domain_url);

            DB::commit();
            return $setting->fresh();
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce setting update failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to update setting');
        }
    }

    /**
     * Delete setting (soft delete)
     */
    public function deleteSetting(int $id): bool
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSettingById($id);
            $setting->delete();

            LogHelper::deleted('woocommerce_setting', $setting->id, $setting->company_id, $setting->domain_url);
            DB::commit();
            return true;
        } catch (ApiException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce setting deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to delete setting');
        }
    }

    /**
     * Restore soft deleted WooCommerce setting
     */
    public function restoreSetting(int $id): WocommerceSetting
    {
        DB::beginTransaction();
        try {
            $setting = WocommerceSetting::withTrashed()->find($id);

            if (!$setting) {
                throw ApiException::notFound('WooCommerce Setting');
            }

            $setting->restore();

            LogHelper::restored('woocommerce_setting', $setting->id, $setting->company_id, $setting->domain_url);

            DB::commit();
            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce setting restore failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to restore setting');
        }
    }

    /**
     * Permanently delete a WooCommerce setting
     */
    public function forceDeleteSetting(int $id): bool
    {
        DB::beginTransaction();
        try {
            $setting = WocommerceSetting::withTrashed()->find($id);

            if (!$setting) {
                throw ApiException::notFound('WooCommerce Setting');
            }

            $setting->forceDelete();

            LogHelper::forceDeleted('woocommerce_setting', $id, $setting->company_id, $setting->domain_url);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce setting permanent deletion failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to permanently delete setting');
        }
    }

    /**
     * Toggle status (Active/Inactive)
     */
    public function toggleStatus(int $id): WocommerceSetting
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSettingById($id);

            $currentStatus = Status::from($setting->status);
            $newStatus = $currentStatus === Status::Active
                ? Status::Inactive
                : Status::Active;

            $setting->update(['status' => $newStatus->value]);

            LogHelper::statusChanged(
                'woocommerce_setting',
                $setting->id,
                $setting->company_id,
                $setting->domain_url . ' new status ' . $newStatus->label()
            );

            DB::commit();
            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce status toggle failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to toggle status');
        }
    }
     /**
     * Toggle Sync (Active/Inactive)
     */
    public function toggleSync(int $id): WocommerceSetting
    {
        DB::beginTransaction();
        try {
            $setting = $this->getSettingById($id);

     
            $newStatus = $setting->sync === 1
                ? 0
                : 1;

            $setting->update(['sync' => $newStatus]);

            LogHelper::statusChanged(
                'woocommerce_setting',
                $setting->id,
                $setting->company_id,
                $setting->domain_url . ' new sync ' . $newStatus
            );

            DB::commit();
            return $setting;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('WooCommerce status sync failed: ' . $e->getMessage());
            throw ApiException::serverError('Failed to sync status');
        }
    }
    public function importProducts(int $id, Request $request): array
    {
        $setting = WocommerceSetting::find($id);

        if (!$setting) {
            throw ApiException::notFound('WooCommerce setting not found.');
        }

        $domain = trim($setting->domain_url);

        if (!preg_match('/^https?:\/\//i', $domain)) {
            $domain = 'https://' . $domain;
        }

        $domain = rtrim($domain, '/');

        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 50);

        $response = Http::timeout(30)
            ->withBasicAuth(
                $setting->consumer_key,
                $setting->consumer_secret
            )
                ->get($domain . '/wp-json/wc/v3/products', [
                    'page' => $page,
                    'per_page' => $perPage,
                ]);

        if (!$response->successful()) {
            throw ApiException::serverError(
                $response->json()['message'] ?? 'Unable to fetch WooCommerce products.'
            );
        }
        $products = $response->json();
        foreach ($products as &$product) {
            // Variable product হলে variations fetch করুন
            if (($product['type'] ?? '') === 'variable') {
                $variationResponse = Http::timeout(30)
                    ->withBasicAuth(
                        $setting->consumer_key,
                        $setting->consumer_secret
                    )
                    ->get($domain . "/wp-json/wc/v3/products/{$product['id']}/variations");
                if ($variationResponse->successful()) {
                    $product['variations'] = $variationResponse->json();
                    // সব variation-এর stock যোগ করে parent stock বানানো (optional)
                    $product['total_stock'] = collect($product['variations'])
                        ->sum(function ($variation) {
                            return $variation['stock_quantity'] ?? 0;
                        });
                } else {
                    $product['variations'] = [];
                    $product['total_stock'] = 0;
                }
            } else {
                // Simple product
                $product['variations'] = [];
                $product['total_stock'] = $product['stock_quantity'] ?? 0;
            }
        }
        return [
            'current_page' => $page,
            'last_page' => (int) $response->header('X-WP-TotalPages'),
            'per_page' => $perPage,
            'total' => (int) $response->header('X-WP-Total'),
            'data' => $products,
        ];
    }

    // import product to woocommerce in our db ~ pending task
    public function importProduct(Request $request) :array
    {
        $request->validate([
            'woo_product_id'       => 'required|integer',
            'mega_category_ids'    => 'required|array',
            'mega_category_ids.*'  => 'integer',
            'sub_category_ids'     => 'nullable|array',
            'mini_category_ids'    => 'nullable|array',
            'extra_category_ids'   => 'nullable|array',
            'warehouse_info'       => 'required|array',
            'warehouse_info.*.warehouse_id' => 'required',
            'warehouse_info.*.quantity'     => 'nullable',
        ]);
        $setting = WocommerceSetting::where('company_id',auth()->user()->company_id)->first();
        if (!$setting) {
            throw ApiException::notFound('WooCommerce setting not found.');
        }
        $domain = trim($setting->domain_url);
        if (!preg_match('/^https?:\/\//i', $domain)) {
            $domain = 'https://' . $domain;
        }
       

        // 1. Get WooCommerce credentials from your settings / config
        $storeUrl = rtrim($domain, '/');
        $consumerKey    =  $setting->consumer_secret;
        $consumerSecret = config('services.woocommerce.secret');

        // 2. Fetch full product from WooCommerce
        $response = Http::withBasicAuth($consumerKey, $consumerSecret)
            ->get("{$storeUrl}/wp-json/wc/v3/products/{$request->woo_product_id}");

        if (!$response->successful()) {
            return response()->json(['message' => 'Failed to fetch product from WooCommerce'], 400);
        }

        $woo = $response->json();

        // 3. Prepare product data
        $isVariable = $woo['type'] === 'variable';

        $productData = [
            'title'              => $woo['name'],
            'slug'               => $this->makeSlug($woo['slug'] ?? $woo['name']),
            'type'               => $isVariable ? 'variation' : 'single',
            'sku_code'           => $woo['sku'] ?? null,
            'short_description'  => $woo['short_description'] ?? null,
            'full_description'   => $woo['description'] ?? null,
            'regular_price'      => $isVariable ? 0 : ($woo['regular_price'] ?: $woo['price'] ?: 0),
            'purchase_price'     => 0,
            'discount_type'      => 'flat',
            'discount'           => 0,
            'mega_category_ids'  => $request->mega_category_ids,
            'sub_category_ids'   => $request->sub_category_ids ?? [],
            'mini_category_ids'  => $request->mini_category_ids ?? [],
            'extra_category_ids' => $request->extra_category_ids ?? [],
        ];

        // Calculate discount for simple product
        if (!$isVariable) {
            $regular = (float) ($woo['regular_price'] ?: $woo['price'] ?: 0);
            $sale    = (float) ($woo['sale_price'] ?: 0);
            if ($sale > 0 && $regular > $sale) {
                $productData['discount'] = $regular - $sale;
            }
        }

        // 4. Download thumbnail
        if (!empty($woo['images'][0]['src'])) {
            $productData['thumbnail'] = $this->downloadImage($woo['images'][0]['src'], 'products');
        }

        // 5. Create product (adjust to your actual create logic)
        $product = Product::create($productData); // or your service

        // Attach categories if you use relationships
        // $product->megaCategories()->sync($request->mega_category_ids);
        // ...

        // 6. Gallery images
        if (!empty($woo['images']) && count($woo['images']) > 1) {
            foreach (array_slice($woo['images'], 1) as $img) {
                $path = $this->downloadImage($img['src'], 'products/gallery');
                if ($path) {
                    // $product->gallery()->create(['image' => $path]);
                }
            }
        }

        // 7. Warehouse / Stock
        $qty = $this->getQty($woo);

        foreach ($request->warehouse_info as $wh) {
            // $product->warehouses()->create([
            //     'warehouse_id' => $wh['warehouse_id'],
            //     'bin_id'       => $wh['bin_id'] ?? null,
            //     'quantity'     => $wh['quantity'] ?: $qty,
            // ]);
        }

        // 8. Variations
        if ($isVariable && !empty($woo['variations'])) {
            // Note: WooCommerce /products/{id} sometimes only returns variation IDs.
            // If you only get IDs, you need an extra request for each variation.
            // In your earlier JSON the full variations were already embedded.

            foreach ($woo['variations'] as $index => $variation) {
                // If $variation is just an ID, fetch it:
                if (is_numeric($variation)) {
                    $varRes = Http::withBasicAuth($consumerKey, $consumerSecret)
                        ->get("{$storeUrl}/wp-json/wc/v3/products/{$woo['id']}/variations/{$variation}");
                    if (!$varRes->successful()) continue;
                    $variation = $varRes->json();
                }

                $regular = (float) ($variation['regular_price'] ?: $variation['price'] ?: 0);
                $sale    = (float) ($variation['sale_price'] ?: 0);
                $discount = ($sale > 0 && $regular > $sale) ? $regular - $sale : 0;

                $varData = [
                    'regular_price'  => $regular,
                    'purchase_price' => 0,
                    'discount_type'  => 'flat',
                    'discount'       => $discount,
                    'sku'            => $variation['sku'] ?? null,
                ];

                // Variation image
                if (!empty($variation['image']['src'])) {
                    $varData['image'] = $this->downloadImage($variation['image']['src'], 'products/variations');
                }

                // Create variation
                // $newVariation = $product->variations()->create($varData);

                // Warehouse for variation
                $varQty = $this->getQty($variation);
                foreach ($request->warehouse_info as $wh) {
                    // $newVariation->warehouses()->create([
                    //     'warehouse_id' => $wh['warehouse_id'],
                    //     'bin_id'       => $wh['bin_id'] ?? null,
                    //     'quantity'     => $wh['quantity'] ?: $varQty,
                    // ]);
                }

                // Attributes mapping can be added later
            }
        }

        return response()->json([
            'message' => 'Product imported successfully',
            'product' => $product,
        ]);
    }

    private function getQty(array $item): int
    {
        return ($item['stock_status'] ?? '') === 'instock' ? 99999 : (int) ($item['stock_quantity'] ?? 0);
    }

    private function downloadImage(string $url, string $folder = 'products'): ?string
    {
        try {
            $response = Http::timeout(20)->get($url);
            if (!$response->successful()) return null;

            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename  = $folder . '/' . Str::uuid() . '.' . $extension;

            Storage::disk('public')->put($filename, $response->body());

            return $filename;
        } catch (\Exception $e) {
            \Log::error('Image download failed: ' . $url . ' - ' . $e->getMessage());
            return null;
        }
    }

    private function makeSlug(string $text): string
    {
        $text = urldecode($text);
        $text = preg_replace('/[^a-z0-9\x{0980}-\x{09FF}]+/u', '-', strtolower($text));
        return trim($text, '-') ?: 'product-' . time();
    }
}
