@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::CART,
        $setup->company_id ?? null
    );
@endphp
@include('components.meta-info.meta', [
    'setup' => $setup,

    'type' => 'WebPage',

    'title' => 'Shopping Cart (' . Cart::count() . ' items) - ' . ($setup->shop_name ?? 'Bhaiya Digital'),

    'description' => 'Review your selected baby products and proceed to checkout. Quality guaranteed with fast delivery.',

    'keywords' => 'shopping cart, checkout, buy baby products online',

    'image' => $setup->logo_url ?? asset('images/default-share-image.jpg'),

    'canonical' => url()->current(),

    'robots' => 'noindex, nofollow',

    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => 'Shopping Cart',
            'url' => url()->current(),
        ],
    ],
])
