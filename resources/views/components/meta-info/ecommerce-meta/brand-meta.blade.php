@include('components.meta-info', [
    'setup' => $setup,

    'type' => 'CollectionPage',

    'title' => ($brand->meta_title ?: $brand->name . ' Products') . ' - ' . ($setup->shop_name ?? 'Bhaiya Digital'),

    'description' => $brand->meta_description ?: 'Shop the latest collection of authentic ' . $brand->name . ' products at ' . ($setup->shop_name ?? 'our store') . '. Quality and fast delivery guaranteed.',

    'keywords' => $brand->meta_keywords ?: $brand->name . ', ' . $brand->name . ' online shop, authentic ' . $brand->name,

    'image' => $brand->image ? asset('storage/' . $brand->image) : $setup->logo_url,

    'canonical' => url()->current(),

    'robots' => 'index, follow',

    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => 'Brands',
            'url' => route('brand.index'),
        ],
        [
            'name' => $brand->name,
            'url' => url()->current(),
        ],
    ],
])
