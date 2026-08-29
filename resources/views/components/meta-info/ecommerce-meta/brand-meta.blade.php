@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::BRAND_LIST,
        $setup->company_id ?? null
    );

    $isSingleBrand = isset($brand) && !($brand instanceof \Illuminate\Support\Collection);
@endphp

@if ($isSingleBrand)
    {{-- ─── SINGLE BRAND PAGE META ─── --}}
    @include('components.meta-info.meta', [
        'setup'       => $setup,
        'type'        => 'CollectionPage',
        'title'       => ($brand->meta_title ?: $brand->name . ' Products') . '|' . ($setup->shop_name ?? ''),
        'description' => $brand->meta_description ?: 'Shop the latest collection of authentic ' . $brand->name . ' products.',
        'keywords'    => $brand->meta_keywords ?: $brand->name . ', brand shop',
        'image'       => $brand->image ? asset('storage/' . $brand->image) : $setup->logo_url,
        'canonical'   => url()->current(),
        'breadcrumb'  => [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Brands', 'url' => route('brand.index')],
            ['name' => $brand->name, 'url' => url()->current()],
        ],
    ])
@else
    {{-- ─── ALL BRANDS LIST PAGE META ─── --}}
    @include('components.meta-info.meta', [
        'setup'       => $setup,
        'type'        => 'WebPage',

        'title'       => $pageData?->meta_title ?? ('All Brands | ' . ($setup->shop_name ?? 'Shop')),

        'description' => $pageData?->meta_description ?? 'Browse all the authentic brands available in our store.',

        'keywords'    => ($pageData && $pageData->meta_keywords)
                         ? (is_array($pageData->meta_keywords) ? implode(',', $pageData->meta_keywords) : $pageData->meta_keywords)
                         : ($setup->tags ?? 'brands, online shopping'),

        'image'       => $setup->meta_image ? asset('storage/' . $setup->meta_image) : $setup->logo_url,
        'canonical'   => route('brand.index'),
        'breadcrumb'  => [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Brands', 'url' => route('brand.index')],
        ],
    ])
@endif
