<?php

namespace App\Http\Controllers\Api;

use App\Exports\ProductTemplateExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Brand;
use App\Models\MegaCategory;
use App\Models\SubCategory;
use App\Models\MiniCategory;
use App\Models\ExtraCategory;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Filter;
use App\Models\FilterValue;
use App\Helpers\FileUploadHelper;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Auth;

class ProductImportController extends Controller
{
    /**
     * Download the blank template for product import.
     */
    public function downloadTemplate(Request $request)
    {
        try {
            $type = $request->query('type', 'xlsx');
            
            $fileName = 'product_import_template.' . $type;

            if ($type === 'csv') {
                return Excel::download(new ProductTemplateExport, $fileName, \Maatwebsite\Excel\Excel::CSV);
            }

            return Excel::download(new ProductTemplateExport, $fileName, \Maatwebsite\Excel\Excel::XLSX);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export existing products.
     */
    public function exportData(Request $request)
    {
        try {
            $type = $request->query('type', 'xlsx');
            $fileName = 'products_data_' . date('Y_m_d_His') . '.' . $type;

            if ($type === 'csv') {
                return Excel::download(new \App\Exports\ProductDataExport, $fileName, \Maatwebsite\Excel\Excel::CSV);
            }

            return Excel::download(new \App\Exports\ProductDataExport, $fileName, \Maatwebsite\Excel\Excel::XLSX);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import products from uploaded file.
     */
    public function importData(Request $request)
    {
        if (!$request->has('rows')) {
            $request->validate([
                'file' => 'required|file|max:10240'
            ]);

            $extension = strtolower($request->file('file')->getClientOriginalExtension());
            if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return response()->json([
                    'message' => 'The file field must be a file of type: xlsx, xls, csv.'
                ], 422);
            }
        }

        $isPreview = filter_var($request->input('preview', 'true'), FILTER_VALIDATE_BOOLEAN);

        try {
            if ($request->has('rows')) {
                // Read from JSON payload (final submit after preview edits)
                // When FormData sends array of objects, we might need to parse it if sent as string
                $rows = $request->input('rows');
                if (is_string($rows)) {
                    $rows = json_decode($rows, true);
                }
                $data = $rows;
            } else {
                // Read from Excel
                $data = Excel::toArray([], $request->file('file'))[0];
            }
            
            $imported = 0;
            $skipped = 0;
            $errors = [];
            $previewData = [];

            // We need to keep track of parent products created in this session
            // to link variations if they reference Parent SKU
            $parentProductsBySku = [];
            $seenSkus = [];

            foreach ($data as $index => $row) {
                // If it's from Excel, $index is numeric and row is array of columns.
                // If it's from frontend JSON, $row is associative array.
                if (!$request->has('rows') && $index === 0) continue; // Skip header from Excel

                // Read Columns based on the template or JSON payload
                $type = strtolower(trim($row[0] ?? $row['type'] ?? 'single'));
                $parentSku = trim($row[1] ?? $row['parentSku'] ?? '');
                $title = trim($row[2] ?? $row['title'] ?? '');
                $sku = trim($row[3] ?? $row['sku'] ?? '');
                $regularPrice = floatval($row[4] ?? $row['price'] ?? 0);
                $discount = floatval($row[5] ?? $row['discount'] ?? 0);
                $discountType = strtolower(trim($row[6] ?? $row['discountType'] ?? 'flat'));
                
                $manageStockStr = strtoupper(trim($row[7] ?? $row['manageStockStr'] ?? 'FALSE'));
                $manageStock = ($manageStockStr === 'TRUE' || $manageStockStr === '1' || $manageStockStr === true) ? 1 : 0;
                
                $stockQuantity = floatval($row[8] ?? $row['stock'] ?? 0);
                $warehouseName = trim($row[9] ?? $row['raw_warehouse'] ?? '');
                $stockStatus = strtolower(trim($row[10] ?? $row['stockStatus'] ?? 'in_stock'));
                $purpose = strtolower(trim($row[11] ?? $row['purpose'] ?? 'both'));
                
                $brandSlug = trim($row[12] ?? $row['raw_brand'] ?? '');
                $megaCatSlug = trim($row[13] ?? $row['raw_category'] ?? '');

                // Filters & Attributes detection based on column count or JSON payload
                if ($request->has('rows')) {
                    $rawFilters = trim($row['raw_filters'] ?? $row['filters'] ?? '');
                    $filterValueIds = isset($row['filter_value_ids']) && is_array($row['filter_value_ids'])
                        ? array_values(array_filter(array_map('intval', $row['filter_value_ids'])))
                        : [];
                    $attributesStr = trim($row['attributes'] ?? '');
                    $thumbnailUrl = trim($row['thumbnailUrl'] ?? '');
                    $shortDescription = trim($row['shortDescription'] ?? '') ?: null;
                    $fullDescription = trim($row['fullDescription'] ?? '') ?: null;
                    $metaTitle = trim($row['metaTitle'] ?? '') ?: null;
                    $metaDescription = trim($row['metaDescription'] ?? '') ?: null;
                    $metaKeywordsStr = trim($row['metaKeywords'] ?? '');
                } else {
                    $numCols = count($row);
                    if ($numCols >= 22) {
                        // 22-col template (with Filters at col 14)
                        $rawFilters = trim($row[14] ?? '');
                        $filterValueIds = [];
                        $attributesStr = trim($row[15] ?? '');
                        $thumbnailUrl = trim($row[16] ?? '');
                        $shortDescription = trim($row[17] ?? '') ?: null;
                        $fullDescription = trim($row[18] ?? '') ?: null;
                        $metaTitle = trim($row[19] ?? '') ?: null;
                        $metaDescription = trim($row[20] ?? '') ?: null;
                        $metaKeywordsStr = trim($row[21] ?? '');
                    } elseif ($numCols === 21) {
                        // 21-col template without Filters (col 14 was Attributes, col 17 was Full Description)
                        $rawFilters = '';
                        $filterValueIds = [];
                        $attributesStr = trim($row[14] ?? '');
                        $thumbnailUrl = trim($row[15] ?? '');
                        $shortDescription = trim($row[16] ?? '') ?: null;
                        $fullDescription = trim($row[17] ?? '') ?: null;
                        $metaTitle = trim($row[18] ?? '') ?: null;
                        $metaDescription = trim($row[19] ?? '') ?: null;
                        $metaKeywordsStr = trim($row[20] ?? '');
                    } else {
                        // 20-col legacy template
                        $rawFilters = '';
                        $filterValueIds = [];
                        $attributesStr = trim($row[14] ?? '');
                        $thumbnailUrl = trim($row[15] ?? '');
                        $shortDescription = trim($row[16] ?? '') ?: null;
                        $fullDescription = null;
                        $metaTitle = trim($row[17] ?? '') ?: null;
                        $metaDescription = trim($row[18] ?? '') ?: null;
                        $metaKeywordsStr = trim($row[19] ?? '');
                    }
                }
                
                $metaKeywords = $metaKeywordsStr ? array_values(array_filter(array_map('trim', explode(',', $metaKeywordsStr)))) : null;

                // Validation
                if (empty($title) || empty($sku)) {
                    $skipped++;
                    $errors[] = "Row $index: Title or SKU is missing.";
                    continue;
                }

                $companyId = auth()->user()->company_id ?? null;

                // Auto-generate unique SKU if already exists or duplicated in current batch
                $originalSku = $sku;
                $skuCandidate = $sku;
                $copyIndex = 1;

                while (true) {
                    $inBatch = in_array(strtolower($skuCandidate), $seenSkus);
                    $inProducts = Product::withoutGlobalScopes()
                        ->where(function($q) use ($skuCandidate) {
                            $q->where('sku_code', $skuCandidate)
                              ->orWhere('sku_code', '"' . $skuCandidate . '"')
                              ->orWhereRaw("REPLACE(sku_code, char(34), '') = ?", [$skuCandidate]);
                        })
                        ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                        ->exists();

                    $inVariations = ProductVariation::where('sku', $skuCandidate)
                        ->whereHas('product', function($q) use ($companyId) {
                            $q->withoutGlobalScopes();
                            if ($companyId) {
                                $q->where('company_id', $companyId);
                            }
                        })
                        ->exists();

                    if (!$inBatch && !$inProducts && !$inVariations) {
                        break;
                    }

                    if ($copyIndex === 1) {
                        $skuCandidate = $originalSku . '_COPY';
                    } else {
                        $skuCandidate = $originalSku . '_COPY_' . $copyIndex;
                    }
                    $copyIndex++;
                }

                $sku = $skuCandidate;
                $seenSkus[] = strtolower($sku);

                // Lookups
                $brandId = $row['brand_id'] ?? null;
                $isBrandMissing = false;
                if (!$brandId && $brandSlug) {
                    $brand = Brand::where('slug', $brandSlug)->orWhere('name', $brandSlug)->first();
                    if ($brand) {
                        $brandId = $brand->id;
                    } else {
                        $isBrandMissing = true;
                    }
                }

                $rawMegaCatIds = $row['mega_category_ids'] ?? null;
                $megaCatIds = null;
                if (!empty($rawMegaCatIds)) {
                    $megaCatIds = is_array($rawMegaCatIds) 
                        ? array_values(array_filter(array_map('intval', $rawMegaCatIds)))
                        : [intval($rawMegaCatIds)];
                }

                $rawSubCatIds = $row['sub_category_ids'] ?? null;
                $subCatIds = !empty($rawSubCatIds)
                    ? (is_array($rawSubCatIds) ? array_values(array_filter(array_map('intval', $rawSubCatIds))) : [intval($rawSubCatIds)])
                    : null;

                $rawMiniCatIds = $row['mini_category_ids'] ?? null;
                $miniCatIds = !empty($rawMiniCatIds)
                    ? (is_array($rawMiniCatIds) ? array_values(array_filter(array_map('intval', $rawMiniCatIds))) : [intval($rawMiniCatIds)])
                    : null;

                $rawExtraCatIds = $row['extra_category_ids'] ?? null;
                $extraCatIds = !empty($rawExtraCatIds)
                    ? (is_array($rawExtraCatIds) ? array_values(array_filter(array_map('intval', $rawExtraCatIds))) : [intval($rawExtraCatIds)])
                    : null;

                $isCategoryMissing = false;
                if ((empty($megaCatIds) && empty($subCatIds) && empty($miniCatIds) && empty($extraCatIds)) && $megaCatSlug) {
                    // Split by comma to support multiple categories in Excel (e.g. "Electronics, Gadgets" or "men, shoes")
                    $catSlugsOrNames = array_values(array_filter(array_map('trim', explode(',', $megaCatSlug))));
                    $resolvedMega = [];
                    $resolvedSub = [];
                    $resolvedMini = [];
                    $resolvedExtra = [];
                    $matchedCount = 0;

                    foreach ($catSlugsOrNames as $singleCat) {
                        // 1. Check in ExtraCategory first (and auto-link mini, sub, mega)
                        $extraCat = ExtraCategory::with(['miniCategory.subCategory'])->where('slug', $singleCat)->orWhere('name', $singleCat)->first();
                        if ($extraCat) {
                            $resolvedExtra[] = (int)$extraCat->id;
                            $miniId = $extraCat->mini_category_id;
                            $subId = $extraCat->sub_category_id ?: ($extraCat->miniCategory ? $extraCat->miniCategory->sub_category_id : null);
                            $megaId = $extraCat->mega_category_id ?: ($extraCat->miniCategory && $extraCat->miniCategory->subCategory ? $extraCat->miniCategory->subCategory->mega_category_id : null);

                            if ($miniId) $resolvedMini[] = (int)$miniId;
                            if ($subId) $resolvedSub[] = (int)$subId;
                            if ($megaId) $resolvedMega[] = (int)$megaId;

                            $matchedCount++;
                            continue;
                        }

                        // 2. Check in MiniCategory (and auto-link sub, mega)
                        $miniCat = MiniCategory::with('subCategory')->where('slug', $singleCat)->orWhere('name', $singleCat)->first();
                        if ($miniCat) {
                            $resolvedMini[] = (int)$miniCat->id;
                            $subId = $miniCat->sub_category_id;
                            $megaId = $miniCat->mega_category_id ?: ($miniCat->subCategory ? $miniCat->subCategory->mega_category_id : null);

                            if ($subId) $resolvedSub[] = (int)$subId;
                            if ($megaId) $resolvedMega[] = (int)$megaId;

                            $matchedCount++;
                            continue;
                        }

                        // 3. Check in SubCategory (and auto-link mega)
                        $subCat = SubCategory::where('slug', $singleCat)->orWhere('name', $singleCat)->first();
                        if ($subCat) {
                            $resolvedSub[] = (int)$subCat->id;
                            if ($subCat->mega_category_id) {
                                $resolvedMega[] = (int)$subCat->mega_category_id;
                            }
                            $matchedCount++;
                            continue;
                        }

                        // 4. Check in MegaCategory
                        $megaCat = MegaCategory::where('slug', $singleCat)->orWhere('name', $singleCat)->first();
                        if ($megaCat) {
                            $resolvedMega[] = (int)$megaCat->id;
                            $matchedCount++;
                            continue;
                        }
                    }

                    if (!empty($resolvedMega)) {
                        $megaCatIds = array_values(array_unique(array_merge($megaCatIds ?: [], $resolvedMega)));
                    }
                    if (!empty($resolvedSub)) {
                        $subCatIds = array_values(array_unique(array_merge($subCatIds ?: [], $resolvedSub)));
                    }
                    if (!empty($resolvedMini)) {
                        $miniCatIds = array_values(array_unique(array_merge($miniCatIds ?: [], $resolvedMini)));
                    }
                    if (!empty($resolvedExtra)) {
                        $extraCatIds = array_values(array_unique(array_merge($extraCatIds ?: [], $resolvedExtra)));
                    }

                    if ($matchedCount < count($catSlugsOrNames) || (empty($megaCatIds) && empty($subCatIds) && empty($miniCatIds) && empty($extraCatIds))) {
                        $isCategoryMissing = true;
                    }
                }

                $warehouseId = $row['warehouse_id'] ?? null;
                $isWarehouseMissing = false;
                if ($manageStock && !$warehouseId && $warehouseName) {
                    $warehouse = Warehouse::where('name', $warehouseName)->first();
                    if ($warehouse) {
                        $warehouseId = $warehouse->id;
                    } else {
                        $isWarehouseMissing = true;
                    }
                }

                // Resolve Filters from raw string (e.g. "RAM:8GB, Storage:256GB" or "8GB, 256GB")
                if (empty($filterValueIds) && !empty($rawFilters)) {
                    $resolvedFilterValueIds = [];
                    $filterParts = array_values(array_filter(array_map('trim', explode(',', $rawFilters))));
                    
                    foreach ($filterParts as $part) {
                        if (str_contains($part, ':')) {
                            [$fGroup, $fVal] = array_map('trim', explode(':', $part, 2));
                            $matchedVal = FilterValue::where('name', $fVal)
                                ->whereHas('filter', function($q) use ($fGroup) {
                                    $q->where('name', $fGroup);
                                })
                                ->first();
                            if ($matchedVal) {
                                $resolvedFilterValueIds[] = (int)$matchedVal->id;
                            }
                        } else {
                            $matchedVal = FilterValue::where('name', $part)->first();
                            if ($matchedVal) {
                                $resolvedFilterValueIds[] = (int)$matchedVal->id;
                            }
                        }
                    }
                    if (!empty($resolvedFilterValueIds)) {
                        $filterValueIds = array_values(array_unique($resolvedFilterValueIds));
                    }
                }

                // Determine if this is a child variation (has parent SKU) or a parent variation
                $isChildVariation = ($type === 'variation' && !empty($parentSku));
                $companyId = auth()->user()->company_id ?? null;

                // Pre-resolve parent product or slug candidate to align image naming with ProductService standards
                $parent = null;
                $slugCandidate = null;
                if ($isChildVariation) {
                    $parentQuery = Product::where('sku_code', $parentSku)->where('type', 'variation');
                    if ($companyId) {
                        $parentQuery->where('company_id', $companyId);
                    }
                    $parent = $parentQuery->first();
                    if (!$parent && isset($parentProductsBySku[$parentSku])) {
                        $parent = $parentProductsBySku[$parentSku];
                    }
                } else {
                    $baseSlug = Str::slug($title ?: 'product');
                    $slugCandidate = $baseSlug;
                    $slugCounter = 1;
                    while (Product::where('slug', $slugCandidate)->when($companyId, fn($q) => $q->where('company_id', $companyId))->exists()) {
                        $slugCandidate = $baseSlug . '-' . $slugCounter;
                        $slugCounter++;
                    }
                }

                // Image Upload (Generates WebP according to ProductService conventions)
                $thumbnailPath = null;
                $thumbnail310Path = null;
                $thumbnail95Path = null;
                $isImageMissing = empty($thumbnailUrl) && !$request->hasFile("rows.$index.imageFile");
                
                if (!$isPreview) {
                    if ($isChildVariation) {
                        // Follow ProductService: Str::slug($parent->slug) . '_variation_' . uniqid() . '-' . time() in 'products/variation'
                        $parentSlug = $parent ? $parent->slug : Str::slug($parentSku ?: 'product');
                        $customFileName = Str::slug($parentSlug) . '_variation_' . uniqid() . '-' . time();
                        $targetFolder = 'products/variation';

                        if ($request->hasFile("rows.$index.imageFile")) {
                            $imgFile = $request->file("rows.$index.imageFile");
                            try {
                                $thumbnailPath = FileUploadHelper::uploadImage(
                                    $imgFile,
                                    $targetFolder,
                                    'r2',
                                    2048,
                                    $customFileName,
                                    true
                                );
                            } catch (\Exception $e) {
                                Log::warning("Failed to upload variation multipart image for row {$index}: " . $e->getMessage());
                            }
                        } elseif ($thumbnailUrl && filter_var($thumbnailUrl, FILTER_VALIDATE_URL)) {
                            try {
                                $thumbnailPath = FileUploadHelper::uploadFromUrl($thumbnailUrl, $targetFolder, 'r2', true, $customFileName);
                            } catch (\Exception $e) {
                                Log::warning("Failed to upload variation image from URL for row {$index}: " . $e->getMessage());
                            }
                        }
                    } else {
                        // Single / Parent Product: Str::slug($slugCandidate) . '-' . time() with 310x310 and 95x95 thumbnails
                        $customFileName = Str::slug($slugCandidate) . '-' . time();
                        $targetFolder = 'products/thumbnails';

                        if ($request->hasFile("rows.$index.imageFile")) {
                            $imgFile = $request->file("rows.$index.imageFile");
                            try {
                                $thumbnailPath = FileUploadHelper::uploadImage(
                                    $imgFile,
                                    $targetFolder,
                                    'r2',
                                    2048,
                                    $customFileName,
                                    true
                                );
                                $thumbnail310Path = FileUploadHelper::uploadResizedWebpImage(
                                    $imgFile,
                                    $targetFolder,
                                    310,
                                    310,
                                    'r2',
                                    $customFileName . '-310x310',
                                    true
                                );
                                $thumbnail95Path = FileUploadHelper::uploadResizedWebpImage(
                                    $imgFile,
                                    $targetFolder,
                                    95,
                                    95,
                                    'r2',
                                    $customFileName . '-95x95',
                                    true
                                );
                            } catch (\Exception $e) {
                                Log::warning("Failed to upload multipart image for row {$index}: " . $e->getMessage());
                            }
                        } elseif ($thumbnailUrl && filter_var($thumbnailUrl, FILTER_VALIDATE_URL)) {
                            try {
                                $thumbnailPath = FileUploadHelper::uploadFromUrl($thumbnailUrl, $targetFolder, 'r2', true, $customFileName);
                                if ($thumbnailPath) {
                                    $thumbnail310Path = FileUploadHelper::generateResizedFromExisting(
                                        $thumbnailPath,
                                        $targetFolder,
                                        310,
                                        310,
                                        'r2',
                                        $customFileName . '-310x310'
                                    );
                                    $thumbnail95Path = FileUploadHelper::generateResizedFromExisting(
                                        $thumbnailPath,
                                        $targetFolder,
                                        95,
                                        95,
                                        'r2',
                                        $customFileName . '-95x95'
                                    );
                                }
                            } catch (\Exception $e) {
                                Log::warning("Failed to upload image from URL for row {$index}: " . $e->getMessage());
                            }
                        }
                    }
                }

                // Attribute validation for child variation
                $isAttributeMissing = false;
                $missingAttributeDetails = [];
                $missingAttributes = [];
                $validAttributePairs = [];

                if ($isChildVariation) {
                    if (empty($attributesStr)) {
                        $isAttributeMissing = true;
                        $missingAttributeDetails[] = "Attributes are required for variation rows.";
                    } else {
                        $attrPairs = explode(',', $attributesStr);
                        foreach ($attrPairs as $pair) {
                            $parts = explode(':', $pair);
                            if (count($parts) === 2) {
                                $groupName = trim($parts[0]);
                                $valueName = trim($parts[1]);

                                $group = \App\Models\AttributeGroup::where('name', $groupName)->first();
                                if (!$group) {
                                    $isAttributeMissing = true;
                                    $missingAttributeDetails[] = "Attribute group '$groupName' not found.";
                                    $missingAttributes[] = [
                                        'group' => $groupName,
                                        'value' => $valueName,
                                        'group_exists' => false,
                                        'group_id' => null,
                                    ];
                                } else {
                                    $value = \App\Models\AttributeValue::where('attribute_group_id', $group->id)
                                        ->where('name', $valueName)
                                        ->first();
                                    if (!$value) {
                                        $isAttributeMissing = true;
                                        $missingAttributeDetails[] = "Attribute value '$valueName' not found under '$groupName'.";
                                        $missingAttributes[] = [
                                            'group' => $groupName,
                                            'value' => $valueName,
                                            'group_exists' => true,
                                            'group_id' => $group->id,
                                        ];
                                    } else {
                                        $validAttributePairs[] = [
                                            'group_id' => $group->id,
                                            'value_id' => $value->id,
                                            'group_name' => $groupName,
                                            'value_name' => $valueName,
                                        ];
                                    }
                                }
                            } else {
                                $isAttributeMissing = true;
                                $missingAttributeDetails[] = "Invalid attribute format: '$pair'. Expected format: 'Group:Value'.";
                            }
                        }
                    }
                }
                
                if ($isChildVariation) {
                    $companyId = auth()->user()->company_id ?? null;
                    // This is a child variation
                    $parentQuery = Product::where('sku_code', $parentSku)->where('type', 'variation');
                    if ($companyId) {
                        $parentQuery->where('company_id', $companyId);
                    }
                    $parent = $parentQuery->first();
                    $parentId = $parent ? $parent->id : ($parentProductsBySku[$parentSku]->id ?? null);
                    
                    if (!$parentId) {
                        $skipped++;
                        $errors[] = "Row $index: Parent SKU ($parentSku) not found for variation.";
                        continue;
                    }

                    // If attributes are missing or invalid, skip this variation row during actual import
                    if (!$isPreview && $isAttributeMissing) {
                        $skipped++;
                        $errors[] = "Row $index: Variation skipped because attribute group/value is missing (" . implode(', ', $missingAttributeDetails) . ").";
                        continue;
                    }

                    if (!$isPreview && !empty($sku)) {
                        $skuExistsQuery = \App\Models\ProductVariation::where('sku', $sku);
                        if ($companyId) {
                            $skuExistsQuery->whereHas('product', fn($q) => $q->where('company_id', $companyId));
                        }
                        if ($skuExistsQuery->exists()) {
                            $skipped++;
                            $errors[] = "Row $index: Variation SKU '$sku' already exists in your store.";
                            continue;
                        }
                    }

                    if (!$isPreview) {
                        $variation = \App\Models\ProductVariation::create([
                            'product_id' => $parentId,
                            'sku' => $sku,
                            'image' => $thumbnailPath,
                            'regular_price' => $regularPrice,
                            'discount' => $discount,
                            'discount_type' => $discountType,
                            'stock_quantity' => $stockQuantity,
                            'available_stock' => $stockQuantity,
                            'stock_status' => $stockStatus,
                            'combination_hash' => md5($sku . time()),
                        ]);

                        if ($manageStock && $warehouseId) {
                            \App\Models\ProductVariationStock::create([
                                'product_variation_id' => $variation->id,
                                'warehouse_id' => $warehouseId,
                                'quantity' => $stockQuantity,
                                'available_quantity' => $stockQuantity,
                            ]);
                            
                            \App\Models\ProductVariationStockLedger::create([
                                'product_id' => $parentId,
                                'variation_id' => $variation->id,
                                'warehouse_id' => $warehouseId,
                                'transaction_type' => 'initial_stock',
                                'reference_type' => 'ProductImport',
                                'quantity_before' => 0,
                                'quantity_change' => $stockQuantity,
                                'quantity_after' => $stockQuantity,
                                'notes' => 'Initial stock via import',
                                'created_by' => Auth::id() ?? 1,
                            ]);
                        }

                        // Attach verified attributes
                        foreach ($validAttributePairs as $vPair) {
                            \App\Models\ProductVariationAttribute::create([
                                'product_variation_id' => $variation->id,
                                'attribute_group_id' => $vPair['group_id'],
                                'attribute_value_id' => $vPair['value_id'],
                            ]);
                        }
                    }
                } else {
                    // This is single or parent variation
                    $actualType = ($type === 'variable' || $type === 'variation') ? 'variation' : 'single';
                    
                    if (!$isPreview) {
                        // $slugCandidate was already pre-calculated prior to image upload
                        if (empty($slugCandidate)) {
                            $baseSlug = Str::slug($title ?: 'product');
                            $slugCandidate = $baseSlug;
                            $slugCounter = 1;

                            while (Product::where('slug', $slugCandidate)
                                ->when($companyId, fn($q) => $q->where('company_id', $companyId))
                                ->exists()) {
                                $slugCandidate = $baseSlug . '-' . $slugCounter;
                                $slugCounter++;
                            }
                        }

                        $product = Product::create([
                            'title' => $title,
                            'slug' => $slugCandidate,
                            'sku_code' => $sku,
                            'type' => $actualType,
                            'product_type' => 'finished',
                            'brand_id' => $brandId,
                            'mega_category_ids' => $megaCatIds ?: [],
                            'sub_category_ids' => $subCatIds ?: [],
                            'mini_category_ids' => $miniCatIds ?: [],
                            'extra_category_ids' => $extraCatIds ?: [],
                            'filter_value_ids' => $filterValueIds ?: [],
                            'regular_price' => $regularPrice,
                            'discount' => $discount,
                            'discount_type' => $discountType,
                            'manage_stock' => $manageStock,
                            'stock_status' => $stockStatus,
                            'purpose' => $purpose,
                            'thumbnail' => $thumbnailPath,
                            'thumbnail_310' => $thumbnail310Path,
                            'thumbnail_95' => $thumbnail95Path,
                            'short_description' => $shortDescription,
                            'full_description' => $fullDescription,
                            'meta_title' => $metaTitle,
                            'meta_description' => $metaDescription,
                            'meta_keywords' => $metaKeywords,
                            'stock_quantity' => ($manageStock && $actualType === 'single') ? $stockQuantity : 0,
                            'available_stock' => ($manageStock && $actualType === 'single') ? $stockQuantity : 0,
                            'warehouse_info' => ($manageStock && $warehouseId && $actualType === 'single') ? [['warehouse_id' => (string)$warehouseId, 'quantity' => (int)$stockQuantity]] : [],
                            'company_id' => auth()->user()->company_id, // ensure company boundaries
                        ]);

                        if ($actualType === 'variation') {
                            $parentProductsBySku[$sku] = $product;
                        }

                        if ($manageStock && $warehouseId && $actualType === 'single') {
                            \App\Models\ProductStockLedger::create([
                                'product_id' => $product->id,
                                'warehouse_id' => $warehouseId,
                                'transaction_type' => 'initial_stock',
                                'reference_type' => 'ProductImport',
                                'quantity_before' => 0,
                                'quantity_change' => $stockQuantity,
                                'quantity_after' => $stockQuantity,
                                'notes' => 'Initial stock via import',
                                'created_by' => Auth::id() ?? 1,
                            ]);
                        }
                    } else {
                        // In preview mode, fake the parent linking so child validation passes
                        if ($actualType === 'variation') {
                            $parentProductsBySku[$sku] = (object)['id' => 99999]; // dummy ID
                        }
                    }
                }

                $imported++;
                if ($isPreview) {
                    $previewData[] = [
                        'row' => $index,
                        'title' => $title,
                        'sku' => $sku,
                        'type' => $type,
                        'parentSku' => $parentSku,
                        'is_child_variation' => $isChildVariation,
                        'price' => $regularPrice,
                        'discount' => $discount,
                        'discount_type' => $discountType,
                        'stock' => $stockQuantity,
                        'manage_stock' => $manageStock,
                        
                        // Missing flags
                        'is_brand_missing' => $isBrandMissing,
                        'is_category_missing' => $isCategoryMissing,
                        'is_warehouse_missing' => $isWarehouseMissing,
                        'is_image_missing' => $isImageMissing,
                        'is_attribute_missing' => $isAttributeMissing,
                        'missing_attribute_details' => $missingAttributeDetails,
                        'missing_attributes' => $missingAttributes,
                        
                        // Raw data (what the user typed)
                        'raw_brand' => $brandSlug,
                        'raw_category' => $megaCatSlug,
                        'raw_filters' => $rawFilters,
                        'raw_warehouse' => $warehouseName,
                        'attributes' => $attributesStr,
                        'thumbnailUrl' => $thumbnailUrl,
                        'shortDescription' => $shortDescription,
                        'fullDescription' => $fullDescription,
                        'metaTitle' => $metaTitle,
                        'metaDescription' => $metaDescription,
                        'metaKeywords' => $metaKeywords,
                        
                        // Resolved IDs (if they were found)
                        'brand_id' => $brandId,
                        'mega_category_ids' => $megaCatIds ?: [],
                        'sub_category_ids' => $subCatIds ?: [],
                        'mini_category_ids' => $miniCatIds ?: [],
                        'extra_category_ids' => $extraCatIds ?: [],
                        'filter_value_ids' => $filterValueIds ?: [],
                        'warehouse_id' => $warehouseId,
                    ];
                }
            }

            if ($isPreview) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'is_preview' => true,
                        'preview_data' => $previewData,
                        'errors' => $errors,
                        'skipped' => $skipped
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'is_preview' => false,
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => $errors
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
