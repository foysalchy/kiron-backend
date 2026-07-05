@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::BLOG_LIST,
        $setup->company_id ?? null
    );

    $keywords = $pageData->meta_keywords ?? 'blog, products, shopping tips';
    if (is_array($keywords)) {
        $keywords = implode(', ', $keywords);
    }
@endphp

@include('components.meta-info', [
    'setup' => $setup,
    'type' => 'CollectionPage',
    'title' => ($pageData->meta_title ?? 'Our Blog') . ' - ' . ($setup->shop_name ?? 'Bhaiya Digital'),
    'description' => $pageData->meta_description ?? 'Read the latest insights, styles, and shopping tips on our blog.',
    'keywords' => $keywords,
    'image' => $setup->meta_image ? asset('storage/' . $setup->meta_image) : $setup->logo_url,
    'canonical' => route('blog.index'),
    'breadcrumb' => [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Blog', 'url' => route('blog.index')],
    ],
])
