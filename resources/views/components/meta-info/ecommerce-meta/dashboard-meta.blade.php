@include('components.meta-info.meta', [
    'setup' => $setup,
    'type' => 'WebPage',
    'title' => 'Dashboard | ' . ($setup->shop_name ?? 'Our Shop'),
    'description' => 'Customer dashboard, view orders, wishlist and profile details.',
    'keywords' => 'dashboard, my account, customer account, orders',
    'image' => $setup->logo_url ?? asset('images/default-share-image.jpg'),
    'canonical' => url()->current(),
    'robots' => 'noindex, nofollow',
    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => 'Dashboard',
            'url' => url()->current(),
        ],
    ],
])

