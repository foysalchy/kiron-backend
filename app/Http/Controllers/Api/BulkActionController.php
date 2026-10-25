<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\FileUploadHelper;
use App\Helpers\LogHelper;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BulkActionController extends Controller
{
    private array $resourceMap = [
        'parties'          => \App\Models\Party::class,
        'users'            => \App\Models\User::class,
        'products'         => \App\Models\Product::class,
        'mega-categories'  => \App\Models\MegaCategory::class,
        'sub-categories'   => \App\Models\SubCategory::class,
        'mini-categories'  => \App\Models\MiniCategory::class,
        'extra-categories' => \App\Models\ExtraCategory::class,
        'attribute-values' => \App\Models\AttributeValue::class,
        'attribute-groups' => \App\Models\AttributeGroup::class,
        'warehouses'       => \App\Models\Warehouse::class,
        'areas'            => \App\Models\Area::class,
        'cells'            => \App\Models\Cell::class,
        'racks'            => \App\Models\Rack::class,
        'bins'             => \App\Models\Bin::class,
        'sliders'          => \App\Models\Slider::class,
        'pages'            => \App\Models\Page::class,
        'blogs'            => \App\Models\Blog::class,
        'coupons'          => \App\Models\Coupon::class,
        'taxgroups'        => \App\Models\TaxGroup::class,
        'taxrates'         => \App\Models\TaxRate::class,
        'lead-sources'     => \App\Models\LeadSource::class,
        'lead-statuses'    => \App\Models\LeadStatus::class,
        'leads'            => \App\Models\Lead::class,
        'note-templates'   => \App\Models\NoteTemplate::class,
        'sms-templates'    => \App\Models\SmsTemplate::class,
        'email-templates'  => \App\Models\EmailTemplate::class,
        'departments'      => \App\Models\Department::class,
        'jobs'             => \App\Models\JobTitle::class,
        'employee-types'   => \App\Models\EmployeeType::class,
        'office-locations' => \App\Models\OfficeLocation::class,
        'employees'        => \App\Models\Employee::class,
        'holidays'         => \App\Models\Holiday::class,
        'leave-types'      => \App\Models\LeaveType::class,
        'assign-leaves'    => \App\Models\AssignLeaveType::class,
        'pay-rolls'        => \App\Models\PayRoll::class,
        'pay-heads'        => \App\Models\PayHead::class,
        'positions'        => \App\Models\Position::class,
        'pay-roll-pay-heads'        => \App\Models\PayRollPayHead::class,
        'pricing-packages'        => \App\Models\PricingPackage::class,
        'landings'        => \App\Models\LandingPage::class,
        'resign-rules'        => \App\Models\ResignRule::class,
        'support-departments'        => \App\Models\SupportDepartment::class,
        'support-tickets'        => \App\Models\SupportTicket::class,
        'kb'        => \App\Models\KnowledgeBase::class,
        'asset-categories'        => \App\Models\AssetCategory::class,
        'assets'        => \App\Models\Asset::class,
        'brands'        => \App\Models\Brand::class,
        'asset-purchases'        => \App\Models\AssetPurchase::class,
        'disposal-types'        => \App\Models\DisposalType::class,
        'customer-groups'       => \App\Models\CustomerGroup::class,
        'asset-disposals'        => \App\Models\AssetDisposal::class,
        'master-brands'        => \App\Models\MasterBrand::class,
        'master-demos'        => \App\Models\MasterDemo::class,
        'master-features'        => \App\Models\MasterFeature::class,
        'account-groups'        => \App\Models\AccountGroup::class,
        'account-expenses'        => \App\Models\TransactionExpense::class,
        'account-incomes'        => \App\Models\TransactionIncome::class,
        'product-groups'        => \App\Models\ProductGroup::class,
        'notices'        => \App\Models\Notice::class,
    ];

    /**
     * Resolve and return the model class for the given resource key.
     */
    private function resolveModel(string $resource): string
    {
        if (!isset($this->resourceMap[$resource])) {
            abort(404, "Resource '{$resource}' not found.");
        }

        return $this->resourceMap[$resource];
    }

    /**
     * Guard against calling SoftDelete-only operations on models
     * that don't use the SoftDeletes trait.
     */
    private function assertSoftDeletable(string $model, string $resource): void
    {
        if (!in_array(SoftDeletes::class, class_uses_recursive($model))) {
            abort(422, "Resource '{$resource}' does not support soft deletes.");
        }
    }

    /**
     * Get the current authenticated user's company_id (if any).
     */
    private function currentCompanyId(): ?int
    {
        return Auth::user()->company_id ?? null;
    }

    /**
     * Bulk update status for the given resource.
     */
    public function updateStatus(Request $request, string $resource)
    {
        $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer'],
            'status' => ['required', 'in:0,1,39'],
        ]);

        $model = $this->resolveModel($resource);
        $actionType = class_basename($model);
        $companyId = $this->currentCompanyId();

        $records = $model::whereIn('id', $request->ids)->get();

        $updated = $model::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        $this->clearModelCaches($model, $companyId, $records);

        foreach ($request->ids as $id) {
            LogHelper::statusChanged($resource, $id, $companyId, $actionType);
        }

        return response()->json([
            'message' => 'Status updated successfully.',
            'updated' => $updated,
        ]);
    }

    /**
     * Bulk soft-delete records for the given resource.
     */
    public function bulkDelete(Request $request, string $resource)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        $model = $this->resolveModel($resource);
        $actionType = class_basename($model);
        $companyId = $this->currentCompanyId();

        $records = $model::whereIn('id', $request->ids)->get();

        $deleted = $model::whereIn('id', $request->ids)->delete();

        $this->clearModelCaches($model, $companyId, $records);

        foreach ($request->ids as $id) {
            LogHelper::deleted($resource, $id, $companyId, $actionType);
        }

        return response()->json([
            'message' => 'Deleted successfully.',
            'deleted' => $deleted,
        ]);
    }

    /**
     * Bulk permanently delete (force delete) soft-deleted records.
     * Only works on models using the SoftDeletes trait.
     */
    public function bulkForceDelete(Request $request, string $resource)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        $model = $this->resolveModel($resource);
        $this->assertSoftDeletable($model, $resource);
        $actionType = class_basename($model);
        $companyId = $this->currentCompanyId();

        $records = $model::withTrashed()
            ->whereIn('id', $request->ids)
            ->get();

        if ($records->isEmpty()) {
            return response()->json([
                'message' => 'No records found to delete.',
                'deleted' => 0,
                'skipped' => 0,
            ]);
        }

        // ── 1. Happy Path: Try fast single batch delete first ──
        try {
            $deleted = $model::withTrashed()
                ->whereIn('id', $request->ids)
                ->forceDelete();

            $this->deleteResourceFiles($resource, $records);
            $this->clearModelCaches($model, $companyId, $records);

            foreach ($request->ids as $id) {
                LogHelper::forceDeleted($resource, $id, $companyId, $actionType);
            }

            return response()->json([
                'message' => 'Permanently deleted successfully.',
                'deleted' => $deleted,
                'skipped' => 0,
            ]);
        } catch (QueryException $e) {
            // Check if it's a Foreign Key Constraint error (1451 / 23000)
            if ($e->getCode() != 23000 && !str_contains($e->getMessage(), '1451')) {
                throw $e;
            }

            // ── 2. Fallback Path: Identify which records can be safely deleted ──
            $deletedRecords = collect();
            $skippedCount = 0;

            foreach ($records as $record) {
                try {
                    // Test single delete in savepoint
                    $record->forceDelete();
                    $deletedRecords->push($record);
                } catch (QueryException $subException) {
                    $skippedCount++;
                }
            }

            $deletedCount = $deletedRecords->count();

            if ($deletedCount > 0) {
                $this->deleteResourceFiles($resource, $deletedRecords);
                $this->clearModelCaches($model, $companyId, $deletedRecords);

                foreach ($deletedRecords as $delRecord) {
                    LogHelper::forceDeleted($resource, $delRecord->id, $companyId, $actionType);
                }
            }

            $msg = $deletedCount > 0
                ? "{$deletedCount} record(s) permanently deleted. {$skippedCount} record(s) skipped because they are in use."
                : "Cannot delete selected record(s) because they are in use in orders or transactions.";

            return response()->json([
                'message' => $msg,
                'deleted' => $deletedCount,
                'skipped' => $skippedCount,
            ]);
        }
    }

    /**
     * Bulk restore soft-deleted records.
     * Only works on models using the SoftDeletes trait.
     */
    public function bulkRestore(Request $request, string $resource)
    {
        $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        $model = $this->resolveModel($resource);
        $this->assertSoftDeletable($model, $resource);
        $actionType = class_basename($model);
        $companyId = $this->currentCompanyId();

        $records = $model::onlyTrashed()
            ->whereIn('id', $request->ids)
            ->get();

        // onlyTrashed() ensures we only touch soft-deleted rows.
        $restored = $model::onlyTrashed()
            ->whereIn('id', $request->ids)
            ->restore();

        $this->clearModelCaches($model, $companyId, $records);

        foreach ($request->ids as $id) {
            LogHelper::restored($resource, $id, $companyId, $actionType);
        }

        return response()->json([
            'message'  => 'Restored successfully.',
            'restored' => $restored,
        ]);
    }

    /**
     * Delete all associated files from R2 storage before permanently deleting records.
     */
    private function deleteResourceFiles(string $resource, $records): void
    {
        if ($records->isEmpty()) {
            return;
        }

        switch ($resource) {
            case 'landings':
                foreach ($records as $landingPage) {
                    // 1. Thumbnail (avoid deleting if shared with existing product)
                    if (!empty($landingPage->thumbnail)) {
                        $isProductThumb = false;
                        if (!empty($landingPage->product_id)) {
                            $product = \App\Models\Product::find($landingPage->product_id);
                            if ($product && $product->thumbnail === $landingPage->thumbnail) {
                                $isProductThumb = true;
                            }
                        }
                        if (!$isProductThumb) {
                            $this->deleteFile($landingPage->thumbnail);
                        }
                    }

                    // 2. Video
                    if (!empty($landingPage->video)) {
                        $this->deleteFile($landingPage->video);
                    }

                    // 3. Review images (img_paths)
                    $imgPaths = is_string($landingPage->img_paths)
                        ? json_decode($landingPage->img_paths, true)
                        : $landingPage->img_paths;
                    if (is_array($imgPaths)) {
                        foreach ($imgPaths as $path) {
                            $this->deleteFile($path);
                        }
                    }

                    // 4. Extras card images
                    $extras = is_string($landingPage->extras)
                        ? json_decode($landingPage->extras, true)
                        : $landingPage->extras;
                    if (is_array($extras)) {
                        foreach ($extras as $fieldValue) {
                            if (is_array($fieldValue)) {
                                foreach ($fieldValue as $card) {
                                    if (is_array($card) && !empty($card['image']) && is_string($card['image'])) {
                                        $this->deleteFile($card['image']);
                                    }
                                }
                            }
                        }
                    }

                    // 5. Meta image
                    if (!empty($landingPage->meta_image) && is_string($landingPage->meta_image)) {
                        $this->deleteFile($landingPage->meta_image);
                    }
                }
                break;

            case 'products':
                $productIds = $records->pluck('id')->all();

                foreach ($records as $product) {
                    $this->deleteFile($product->thumbnail);
                    $this->deleteFile($product->thumbnail_310);
                    $this->deleteFile($product->thumbnail_95);
                }

                // Delete galleries for these products
                $galleries = \App\Models\Gallery::whereIn('product_id', $productIds)->get();
                foreach ($galleries as $gallery) {
                    $this->deleteFile($gallery->image);
                }

                // Delete variation images & variation galleries
                $variations = \App\Models\ProductVariation::whereIn('product_id', $productIds)->get();
                if ($variations->isNotEmpty()) {
                    $variationIds = $variations->pluck('id')->all();
                    foreach ($variations as $variation) {
                        $this->deleteFile($variation->image);
                    }

                    $varGalleries = \App\Models\VariationGallery::whereIn('variation_id', $variationIds)->get();
                    foreach ($varGalleries as $vg) {
                        $this->deleteFile($vg->image);
                    }
                }
                break;

            case 'blogs':
                foreach ($records as $blog) {
                    $images = is_string($blog->images)
                        ? json_decode($blog->images, true)
                        : $blog->images;
                    if (is_array($images)) {
                        foreach ($images as $img) {
                            $this->deleteFile($img);
                        }
                    } elseif (is_string($images)) {
                        $this->deleteFile($images);
                    }
                }
                break;

            default:
                // Generic handler for all other resources with file/image columns
                $fileColumns = [
                    'image',
                    'logo',
                    'icon',
                    'thumbnail',
                    'file',
                    'avatar',
                    'nid_image',
                    'letter',
                    'appointment_letter',
                    'documents',
                    'profile',
                ];

                foreach ($records as $record) {
                    foreach ($fileColumns as $column) {
                        if (!isset($record->{$column})) {
                            continue;
                        }

                        $value = $record->{$column};
                        if (empty($value)) {
                            continue;
                        }

                        if (is_string($value)) {
                            // Check if it might be a JSON encoded array of file paths
                            if (str_starts_with(trim($value), '[') || str_starts_with(trim($value), '{')) {
                                $decoded = json_decode($value, true);
                                if (is_array($decoded)) {
                                    foreach ($decoded as $item) {
                                        if (is_string($item)) {
                                            $this->deleteFile($item);
                                        }
                                    }
                                    continue;
                                }
                            }
                            $this->deleteFile($value);
                        } elseif (is_array($value)) {
                            foreach ($value as $item) {
                                if (is_string($item)) {
                                    $this->deleteFile($item);
                                }
                            }
                        }
                    }
                }
                break;
        }
    }

    /**
     * Delete a single file path safely from R2 storage.
     */
    private function deleteFile(?string $path): void
    {
        if (empty($path) || !is_string($path)) {
            return;
        }

        if (str_starts_with($path, 'data:') || str_starts_with($path, 'blob:') || str_starts_with($path, '__file__')) {
            return;
        }

        // If it's a full URL, strip scheme and host to extract the relative storage path
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsedPath = parse_url($path, PHP_URL_PATH);
            if ($parsedPath) {
                $path = ltrim($parsedPath, '/');
            }
        }

        FileUploadHelper::delete($path);
    }

    /**
     * Clear all associated model caches across all cache traits for bulk operations.
     */
    private function clearModelCaches(string $model, ?int $companyId, $records = null): void
    {
        // 1. HasHomepageCache (Notice, Slider, Product, Category, etc.)
        if (method_exists($model, 'clearHomepageCache')) {
            $model::clearHomepageCache($companyId);
        }

        // 2. HasCachedOptions (Category, Brand, Attribute, Dropdowns)
        if (method_exists($model, 'clearOptionsCache')) {
            $model::clearOptionsCache($companyId);
        }

        // 3. HasGlobalLayoutCache (Header, Footer, Menu, Site Settings)
        if (method_exists($model, 'clearGlobalLayoutCache') && method_exists($model, 'globalLayoutSections')) {
            foreach ($model::globalLayoutSections() as $section) {
                $model::clearGlobalLayoutCache($companyId, $section);
            }
        }

        // 4. HasSaasCache (SaaS keys)
        if (is_null($companyId) && method_exists($model, 'clearSaasCache') && method_exists($model, 'saasCacheKeys')) {
            foreach ($model::saasCacheKeys() as $key) {
                $model::clearSaasCache($key);
            }
        }

        // 5. Slug-based caches (Page, Blog, Notice, etc.)
        if ($records) {
            foreach ($records as $record) {
                if (!empty($record->slug)) {
                    Cache::forget("notice_details_{$companyId}_{$record->slug}");
                    Cache::forget("page_content_{$companyId}_{$record->slug}");
                    Cache::forget("blog_single_{$companyId}_{$record->slug}");
                    Cache::forget("slug_type_{$record->slug}");
                }
            }
        }
    }
}
