@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::CHECKOUT,
        $setup->company_id ?? null
    );
@endphp
@include('components.meta-info.meta', [
    'setup' => $setup,

    'type' => 'WebPage',

    'title' => 'Secure Checkout - ' . ($setup->shop_name ?? 'Bhaiya Digital'),

    'description' => 'Finalize your purchase at ' . ($setup->shop_name ?? 'our shop') . '. Fill in your shipping details and choose your preferred payment method to complete your order.',

    'keywords' => 'checkout, secure payment, complete order',

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
            'url' => route('cart.index'),
        ],
        [
            'name' => 'Checkout',
            'url' => url()->current(),
        ],
    ],
])
