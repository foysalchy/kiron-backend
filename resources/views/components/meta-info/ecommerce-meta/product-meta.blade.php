@php
<<<<<<< HEAD
    $activeCategory = $category ?? $miniCategory ?? $subCategory ?? $megaCategory ?? null;
=======
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::SHOP,
        $setup->company_id ?? null
    );
@endphp
@php
    $activeCategory = $miniCategory ?? ($subCategory ?? ($megaCategory ?? null));
>>>>>>> 8cc9a85 (fix cart 4)
@endphp

@if ($activeCategory)
    {{-- ─── CATEGORY SPECIFIC META ─── --}}
    @php
        $breadcrumbItems = [['name' => 'Home', 'url' => url('/')]];

        if (isset($megaCategory)) {
            $breadcrumbItems[] = [
                'name' => $megaCategory->name,
                'url' => route('category.products', $megaCategory->slug),
            ];
        }
        if (isset($subCategory)) {
            $breadcrumbItems[] = [
                'name' => $subCategory->name,
                'url' => route('category.products', $subCategory->slug),
            ];
        }
        if (isset($miniCategory)) {
            $breadcrumbItems[] = [
                'name' => $miniCategory->name,
                'url' => route('category.products', $miniCategory->slug),
            ];
        }
        $canonicalUrl = url()->current();
    @endphp

    @include('components.meta-info.meta', [
<<<<<<< HEAD
        'setup'       => $setup,
        'type'        => 'CollectionPage',
        'title'       => ($activeCategory->meta_title ?: $activeCategory->name) . ' - ' . ($setup->shop_name ?? ''),
        'description' => $activeCategory->meta_description ?: 'Browse our latest collection of ' . $activeCategory->name,
        'keywords'    => is_array($activeCategory->meta_keywords) ? implode(',', $activeCategory->meta_keywords) : ($activeCategory->meta_keywords ?? ''),
        'image'       => $activeCategory->image ? asset('storage/' . $activeCategory->image) : asset('images/default-share.jpg'),
        'canonical'   => $canonicalUrl,
        'breadcrumb'  => $breadcrumbItems,
=======
        'setup' => $setup,
        'type' => 'CollectionPage',
        'title' => ($activeCategory->meta_title ?: $activeCategory->name) . ' - ' . ($setup->shop_name ?? ''),
        'description' =>
            $activeCategory->meta_description ?: 'Browse our latest collection of ' . $activeCategory->name,
        'keywords'    => is_array($activeCategory->meta_keywords) ? implode(',', $activeCategory->meta_keywords) : ($activeCategory->meta_keywords ?? ''),
        'image' => $activeCategory->image
            ? asset('storage/' . $activeCategory->image)
            : asset('images/default-share.jpg'),
        'canonical' => $canonicalUrl,
        'breadcrumb' => $breadcrumbItems,
>>>>>>> 8cc9a85 (fix cart 4)
    ])
@else
    {{-- ─── GENERAL SHOP PAGE META ─── --}}
    @php
        $pageData = \App\Services\Saas\SystemPageService::get(
            \App\Enums\SystemPageType::SHOP,
            $setup->company_id ?? null,
        );
    @endphp

<<<<<<< HEAD
    @include('components.meta-info.meta', [
        'setup'       => $setup,
        'type'        => 'CollectionPage',

        'title'       => $pageData?->meta_title ?? ('All Products | ' . ($setup->shop_name ?? 'Shop')),

        'description' => $pageData?->meta_description ?? ('Browse all products available on ' . ($setup->shop_name ?? 'our shop')),

        'keywords'    => ($pageData && $pageData->meta_keywords)
                         ? (is_array($pageData->meta_keywords) ? implode(',', $pageData->meta_keywords) : $pageData->meta_keywords)
                         : ($setup->tags ?? 'products, online shop'),

        'image'       => ($setup->meta_image ?? null) ? asset('storage/' . $setup->meta_image) : asset('storage/' . ($setup->logo ?? '')),
        'canonical'   => route('shop.index'),
        'breadcrumb'  => [
=======
    @include('components.meta-info', [
        'setup' => $setup,
        'type' => 'CollectionPage',
        'title' => $pageData->meta_title ?? 'All Products | ' . $setup->shop_name,
        'description' => $pageData->meta_description ?? 'Browse all products available on ' . $setup->shop_name,
        'keywords'    => ($pageData && $pageData->meta_keywords)
                         ? (is_array($pageData->meta_keywords) ? implode(',', $pageData->meta_keywords) : $pageData->meta_keywords)
                         : ($setup->tags ?? 'products, online shop'),
        'image' => $setup->meta_image ? asset('storage/' . $setup->meta_image) : asset('storage/' . $setup->logo),
        'canonical' => route('shop.index'),
        'breadcrumb' => [
>>>>>>> 8cc9a85 (fix cart 4)
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Products', 'url' => route('shop.index')],
        ],
    ])
@endif
