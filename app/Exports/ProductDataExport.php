<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Helpers\FileUploadHelper;

class ProductDataExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $companyId;

    public function __construct()
    {
        $this->companyId = auth()->user()?->company_id;
    }

    public function collection()
    {
        // Load products with their relationships to export
        return Product::with(['brand', 'parent', 'warehouseInfo.warehouse', 'megaCategories'])
            ->where('company_id', $this->companyId)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Product Type',      // single, variable, variation
            'Parent SKU',        // Only for 'variation' type
            'Title',             // Product Name
            'SKU',               // Unique Product Code
            'Regular Price', 
            'Discount',
            'Discount Type',     // flat or percent
            'Manage Stock',      // TRUE or FALSE
            'Stock Quantity',
            'Warehouse',         // Warehouse Name
            'Stock Status',      // in_stock, out_of_stock
            'Purpose',           // both, website, pos
            'Brand Slug',        // e.g. 'nike'
            'Mega Category Slug',// e.g. 'mens-fashion'
            'Attributes',        // For variation e.g. 'Size:XL, Color:Red'
            'Thumbnail URL',     // Public URL of the image
            'Short Description'
        ];
    }

    public function map($product): array
    {
        // Calculate total stock if manage stock is true
        $stockQuantity = 0;
        $warehouseName = '';
        if ($product->manage_stock && $product->warehouseInfo && $product->warehouseInfo->isNotEmpty()) {
            $stockQuantity = $product->warehouseInfo->sum('quantity');
            $warehouseName = $product->warehouseInfo->first()->warehouse->name ?? '';
        }

        // Parent SKU
        $parentSku = $product->parent ? $product->parent->sku_code : '';

        // Mega Category Slugs
        $megaCategorySlugs = $product->megaCategories ? $product->megaCategories->pluck('slug')->implode(',') : '';

        // Thumbnail URL
        $thumbnailUrl = $product->thumbnail ? FileUploadHelper::getUrl($product->thumbnail, 'r2') : '';

        // Attributes for variation (TODO: format attributes)
        $attributesStr = '';

        return [
            $product->product_type ?? 'single',
            $parentSku,
            $product->title,
            $product->sku_code,
            $product->regular_price,
            $product->discount,
            $product->discount_type,
            $product->manage_stock ? 'TRUE' : 'FALSE',
            $stockQuantity,
            $warehouseName,
            $product->stock_status,
            $product->purpose,
            $product->brand ? $product->brand->slug : '',
            $megaCategorySlugs,
            $attributesStr,
            $thumbnailUrl,
            $product->short_description
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => 'FF13565E']]],
        ];
    }
}
