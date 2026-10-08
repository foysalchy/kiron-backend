<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
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
            'Manage Stock',      // TRUE or FALSE (If FALSE, leave Stock Qty and Warehouse blank)
            'Stock Quantity',    // Only if Manage Stock is TRUE
            'Warehouse',         // Warehouse Name (Only if Manage Stock is TRUE)
            'Stock Status',      // in_stock, out_of_stock
            'Purpose',           // both, website, pos
            'Brand Slug',        // e.g. 'nike'
            'Mega Category Slug',// e.g. 'mens-fashion'
            'Attributes',        // For variation e.g. 'Size:XL, Color:Red'
            'Thumbnail URL',     // Public URL of the image
            'Short Description'
        ];
    }

    public function array(): array
    {
        return [
            // Example 1: Single Product (Simple product with no variations)
            ['single', '', 'Mens Cotton T-Shirt', 'TSHIRT-001', '500', '50', 'flat', 'TRUE', '100', 'Main Warehouse', 'in_stock', 'both', 'nike', 'mens-fashion', '', 'https://example.com/img1.jpg', 'Premium quality cotton t-shirt'],
            
            // Example 2: Variable Product Parent (Mother product that holds variations)
            ['variable', '', 'Running Sneakers', 'SNEAKER-001', '1500', '0', 'flat', 'FALSE', '', '', 'in_stock', 'website', 'nike', 'mens-fashion', '', 'https://example.com/img2.jpg', 'Comfortable running sneakers with multiple sizes and colors'],
            
            // Example 3: Variation Product 1 (Child of SNEAKER-001)
            ['variation', 'SNEAKER-001', 'Running Sneakers - Black 42', 'SNEAKER-001-B42', '1500', '10', 'percent', 'TRUE', '50', 'Main Warehouse', 'in_stock', 'website', '', '', 'Color:Black, Size:42', 'https://example.com/img2-black.jpg', 'Black color running sneaker size 42'],
            
            // Example 4: Variation Product 2 (Child of SNEAKER-001)
            ['variation', 'SNEAKER-001', 'Running Sneakers - Red 42', 'SNEAKER-001-R42', '1600', '15', 'percent', 'TRUE', '30', 'Main Warehouse', 'in_stock', 'website', '', '', 'Color:Red, Size:42', 'https://example.com/img2-red.jpg', 'Red color running sneaker size 42'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => 'FF13565E']]],
        ];
    }
}
