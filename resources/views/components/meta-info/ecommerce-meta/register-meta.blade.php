@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::REGISTER,
        $setup->company_id ?? null
    );
@endphp
@include('components.meta-info.meta', [
    'setup' => $setup,

    'type' => 'WebPage',

    'title' => 'Create an Account - ' . ($setup->shop_name ?? 'Bhaiya Digital'),

    'description' => 'Create a new account at ' . ($setup->shop_name ?? 'our shop') . ' to save your shipping details, track orders in real-time, and get exclusive member-only offers.',

    'keywords' => 'register, sign up, create account, join now, new customer',

    'image' => $setup->logo_url ?? asset('images/default-share-image.jpg'),

    'canonical' => url()->current(),

    'robots' => 'noindex, nofollow',

    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => 'Create Account',
            'url' => url()->current(),
        ],
    ],
])
