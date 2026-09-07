@include('components.meta-info.meta', [
    'setup' => $setup,

    'type' => 'Product',

    'title' => !empty($product->meta_title) ? $product->meta_title : $product->title,

    'description' => Str::limit(strip_tags($product->meta_description ?? $product->short_description ?? $product->full_description ?? ''), 160),

    'keywords' => $product->meta_keywords,

    'image' => $product->thumbnail_url ?? asset('images/no-image.png'),

    'canonical' => route('product.details', $product->slug),

    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/')
        ],
        [
            'name' => 'Products',
            'url' => route('shop.index')
        ],
        [
            'name' => $product->title,
            'url' => route('product.details', $product->slug)
        ]
    ],

    'schema' => [
        'name' => $product->title,
        'sku' => is_array($product->sku_code) ? implode(', ', $product->sku_code) : $product->sku_code,
        'price' => $product->display_price_data->sale_price ?? 0,
        'currency' => $setup->currency ?? 'BDT',
        'availability' => ($product->available_stock > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'
    ]
])
