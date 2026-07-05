@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::HOME, null
    );
@endphp

@include('components.meta-info',[
    'setup' => $setup,
    'type' => 'WebPage',
    'title' => $pageData->meta_title ?? $setup->title,
    'description' => $pageData->meta_description ?? $setup->description,
    'keywords' => $pageData->meta_keywords ? implode(',', $pageData->meta_keywords) : $setup->tags,

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
