@php
    $product = $product ?? null;
    if (!$product) {
        return;
    }

    $rawMetaTitle = trim($product->meta_title ?? '');
    $metaTitle = (!empty($rawMetaTitle) && !in_array(strtolower($rawMetaTitle), ['null', 'undefined'])) ? $rawMetaTitle : null;

    $productTitle = $metaTitle ?: $product->title;
    $shopName = $setup->shop_name ?? '';

    // Page title: Product Title | Shop Name
    $pageTitle = $productTitle;
    if ($shopName && !str_contains(strtolower($pageTitle), strtolower($shopName))) {
        $pageTitle .= ' | ' . $shopName;
    }

    $rawMetaDesc = trim($product->meta_description ?? '');
    $customMetaDesc = (!empty($rawMetaDesc) && !in_array(strtolower($rawMetaDesc), ['null', 'undefined'])) ? $rawMetaDesc : null;

    $metaDescription = $customMetaDesc
        ?: ($product->short_description ? \Illuminate\Support\Str::limit(strip_tags($product->short_description), 160) : ($setup->description ?? ''));

    $rawKeywords = $product->meta_keywords;
    $metaKeywords = (is_array($rawKeywords) ? implode(',', array_filter($rawKeywords, fn($k) => !in_array(strtolower(trim($k)), ['null', 'undefined']))) : ((!empty($rawKeywords) && !in_array(strtolower(trim($rawKeywords)), ['null', 'undefined'])) ? $rawKeywords : ($setup->tags ?? '')));

    $metaImage = $product->thumbnail_url
        ?: ($product->thumbnail ? asset('storage/' . $product->thumbnail) : ($setup->meta_image ? asset('storage/' . $setup->meta_image) : asset('storage/' . ($setup->logo ?? ''))));

    $canonicalUrl = url($product->slug);

    $breadcrumbItems = [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => 'Products',
            'url' => route('shop.index'),
        ],
    ];

    if (!empty($breadcrumb) && (is_array($breadcrumb) || $breadcrumb instanceof \Illuminate\Support\Collection)) {
        foreach ($breadcrumb as $item) {
            $name = is_array($item) ? ($item['name'] ?? '') : ($item->name ?? '');
            $slug = is_array($item) ? ($item['slug'] ?? '') : ($item->slug ?? '');
            if ($name) {
                $breadcrumbItems[] = [
                    'name' => $name,
                    'url' => $slug ? url($slug) : url()->current(),
                ];
            }
        }
    }

    $breadcrumbItems[] = [
        'name' => $product->title,
        'url' => $canonicalUrl,
    ];

    $price = $product->sale_price ?? $product->regular_price ?? 0;
    $isInStock = !isset($product->available_stock) || $product->available_stock > 0;
@endphp

@include('components.meta-info.meta', [
    'setup' => $setup,
    'type' => 'Product',
    'title' => $pageTitle,
    'description' => $metaDescription,
    'keywords' => $metaKeywords,
    'image' => $metaImage,
    'canonical' => $canonicalUrl,
    'breadcrumb' => $breadcrumbItems,
    'schema' => [
        'name' => $product->title,
        'sku' => is_array($product->sku_code) ? implode(', ', $product->sku_code) : ($product->sku_code ?? null),
        'price' => $price,
        'currency' => 'BDT',
        'availability' => $isInStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
    ],
])