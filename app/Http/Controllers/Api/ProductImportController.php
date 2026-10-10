<?php

namespace App\Http\Controllers\Api;

use App\Exports\ProductTemplateExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Brand;
use App\Models\MegaCategory;
use App\Models\Warehouse;
use App\Models\Product;
use App\Helpers\FileUploadHelper;
use Illuminate\Support\Str;
use Exception;

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
                $attributesStr = trim($row[14] ?? $row['attributes'] ?? '');
                $thumbnailUrl = trim($row[15] ?? $row['thumbnailUrl'] ?? '');
                $shortDescription = trim($row[16] ?? $row['shortDescription'] ?? '') ?: null;
                $metaTitle = trim($row[17] ?? $row['metaTitle'] ?? '') ?: null;
                $metaDescription = trim($row[18] ?? $row['metaDescription'] ?? '') ?: null;
                
                $metaKeywordsStr = trim($row[19] ?? $row['metaKeywords'] ?? '');
                $metaKeywords = $metaKeywordsStr ? array_values(array_filter(array_map('trim', explode(',', $metaKeywordsStr)))) : null;

                // Validation
                if (empty($title) || empty($sku)) {
                    $skipped++;
                    $errors[] = "Row $index: Title or SKU is missing.";
                    continue;
                }

                if (Product::where('sku_code', $sku)->exists()) {
                    $skipped++;
                    $errors[] = "Row $index: SKU ($sku) already exists.";
                    continue;
                }

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

                $megaCatIds = $row['mega_category_ids'] ?? null;
                $isCategoryMissing = false;
                if (!$megaCatIds && $megaCatSlug) {
                    $megaCat = MegaCategory::where('slug', $megaCatSlug)->orWhere('name', $megaCatSlug)->first();
                    if ($megaCat) {
                        $megaCatIds = [$megaCat->id];
                    } else {
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

                // Image Upload
                $thumbnailPath = null;
                $isImageMissing = empty($thumbnailUrl) && !$request->hasFile("rows.$index.imageFile");
                
                if ($request->hasFile("rows.$index.imageFile")) {
                    $thumbnailPath = FileUploadHelper::upload($request->file("rows.$index.imageFile"), 'products', 'r2', true);
                } elseif ($thumbnailUrl && filter_var($thumbnailUrl, FILTER_VALIDATE_URL)) {
                    $thumbnailPath = FileUploadHelper::uploadFromUrl($thumbnailUrl, 'products', 'r2', true);
                }

                // Determine if this is a child variation (has parent SKU) or a parent variation
                $isChildVariation = ($type === 'variation' && !empty($parentSku));
                
                if ($isChildVariation) {
                    // This is a child variation
                    $parent = Product::where('sku_code', $parentSku)->where('type', 'variation')->first();
                    $parentId = $parent ? $parent->id : ($parentProductsBySku[$parentSku]->id ?? null);
                    
                    if (!$parentId) {
                        $skipped++;
                        $errors[] = "Row $index: Parent SKU ($parentSku) not found for variation.";
                        continue;
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
                        }

                        // Basic Attribute Parsing (e.g. Size:M, Color:Red)
                        if (!empty($attributesStr)) {
                            $attrPairs = explode(',', $attributesStr);
                            foreach ($attrPairs as $pair) {
                                $parts = explode(':', $pair);
                                if (count($parts) === 2) {
                                    $groupName = trim($parts[0]);
                                    $valueName = trim($parts[1]);
                                    
                                    $group = \App\Models\AttributeGroup::where('name', $groupName)->first();
                                    if ($group) {
                                        $value = \App\Models\AttributeValue::where('attribute_group_id', $group->id)
                                            ->where('name', $valueName)
                                            ->first();
                                            
                                        if ($value) {
                                            \App\Models\ProductVariationAttribute::create([
                                                'product_variation_id' => $variation->id,
                                                'attribute_group_id' => $group->id,
                                                'attribute_value_id' => $value->id,
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // This is single or parent variation
                    $actualType = ($type === 'variable' || $type === 'variation') ? 'variation' : 'single';
                    
                    if (!$isPreview) {
                        $product = Product::create([
                            'title' => $title,
                            'slug' => Str::slug($title) . '-' . time() . '-' . rand(100,999),
                            'sku_code' => $sku,
                            'type' => $actualType,
                            'product_type' => 'finished',
                            'brand_id' => $brandId,
                            'mega_category_ids' => collect($megaCatIds)->toJson(),
                            'regular_price' => $regularPrice,
                            'discount' => $discount,
                            'discount_type' => $discountType,
                            'manage_stock' => $manageStock,
                            'stock_status' => $stockStatus,
                            'purpose' => $purpose,
                            'thumbnail' => $thumbnailPath,
                            'short_description' => $shortDescription,
                            'meta_title' => $metaTitle,
                            'meta_description' => $metaDescription,
                            'meta_keywords' => $metaKeywords,
                            'company_id' => auth()->user()->company_id, // ensure company boundaries
                        ]);

                        if ($actualType === 'variation') {
                            $parentProductsBySku[$sku] = $product;
                        }

                        if ($manageStock && $warehouseId && $actualType === 'single') {
                            \App\Models\WarehouseInventory::create([
                                'product_id' => $product->id,
                                'warehouse_id' => $warehouseId,
                                'quantity' => $stockQuantity
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
                        'price' => $regularPrice,
                        'stock' => $stockQuantity,
                        'manage_stock' => $manageStock,
                        
                        // Missing flags
                        'is_brand_missing' => $isBrandMissing,
                        'is_category_missing' => $isCategoryMissing,
                        'is_warehouse_missing' => $isWarehouseMissing,
                        'is_image_missing' => $isImageMissing,
                        
                        // Raw data (what the user typed)
                        'raw_brand' => $brandSlug,
                        'raw_category' => $megaCatSlug,
                        'raw_warehouse' => $warehouseName,
                        'attributes' => $attributesStr,
                        'thumbnailUrl' => $thumbnailUrl,
                        'metaTitle' => $metaTitle,
                        'metaDescription' => $metaDescription,
                        'metaKeywords' => $metaKeywords,
                        
                        // Resolved IDs (if they were found)
                        'brand_id' => $brandId,
                        'mega_category_ids' => $megaCatIds,
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
