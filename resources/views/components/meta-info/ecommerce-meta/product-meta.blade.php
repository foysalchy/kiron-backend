@php

    $isFlashSale = request()->routeIs('flash.sale') || (isset($category->name) && $category->name == 'Flash Sale Items');

    $activeCategory = $category ?? $miniCategory ?? $subCategory ?? $megaCategory ?? null;

    $breadcrumbItems = [['name' => 'Home', 'url' => url('/')]];
@endphp

@if ($isFlashSale)
    {{-- ─── FLASH SALE META ─── --}}
    @php
        $breadcrumbItems[] = ['name' => 'Flash Sale', 'url' => url()->current()];
    @endphp

    @include('components.meta-info.meta', [
        'setup'       => $setup,
        'type'        => 'CollectionPage',
        'title'       => 'Flash Sale - Best Deals | ' . ($setup->shop_name ?? 'Shop'),
        'description' => 'Get amazing discounts on our flash sale items. Limited time offers!',
        'keywords'    => 'flash sale, discount, deals, offers',
        'image'       => $setup->meta_image ? asset('storage/' . $setup->meta_image) : asset('images/default-share.jpg'),
        'canonical'   => url()->current(),
        'breadcrumb'  => $breadcrumbItems,
    ])

@elseif ($activeCategory)
    {{-- ─── CATEGORY SPECIFIC META ─── --}}
    @php
        if (isset($megaCategory)) {
            $breadcrumbItems[] = ['name' => $megaCategory->name, 'url' => url($megaCategory->slug)];
        }
        if (isset($subCategory)) {
            $breadcrumbItems[] = ['name' => $subCategory->name, 'url' => url($subCategory->slug)];
        }
        if (isset($miniCategory)) {
            $breadcrumbItems[] = ['name' => $miniCategory->name, 'url' => url($miniCategory->slug)];
        }
        if (!isset($megaCategory) && !isset($subCategory) && !isset($miniCategory) && isset($activeCategory->name)) {
             $breadcrumbItems[] = ['name' => $activeCategory->name, 'url' => url()->current()];
        }

        $canonicalUrl = url()->current();
    @endphp

    @include('components.meta-info.meta', [
        'setup'       => $setup,
        'type'        => 'CollectionPage',
        'title'       => ($activeCategory->meta_title ?? $activeCategory->name ?? 'Category') . ' | ' . ($setup->shop_name ?? ''),
        'description' => ($activeCategory->meta_description ?? 'Browse our latest collection of ' . ($activeCategory->name ?? 'products')),
        'keywords'    => is_array($activeCategory->meta_keywords ?? null) ? implode(',', $activeCategory->meta_keywords) : ($activeCategory->meta_keywords ?? ''),
        'image'       => ($activeCategory->image ?? null) ? asset('storage/' . $activeCategory->image) : asset('images/default-share.jpg'),
        'canonical'   => $canonicalUrl,
        'breadcrumb'  => $breadcrumbItems,
    ])

@else
    {{-- ─── GENERAL SHOP PAGE META ─── --}}
    @php
        $pageData = \App\Services\Saas\SystemPageService::get(
            \App\Enums\SystemPageType::SHOP,
            $setup->company_id ?? null,
        );
    @endphp

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
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Products', 'url' => route('shop.index')],
        ],
    ])
@endif
