@php
    $landing = $landing ?? null;
    if (!$landing) {
        return;
    }

    $rawMetaTitle = trim($landing->meta_title ?? '');
    $metaTitle = (!empty($rawMetaTitle) && !in_array(strtolower($rawMetaTitle), ['null', 'undefined'])) ? $rawMetaTitle : null;

    $landingTitle = $metaTitle ?: ($landing->title ?: ($landing->name ?: 'Landing Page'));
    $shopName = $setup->shop_name ?? '';

    // Page title: Landing Title | Shop Name
    $pageTitle = $landingTitle;
    if ($shopName && !str_contains(strtolower($pageTitle), strtolower($shopName))) {
        $pageTitle .= ' | ' . $shopName;
    }

    $rawMetaDesc = trim($landing->meta_description ?? '');
    $customMetaDesc = (!empty($rawMetaDesc) && !in_array(strtolower($rawMetaDesc), ['null', 'undefined'])) ? $rawMetaDesc : null;

    $metaDescription = $customMetaDesc
        ?: ($landing->short_description 
            ? \Illuminate\Support\Str::limit(strip_tags($landing->short_description), 160) 
            : ($landing->description 
                ? \Illuminate\Support\Str::limit(strip_tags($landing->description), 160) 
                : ($setup->description ?? '')));

    $rawKeywords = $landing->meta_keywords;
    $metaKeywords = (is_array($rawKeywords) 
        ? implode(',', array_filter($rawKeywords, fn($k) => !in_array(strtolower(trim($k)), ['null', 'undefined']))) 
        : ((!empty($rawKeywords) && !in_array(strtolower(trim($rawKeywords)), ['null', 'undefined'])) 
            ? $rawKeywords 
            : ($setup->tags ?? '')));

    $metaImage = $landing->meta_image_url
        ?: ($landing->thumbnail_url 
            ?: ($landing->product->thumbnail_url ?? ($setup->meta_image_url ?? $setup->logo_url)));

    $canonicalUrl = url($landing->slug);

    $breadcrumbItems = [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => $landing->title ?: 'Landing Page',
            'url' => $canonicalUrl,
        ],
    ];

    $product = $landing->product ?? null;
    $schema = [];
    if ($product) {
        $price = $landing->discount_price ?? $landing->regular_price ?? $product->sale_price ?? $product->regular_price ?? 0;
        $isInStock = !isset($product->available_stock) || $product->available_stock > 0;
        $brandName = $product->brand->name ?? $setup->shop_name ?? 'Generic';
        $mpn = is_array($product->sku_code) ? implode(', ', $product->sku_code) : ($product->sku_code ?? null);

        $schema = [
            'name' => $landing->title ?: $product->title,
            'sku' => $mpn,
            'mpn' => $mpn,
            'price' => $price,
            'currency' => 'BDT',
            'availability' => $isInStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'brand_name' => $brandName,
        ];
    }
@endphp

@include('components.meta-info.meta', [
    'setup' => $setup,
    'type' => $product ? 'Product' : 'WebPage',
    'title' => $pageTitle,
    'description' => $metaDescription,
    'keywords' => $metaKeywords,
    'image' => $metaImage,
    'canonical' => $canonicalUrl,
    'breadcrumb' => $breadcrumbItems,
    'schema' => $schema,
])

