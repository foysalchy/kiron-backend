<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements FromArray, WithHeadings, WithStyles
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
            'Stock Quantity',
            'Stock Status',      // in_stock, out_of_stock
            'Brand Slug',        // e.g. 'nike'
            'Category Slug',     // e.g. 'mens-fashion'
            'Attributes',        // For variation e.g. 'Size:XL, Color:Red'
            'Thumbnail URL',     // Public URL of the image
            'Short Description'
        ];
    }

    public function array(): array
    {
        return [
            // Example 1: Single Product
            ['single', '', 'Mens Cotton T-Shirt', 'TSHIRT-001', '500', '50', 'flat', '100', 'in_stock', 'local', 't-shirts', '', 'https://example.com/img1.jpg', 'Good tshirt'],
            
            // Example 2: Variable Product (Parent)
            ['variable', '', 'Nike Running Shoe', 'NIKE-SHOE-01', '', '', '', '', 'in_stock', 'nike', 'shoes', '', 'https://example.com/img2.jpg', 'Running shoe'],
            
            // Example 3: Variations of the above Variable Product
            ['variation', 'NIKE-SHOE-01', 'Nike Running Shoe - 42 Red', 'NIKE-SHOE-01-42-R', '2500', '', '', '10', 'in_stock', '', '', 'Size:42, Color:Red', '', ''],
            ['variation', 'NIKE-SHOE-01', 'Nike Running Shoe - 43 Black', 'NIKE-SHOE-01-43-B', '2500', '', '', '15', 'in_stock', '', '', 'Size:43, Color:Black', '', ''],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'color' => ['argb' => 'FF13565E']]],
        ];
    }
}
