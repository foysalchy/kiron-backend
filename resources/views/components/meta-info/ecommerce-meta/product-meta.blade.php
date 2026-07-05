@php
    $activeCategory = $miniCategory ?? $subCategory ?? $megaCategory ?? null;
@endphp

@if($activeCategory)
    {{-- ─── CATEGORY SPECIFIC META ─── --}}
    @php
        $breadcrumbItems = [['name' => 'Home', 'url' => url('/')]];

        if (isset($megaCategory)) {
            $breadcrumbItems[] = ['name' => $megaCategory->name, 'url' => route('category.products', $megaCategory->slug)];
        }
        if (isset($subCategory)) {
            $breadcrumbItems[] = ['name' => $subCategory->name, 'url' => route('category.products', $subCategory->slug)];
        }
        if (isset($miniCategory)) {
            $breadcrumbItems[] = ['name' => $miniCategory->name, 'url' => route('category.products', $miniCategory->slug)];
        }
        $canonicalUrl = url()->current();
    @endphp

    @include('components.meta-info.meta', [
        'setup'       => $setup,
        'type'        => 'CollectionPage',
        'title'       => ($activeCategory->meta_title ?: $activeCategory->name) . ' - ' . ($setup->shop_name ?? ''),
        'description' => $activeCategory->meta_description ?: 'Browse our latest collection of ' . $activeCategory->name,
        'keywords'    => is_array($activeCategory->meta_keywords) ? implode(',', $activeCategory->meta_keywords) : $activeCategory->meta_keywords,
        'image'       => $activeCategory->image ? asset('storage/' . $activeCategory->image) : asset('images/default-share.jpg'),
        'canonical'   => $canonicalUrl,
        'breadcrumb'  => $breadcrumbItems,
    ])

@else
    {{-- ─── GENERAL SHOP PAGE META ─── --}}
    @php
        $pageData = \App\Services\Saas\SystemPageService::get(
            \App\Enums\SystemPageType::SHOP,
            $setup->company_id ?? null
        );
    @endphp

    @include('components.meta-info', [
        'setup'       => $setup,
        'type'        => 'CollectionPage',
        'title'       => $pageData->meta_title ?? ('All Products | ' . $setup->shop_name),
        'description' => $pageData->meta_description ?? ('Browse all products available on ' . $setup->shop_name),
        'keywords'    => $pageData->meta_keywords ? (is_array($pageData->meta_keywords) ? implode(',', $pageData->meta_keywords) : $pageData->meta_keywords) : 'products, online shop',
        'image'       => $setup->meta_image ? asset('storage/' . $setup->meta_image) : asset('storage/' . $setup->logo),
        'canonical'   => route('shop.index'),
        'breadcrumb'  => [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Products', 'url' => route('shop.index')],
        ],
    ])
@endif
