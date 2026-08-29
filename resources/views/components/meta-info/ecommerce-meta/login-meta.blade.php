@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::LOGIN,
        $setup->company_id ?? null
    );
@endphp

@include('components.meta-info.meta', [
    'setup' => $setup,

    'type' => 'WebPage',

    'title' => ($pageData->meta_title ?? 'Customer Login') . '|' . ($setup->shop_name ?? 'Bhaiya Digital'),

    'description' => $pageData->meta_description ?? 'Login to your account to track your orders, manage your profile, and enjoy a personalized shopping experience.',

    'keywords' => $pageData->meta_keywords ?? 'login, customer login, account access, sign in',

    'image' => $setup->logo_url ?? asset('images/default-share-image.jpg'),

    'canonical' => url()->current(),

    'robots' => 'noindex, nofollow',

    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => 'Login',
            'url' => url()->current(),
        ],
    ],
])
