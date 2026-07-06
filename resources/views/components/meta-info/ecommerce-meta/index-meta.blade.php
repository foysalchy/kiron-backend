@php
$pageData = \App\Services\Saas\SystemPageService::get(
\App\Enums\SystemPageType::HOME,
$setup->company_id ?? null
);
@endphp

@include('components.meta-info.meta',[
    'setup' => $setup,
    'type' => 'WebPage',
    'title' => $pageData->meta_title ?? $setup->title ?? $setup->shop_name ?? 'Dorja',
    'description' => $pageData->meta_description ?? $setup->description ?? '',
    'keywords' => (isset($pageData->meta_keywords) && is_array($pageData->meta_keywords))
        ? implode(',', $pageData->meta_keywords)
        : ($setup?->tags ?? ''),

    'image' => $setup->meta_image
        ? asset('storage/'.$setup->meta_image)
        : asset('storage/'.$setup->logo),
    'canonical' => url()->current(),
    'breadcrumb' => [
        [
            'name'=>'Home',
            'url'=>url('/')
        ]
    ]
])
