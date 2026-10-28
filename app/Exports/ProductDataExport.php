<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\MegaCategory;
use App\Models\SubCategory;
use App\Models\MiniCategory;
use App\Models\ExtraCategory;
use App\Models\FilterValue;
use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Helpers\FileUploadHelper;

class ProductDataExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $companyId;
    protected $productIds;

    public function __construct(array $productIds = [])
    {
        $this->companyId = auth()->user()?->company_id;
        $this->productIds = array_filter(array_map('intval', $productIds));
    }

    public function headings(): array
    {
        return [
            'Product Type',          // single, variation
            'Parent SKU',            // Only for 'variation' type (child)
            'Title',                 // Product Name
            'SKU',                   // Unique Product Code
            'Regular Price',
            'Discount',
            'Discount Type',         // flat or percent
            'Manage Stock',          // TRUE or FALSE
            'Stock Quantity',        // Only if Manage Stock is TRUE
            'Warehouse',             // Warehouse Name
            'Stock Status',          // in_stock, out_of_stock
            'Purpose',               // both, website, pos
            'Brand Slug',            // e.g. 'nike'
            'Category Slug',         // e.g. 'mens-fashion, summer-collection'
            'Filters',               // e.g. 'RAM:8GB, Storage:256GB'
            'Attributes',            // For variation child e.g. 'Size:XL, Color:Red'
            'Thumbnail URL',         // Public URL of the main image
            'Gallery Image URLs',    // Comma-separated public URLs e.g. 'https://.../img1.jpg, https://.../img2.jpg'
            'Short Description',
            'Full Description',
            'Meta Title',
            'Meta Description',
            'Meta Keywords'
        ];
    }

    public function array(): array
    {
        $query = Product::with([
            'brand',
            'galleries',
            'variations.attributes.attributeGroup',
            'variations.attributes.attributeValue',
            'variations.stocks.warehouse',
            'variations.galleries',
        ]);

        if ($this->companyId) {
            $query->where('company_id', $this->companyId);
        }

        if (!empty($this->productIds)) {
            $query->whereIn('id', $this->productIds);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return [];
        }

        // Preload Warehouses keyBy id for fast name lookup
        $warehouses = Warehouse::pluck('name', 'id')->toArray();

        // Preload Categories for slugs
        $megaIds = $products->pluck('mega_category_ids')->flatten()->unique()->filter()->values()->all();
        $subIds = $products->pluck('sub_category_ids')->flatten()->unique()->filter()->values()->all();
        $miniIds = $products->pluck('mini_category_ids')->flatten()->unique()->filter()->values()->all();
        $extraIds = $products->pluck('extra_category_ids')->flatten()->unique()->filter()->values()->all();

        $megaSlugs = !empty($megaIds) ? MegaCategory::whereIn('id', $megaIds)->pluck('slug', 'id')->toArray() : [];
        $subSlugs = !empty($subIds) ? SubCategory::whereIn('id', $subIds)->pluck('slug', 'id')->toArray() : [];
        $miniSlugs = !empty($miniIds) ? MiniCategory::whereIn('id', $miniIds)->pluck('slug', 'id')->toArray() : [];
        $extraSlugs = !empty($extraIds) ? ExtraCategory::whereIn('id', $extraIds)->pluck('slug', 'id')->toArray() : [];

        // Preload FilterValues
        $filterValueIds = $products->pluck('filter_value_ids')->flatten()->unique()->filter()->values()->all();
        $filterPairs = [];
        if (!empty($filterValueIds)) {
            $fValues = FilterValue::with('filter')->whereIn('id', $filterValueIds)->get();
            foreach ($fValues as $fv) {
                $fName = $fv->filter->name ?? '';
                $fvName = $fv->name ?? '';
                if ($fName && $fvName) {
                    $filterPairs[$fv->id] = "{$fName}:{$fvName}";
                }
            }
        }

        $rows = [];

        foreach ($products as $product) {
            $isVariable = in_array(strtolower($product->type ?? ''), ['variable', 'variation']) ||
                          in_array(strtolower($product->product_type ?? ''), ['variable', 'variation']);
            $productType = $isVariable ? 'variation' : 'single';

            // Clean SKU - If parent variation product has no sku_code, generate a unique parent SKU based on ID / slug
            $sku = is_array($product->sku_code) ? ($product->sku_code[0] ?? '') : $product->sku_code;
            $sku = trim((string)$sku, '"\' ');
            if (empty($sku)) {
                $sku = $isVariable 
                    ? 'PROD-' . $product->id . '-VAR' 
                    : 'PROD-' . $product->id;
            }

            // Calculate stock and warehouse name for single product
            $stockQty = '';
            $warehouseName = '';
            $manageStockStr = $product->manage_stock ? 'TRUE' : 'FALSE';

            if ($product->manage_stock && !$isVariable) {
                $warehouseInfo = $product->warehouse_info;
                if (is_string($warehouseInfo)) {
                    $warehouseInfo = json_decode($warehouseInfo, true);
                }

                if (is_array($warehouseInfo) && !empty($warehouseInfo)) {
                    $totalQty = 0;
                    $firstWhName = '';
                    foreach ($warehouseInfo as $whItem) {
                        $whId = $whItem['warehouse_id'] ?? null;
                        $q = (int)($whItem['quantity'] ?? 0);
                        $totalQty += $q;
                        if (!$firstWhName && $whId && isset($warehouses[$whId])) {
                            $firstWhName = $warehouses[$whId];
                        }
                    }
                    $stockQty = (string)$totalQty;
                    $warehouseName = $firstWhName;
                } else {
                    $stockQty = (string)($product->stock_quantity ?? 0);
                }
            }

            // Categories slug list
            $catSlugs = [];
            foreach ((array)($product->mega_category_ids ?? []) as $cid) {
                if (isset($megaSlugs[$cid])) $catSlugs[] = $megaSlugs[$cid];
            }
            foreach ((array)($product->sub_category_ids ?? []) as $cid) {
                if (isset($subSlugs[$cid])) $catSlugs[] = $subSlugs[$cid];
            }
            foreach ((array)($product->mini_category_ids ?? []) as $cid) {
                if (isset($miniSlugs[$cid])) $catSlugs[] = $miniSlugs[$cid];
            }
            foreach ((array)($product->extra_category_ids ?? []) as $cid) {
                if (isset($extraSlugs[$cid])) $catSlugs[] = $extraSlugs[$cid];
            }
            $catSlugStr = implode(', ', array_unique(array_filter($catSlugs)));

            // Filters list e.g. "Brand:Apple, Storage:128GB"
            $productFilters = [];
            foreach ((array)($product->filter_value_ids ?? []) as $fvid) {
                if (isset($filterPairs[$fvid])) {
                    $productFilters[] = $filterPairs[$fvid];
                }
            }
            $filterStr = implode(', ', array_unique($productFilters));

            // Thumbnail URL
            $thumbnailUrl = $product->thumbnail ? FileUploadHelper::getUrl($product->thumbnail, 'r2') : '';

            // Product Gallery Images (Comma-separated)
            $galleryUrls = [];
            if ($product->galleries && $product->galleries->isNotEmpty()) {
                foreach ($product->galleries as $gal) {
                    if (!empty($gal->image)) {
                        $galleryUrls[] = FileUploadHelper::getUrl($gal->image, 'r2');
                    }
                }
            }
            $galleryUrlsStr = implode(', ', array_filter($galleryUrls));

            // Meta Keywords
            $metaKeywordsStr = '';
            if (is_array($product->meta_keywords)) {
                $metaKeywordsStr = implode(', ', array_filter($product->meta_keywords));
            } elseif (is_string($product->meta_keywords)) {
                $metaKeywordsStr = $product->meta_keywords;
            }

            // 1. Parent/Single Product Row
            $rows[] = [
                $productType,                                    // Product Type
                '',                                              // Parent SKU (Blank for parent/single)
                $product->title ?? '',                           // Title
                $sku,                                            // SKU
                (string)($product->regular_price ?? '0'),        // Regular Price
                (string)($product->discount ?? '0'),             // Discount
                $product->discount_type ?? 'flat',               // Discount Type
                $manageStockStr,                                 // Manage Stock
                $stockQty,                                       // Stock Quantity
                $warehouseName,                                  // Warehouse
                $product->stock_status ?? 'in_stock',            // Stock Status
                $product->purpose ?? 'both',                     // Purpose
                $product->brand?->slug ?? '',                    // Brand Slug
                $catSlugStr,                                     // Category Slug
                $filterStr,                                      // Filters
                '',                                              // Attributes (Blank for parent)
                $thumbnailUrl,                                   // Thumbnail URL
                $galleryUrlsStr,                                 // Gallery Image URLs
                $product->short_description ?? '',               // Short Description
                $product->full_description ?? '',                // Full Description
                $product->meta_title ?? '',                      // Meta Title
                $product->meta_description ?? '',                // Meta Description
                $metaKeywordsStr                                 // Meta Keywords
            ];

            // 2. Child Variation Rows (If variable product)
            if ($isVariable && $product->variations && $product->variations->isNotEmpty()) {
                foreach ($product->variations as $vIndex => $variation) {
                    $varSku = trim((string)$variation->sku, '"\' ');
                    if (empty($varSku)) {
                        $varSku = $sku . '-V' . ($vIndex + 1);
                    }
                    
                    // Attributes format: "Size:XL, Color:Red"
                    $varAttrPairs = [];
                    if ($variation->attributes) {
                        foreach ($variation->attributes as $vAttr) {
                            $gName = $vAttr->attributeGroup->name ?? '';
                            $valName = $vAttr->attributeValue->name ?? '';
                            if ($gName && $valName) {
                                $varAttrPairs[] = "{$gName}:{$valName}";
                            }
                        }
                    }
                    $varAttrStr = implode(', ', $varAttrPairs);

                    // Variation stock and warehouse
                    $varStockQty = (string)($variation->stock_quantity ?? 0);
                    $varWarehouseName = '';
                    if ($variation->stocks && $variation->stocks->isNotEmpty()) {
                        $firstStock = $variation->stocks->first();
                        $varWarehouseName = $firstStock->warehouse->name ?? ($warehouses[$firstStock->warehouse_id] ?? '');
                        $varStockQty = (string)$variation->stocks->sum('quantity');
                    }

                    // Variation thumbnail
                    $varThumbnailUrl = $variation->image ? FileUploadHelper::getUrl($variation->image, 'r2') : '';

                    // Variation gallery images
                    $varGalleryUrls = [];
                    if ($variation->galleries && $variation->galleries->isNotEmpty()) {
                        foreach ($variation->galleries as $vGal) {
                            if (!empty($vGal->image)) {
                                $varGalleryUrls[] = FileUploadHelper::getUrl($vGal->image, 'r2');
                            }
                        }
                    }
                    $varGalleryUrlsStr = implode(', ', array_filter($varGalleryUrls));

                    $rows[] = [
                        'variation',                                       // Product Type
                        $sku,                                              // Parent SKU
                        ($product->title ?? '') . ' - ' . $varAttrStr,     // Title
                        $varSku,                                           // SKU
                        (string)($variation->regular_price ?? $product->regular_price ?? '0'), // Regular Price
                        (string)($variation->discount ?? '0'),              // Discount
                        $variation->discount_type ?? 'flat',               // Discount Type
                        $manageStockStr,                                   // Manage Stock
                        $varStockQty,                                      // Stock Quantity
                        $varWarehouseName,                                 // Warehouse
                        $variation->stock_status ?? 'in_stock',            // Stock Status
                        $product->purpose ?? 'both',                       // Purpose
                        '',                                                // Brand Slug (Inherited)
                        '',                                                // Category Slug (Inherited)
                        '',                                                // Filters
                        $varAttrStr,                                       // Attributes
                        $varThumbnailUrl,                                  // Thumbnail URL
                        $varGalleryUrlsStr,                                // Gallery Image URLs
                        '',                                                // Short Description
                        '',                                                // Full Description
                        '',                                                // Meta Title
                        '',                                                // Meta Description
                        ''                                                 // Meta Keywords
                    ];
                }
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'color' => ['argb' => 'FF13565E']],
            ],
        ];
    }
}
