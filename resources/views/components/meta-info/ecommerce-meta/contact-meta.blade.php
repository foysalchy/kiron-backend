@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::CONTACT_US,
        $setup->company_id ?? null
    );
@endphp

@include('components.meta-info.meta', [
    'setup' => $setup,
    'type' => 'ContactPage',

    'title' => $pageData?->meta_title ?? ('Contact Us | ' . $setup->shop_name),

    'description' => $pageData?->meta_description ?? ('Contact ' . $setup->shop_name . ' for sales, support, product demos, or any business inquiries. We are here to help you grow your business.'),

    'keywords' => $pageData?->meta_keywords
        ? implode(',', $pageData->meta_keywords)
        : 'contact, support, sales, customer service, business software',

    'image' => $setup->meta_image_url ?: $setup->logo_url,

    'canonical' => route('contact.index'),

    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => 'Contact',
            'url' => route('contact.index'),
        ],
    ],
])
