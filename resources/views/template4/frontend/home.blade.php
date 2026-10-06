@extends('template4.layouts.front')
@section('meta')
@include('components.meta-info.ecommerce-meta.index-meta', ['setup' => $setup])
@if(isset($mainSliders) && $mainSliders->isNotEmpty())
@php $firstSlider = $mainSliders->first(); @endphp
<link rel="preload" as="image" href="{{ $firstSlider->mobile_image_url ?? asset('images/template1/frontend/cover.webp') }}" media="(max-width: 767px)" fetchpriority="high">
<link rel="preload" as="image" href="{{ $firstSlider->image_url ?? asset('images/template1/frontend/cover.webp') }}" media="(min-width: 768px)" fetchpriority="high">
@endif
@endsection
@push('styles')
<style>
    .p-0 {
        padding: 0 !important
    }

    @keyframes marqueeFast {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    .animate-marquee-fast {
        display: flex;
        width: max-content;
        animation: marqueeFast 10s linear infinite;
    }

    .notch-border {
        filter: drop-shadow(1px 0 0 #e5e7eb) drop-shadow(-1px 0 0 #e5e7eb) drop-shadow(0 1px 0 #e5e7eb) drop-shadow(0 -1px 0 #e5e7eb);
    }

    .notch-bottom-right {
        clip-path: polygon(0% 0%,
                100% 0%,
                100% 75%,
                75% 75%,
                75% 100%,
                0% 100%);
    }

    .notch-bottom-left {
        clip-path: polygon(0% 0%,
                100% 0%,
                100% 100%,
                25% 100%,
                25% 75%,
                0% 75%);
    }

    .notch-top-right {
        clip-path: polygon(0% 0%,
                75% 0%,
                75% 25%,
                100% 25%,
                100% 100%,
                0% 100%);
    }

    .notch-top-left {
        clip-path: polygon(0% 25%,
                25% 25%,
                25% 0%,
                100% 0%,
                100% 100%,
                0% 100%);
    }

    .product-card-notch {
        -webkit-mask-image: radial-gradient(circle 50px at 100% 0%,
                transparent 50px,
                black 51px);
        mask-image: radial-gradient(circle 50px at 100% 0%,
                transparent 50px,
                black 51px);
    }

    @media (max-width: 640px) {
        .product-card-notch {
            -webkit-mask-image: radial-gradient(circle 45px at 100% 0%,
                    transparent 45px,
                    black 46px);
            mask-image: radial-gradient(circle 45px at 100% 0%,
                    transparent 45px,
                    black 46px);
        }

        .mainHeroSwiper .swiper-pagination-bullet {
            width: 48px !important;
            height: 48px !important;
            margin: 0 !important;
        }
    }
</style>
<style>
    .mainHeroSwiper .swiper-pagination-bullet {
        width: 48px !important;
        height: 48px !important;
        background: transparent !important;
        opacity: 1 !important;
        margin: 0 24px !important;
        position: relative !important;
        transition: all 0.3s ease;
    }

    .mainHeroSwiper .swiper-pagination-bullet-active {
        background: transparent !important;
        transform: scale(1.2);
    }

    .mainHeroSwiper .swiper-pagination-bullet::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 12px;
        height: 12px;
        background: #ffffff;
        border-radius: 50%;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        cursor: pointer;
    }

    .mainHeroSwiper .swiper-pagination-bullet-active::before {
        background: #66267b;
    }

    @media (max-width: 640px) {
        .mainHeroSwiper .swiper-pagination-bullet {
            margin: 0 !important;
        }

        .mainHeroSwiper .swiper-pagination-bullet::before {
            width: 8px;
            height: 8px;
        }
    }
</style>
@endpush
@section('content')
<!-- hero section -->
<section class="container mx-auto mt-6 px-4 ">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-start">

        <!-- LEFT: Dynamic Swiper Slider -->
        <div class="lg:col-span-3 relative group overflow-hidden ">
            <div class="swiper mainHeroSwiper w-full h-auto">
                <div class="swiper-wrapper">
                    @forelse($mainSliders as $slider)
                    <div class="swiper-slide">
                        <a href="{{ $slider->url ?? '#' }}" aria-label="{{ $slider->title ?? 'Slider Image' }}" class="block w-full h-full">
                            <picture class="block w-full h-full">
                                <source media="(max-width: 767px)" srcset="{{ $slider->mobile_image_url ?? $slider->image_url ?? ''}}">
                                <source media="(min-width: 768px)" srcset="{{ $slider->image_url ?? ''}}">
                                <img src="{{ $slider->image_url ?? ''}}"
                                    alt="{{ $slider->title ?: 'Promotion Slider Image' }}"
                                    class="w-full h-full object-cover rounded-2xl" @if ($loop->first) fetchpriority="high"
                                loading="eager" @else loading="lazy" @endif />
                            </picture>
                        </a>
                    </div>
                    @empty
                    @endforelse
                </div>

                <div class="swiper-pagination !bottom-3 md:!bottom-8"></div>
            </div>
        </div>

        <!-- RIGHT: Side Banner (Dynamic but Static Image) -->
        <div
            class="hidden lg:block lg:col-span-1 h-[450px] overflow-hidden border border-gray-100 rounded-2xl">
            @php $sideBanner = $sidebarSliders->first(); @endphp
            @if ($sideBanner)
            <a href="{{ $sideBanner->url ?? '#' }}" aria-label="{{ $sideBanner->title ?? 'banner Image' }}">
                <img src="{{ $sideBanner->image_url ?? '' }}" alt="{{ $sideBanner->title }}" height="450" width="300"
                    loading="lazy"
                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
            </a>
            @endif
        </div>
    </div>

    <!-- TICKER: Announcement Bar -->
    <div class=" hidden mt-8 mb-10 overflow-hidden bg-white py-4 relative border-y border-gray-50">
        <div class="flex items-center whitespace-nowrap animate-marquee-fast hover:[animation-play-state:paused]">
            <!-- Ticker Content -->
            <div
                class="flex items-center gap-6 md:gap-10 px-4 text-sm md:text-base text-gray-800 tracking-tight font-medium">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-percent text-[#313bc9]"></i>
                    <span>Use Code {{ $setup->promo_code ?? 'BABYOS' }}</span>
                </div>
                <span class="text-gray-900 font-black text-2xl">•</span>
                <div>Call to Order: {{ $setup->phone ?? '+880 00000000' }}</div>

                <span class="text-gray-900 font-black text-2xl">•</span>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast text-[var(--primary-color)]"></i>
                    <span>Inside Charge: {{ $setup->currency ?? 'BDT' }}{{ $setup->inside_charge ?? '0' }} | Outside:
                        {{ $setup->currency ?? 'BDT' }}{{ $setup->outside_charge ?? '0' }}</span>
                </div>
                <span class="text-gray-900 font-black text-2xl">•</span>
            </div>

            <!-- Loop (রিপিট কন্টেন্ট) -->
            <div class="flex items-center gap-6 md:gap-10 px-4 text-sm md:text-base text-gray-800 tracking-tight font-medium"
                aria-hidden="true">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-percent text-[#313bc9]"></i>
                    <span>Use Code {{ $setup->promo_code ?? 'BABYOS' }}</span>
                </div>
                <span class="text-gray-900 font-black text-2xl">•</span>
                <div>Call to Order: {{ $setup->phone ?? '' }}</div>
                <span class="text-gray-900 font-black text-2xl">•</span>
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-truck-fast text-[var(--primary-color)]"></i>
                    <span>Delivery Charges Apply</span>
                </div>
                <span class="text-gray-900 font-black text-2xl">•</span>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCT CATEGORIES SECTION -->
@if (!empty($featureCategory) && count($featureCategory['items']) > 0)
<section class="w-full mx-auto px-4 mb-12">
    <div class="container mx-auto p-0">

        <h2 class="lg:text-2xl text-[20px] font-semibold text-[#041533]   tracking-tight mt-6 text-center">
            {{ $featureCategory['name'] ?? 'Featured Category' }}
        </h2>
        <p class="text-gray-600 text-center mb-4 lg:text-[16px] text-[14px]">Buy Your Desired Products from Featured Categories</p>

        <!-- Categories Grid -->
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-7 gap-3">

            @foreach ($featureCategory['items'] as $tItem)
            @if (data_get($tItem, 'visible') === true)
            <a href="{{ url($tItem['link'] ?? '#') }}"
                class="bg-white pb-[10px]  min-h-[120px] flex flex-col items-center justify-center text-center rounded-lg hover:shadow-md transition-all duration-300 group">

                <!-- Icon / Image -->
                <div class="  mb-2 flex items-center justify-center overflow-hidden">
                    @if(!empty($tItem['image']))
                    <img src="{{ $tItem['image'] }}"
                        alt="{{ $tItem['label'] }}"
                        loading="lazy"
                        onerror="this.style.display='none'"
                        class="w-full h-[100px] lg:h-[160px] md:h-[160px] object-cover transition-transform duration-300 group-hover:scale-110"
                        width="800" height="800">
                    @else
                    <i class="fas fa-layer-group text-2xl text-gray-400"></i>
                    @endif
                </div>

                <!-- Category Name -->
                <h3 class=" text-[#0f172a] text-xl font-normal leading-tight
                        group-hover:text-[var(--primary-color)] transition-colors line-clamp-2 text-[14px] md:text-[16px] lg:text-[16px]" style="font-weight: 400 !important;color: #333;padding-top: 8px;">
                    {{ $tItem['label'] }}
                </h3>

            </a>
            @endif
            @endforeach

        </div>
    </div>
</section>
@elseif (isset($headerCategories) && $headerCategories->count() > 0)
<section class="w-full mx-auto px-4 mb-12">
    <div class="container mx-auto p-0">

        <h2 class="lg:text-2xl text-[20px] font-semibold text-[#041533]   tracking-tight mt-6 text-center">
            Featured Category
        </h2>
        <p class="text-gray-600 text-center mb-4 lg:text-[16px] text-[14px]">Buy Your Desired Products from Featured Categories</p>

        <!-- Categories Grid -->
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-7 gap-3">

            @foreach ($headerCategories as $category)
            <a href="{{ url($category->slug) }}"
                class="bg-white pb-[10px]  min-h-[120px] flex flex-col items-center justify-center text-center rounded-lg hover:shadow-md transition-all duration-300 group">

                <!-- Icon / Image -->
                <div class="  mb-2 flex items-center justify-center overflow-hidden">
                    @if($category->image_url)
                    <img src="{{ $category->image_url }}"
                        alt="{{ $category->name }}"
                        loading="lazy"
                        onerror="this.style.display='none'"
                        class="w-full h-[100px] lg:h-[160px] md:h-[160px] object-cover transition-transform duration-300 group-hover:scale-110">
                    @else
                    <i class="fas fa-layer-group text-2xl text-gray-400"></i>
                    @endif
                </div>

                <!-- Category Name -->
                <h3 class=" text-[#0f172a] text-xl font-normal leading-tight
                        group-hover:text-[var(--primary-color)] transition-colors line-clamp-2 text-[14px] md:text-[16px] lg:text-[16px]" style="font-weight: 400 !important;color: #333;padding-top: 8px;">
                    {{ $category->name }}
                </h3>

            </a>
            @endforeach

        </div>
    </div>
</section>
@endif


@foreach ($productGroups as $group)
<section class=" px-4  ">
    <div class="mb-12 container mx-auto p-0">
        <!-- Header -->
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-[#041533]  tracking-tight">
                {{ $group->name }}
            </h2>

            <a href="{{ route('shop.index', ['group' => $group->slug]) }}">
                <button
                    class="primary-bg primary-bg-hover text-primary text-xs md:text-sm px-4 py-1.5 md:px-5 md:py-2 rounded transition-colors shadow-sm">
                    View all
                </button>
            </a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            @foreach ($group->products as $product)
            @php
            $currency = $setup->currency ?? '৳';
            $isVar = $product->type !== 'single';

            $regularPrice = (float) $product->regular_price;
            $salePrice = (float) $product->sale_price;

            $minPrice = 0;
            $maxPrice = 0;

            if ($isVar && $product->variations->count() > 0) {
            $minPrice = $product->variations->min('regular_price');
            $maxPrice = $product->variations->max('regular_price');
            }
            @endphp

            <div
                class="flex bg-white border border-gray-200 rounded-lg p-3 md:p-4 hover:shadow-md transition-all group">
                <!-- Left: Product Image -->
                <div
                    class="w-[110px] md:w-[75px] flex-shrink-0 relative overflow-hidden flex items-center justify-center bg-[#F9F9F9] rounded-md">
                    <a href="{{ url($product->slug) }}" class="block w-full h-full">
                        <img src="{{ $product->thumbnail_url }}"
                            alt="{{ $product->title }}"
                            class="w-full aspect-square object-cover transform group-hover:scale-110 transition-transform duration-500  ">
                    </a>
                </div>

                <!-- Right: Content Area -->
                <div class="flex-1 pl-4 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm md:text-base font-bold text-gray-900 leading-snug line-clamp-2 mb-2">
                            <a href="{{ url($product->slug) }}" class="hover:text-[#016738]">
                                {{ $product->title }}
                            </a>
                        </h3>

                        <!-- Price Logic (Corrected Fields) -->
                        <div class="text-[#016738] font-black text-sm md:text-lg flex flex-wrap items-center gap-2">
                            @php $isLeft = ($setup->currency_position ?? 'left') == 'left'; @endphp

                            @if ($isVar && $minPrice > 0)
                            {{-- ভ্যারিয়েশন: Lowest - Highest Regular Price --}}
                            <span>
                                {{ $isLeft ? $currency : '' }}{{ number_format($minPrice, 0) }}{{ !$isLeft ? $currency : '' }} –
                                {{ $isLeft ? $currency : '' }}{{ number_format($maxPrice, 0) }}{{ !$isLeft ? $currency : '' }}
                            </span>
                            @else
                            {{-- সিঙ্গেল: Discount থাকলে কাটা দামসহ দেখাবে --}}
                            @if ($salePrice < $regularPrice && $salePrice> 0)
                                <span class="line-through text-gray-600 text-xs md:text-sm font-bold">
                                    {{ $isLeft ? $currency : '' }}{{ number_format($regularPrice, 0) }}{{ !$isLeft ? $currency : '' }}
                                </span>
                                <span>
                                    {{ $isLeft ? $currency : '' }}{{ number_format($salePrice, 0) }}{{ !$isLeft ? $currency : '' }}
                                </span>
                                @elseif($regularPrice > 0)
                                <span>
                                    {{ $isLeft ? $currency : '' }}{{ number_format($regularPrice, 0) }}{{ !$isLeft ? $currency : '' }}
                                </span>
                                @endif
                                @endif
                        </div>
                    </div>

                    <!-- Button -->

                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endforeach

<!-- LATEST OFFERS SECTION  -->
@if($latestOffers->isNotEmpty())
<section class="w-full bg-[#fcfcfc] px-4">
    <div class="p-0 container mx-auto">

        <!-- Section Title -->
        <h2 class="text-xl md:text-2xl text-[20px] font-bold text-[#041533] lg:mb-4 mb-3 tracking-tight">
            Latest Offers
        </h2>

        <!-- Offers Grid -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-6">
            @foreach ($latestOffers as $product)
            <a href="{{ url($product->slug) }}" class="block group">
                <div
                    class="bg-[#fcfcfc] flex items-center  border border-[#F0E9F2] shadow-md transition-all duration-300 rounded-sm overflow-hidden hover:shadow-xl">

                    <!-- Left Part: Product Image -->
                    <div
                        class="w-[35%] h-full flex items-center justify-center border-r border-[#F0E9F2] p-2 md:p-4 bg-white relative">
                        <img src="{{ $product->thumbnail_95_url ?? '' }}" height="150" width="150" alt="{{ $product->title }}"
                            loading="lazy"
                            class="max-h-full object-contain group-hover:scale-110 transition-transform duration-500" />
                    </div>

                    <!-- Right Part: Product Details -->
                    <div class="w-[65%] p-2 md:p-6 flex flex-col justify-center">
                        <!-- Price Section (Dynamic) -->
                        @php $isL = ($setup->currency_position ?? 'left') == 'left'; @endphp

                        <div class="flex flex-col sm:flex-row sm:items-center gap-0.5 md:gap-3 mb-1">
                            {{-- বর্তমান অফার প্রাইস --}}
                            <span class="text-[#9d174d] font-bold text-[13px] md:text-xl leading-none">
                                {{ $isL ? $setup->currency : '' }} {{ number_format($product->sale_price) }} {{ !$isL ? $setup->currency : '' }}
                            </span>

                            {{-- আগের রেগুলার প্রাইস --}}
                            <span class="text-[#52525b] line-through text-[10px] md:text-sm font-semibold leading-none">
                                {{ $isL ? $setup->currency : '' }} {{ number_format($product->regular_price) }} {{ !$isL ? $setup->currency : '' }}
                            </span>
                        </div>
                        <!-- Title (Dynamic) -->
                        <h3
                            class="text-[#041533] font-bold text-[11px] md:text-[15px] leading-tight md:leading-[1.3] mb-1 line-clamp-2 group-hover:text-[#9d174d] transition-colors">
                            {{ $product->title }}
                        </h3>

                        <!-- Subtitle/Short Description (Dynamic) -->
                        <p class="text-[#4b5563] text-[9px] md:text-[12px] font-medium truncate hidden sm:block">
                            {{ $product->short_description ?? 'Quality Product' }}
                        </p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
<!-- OUR FEATURED PRODUCTS SECTION -->
<section class="w-full bg-[#fcfcfc] px-4 py-10">
    <div class="p-0 container mx-auto  ">
        <!-- Section Title -->
        <h2 class="lg:text-2xl text-[20px] font-semibold text-[#041533] lg:mb-4 mb-3 tracking-tight">
            Our Featured Products
        </h2>

        <!-- Products Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">

            @foreach ($popularProducts as $product)
            <x-template1.product-card :product="$product" />
            @endforeach

        </div>
    </div>
</section>
<!-- NEW ARRIVAL SECTION -->
{{-- <section class="px-4 mb-12">
        <div class="p-0 container mx-auto   ">
        <!-- Section Title -->
        <h2 class="text-xl md:text-2xl font-bold text-[#041533] mb-4 mt-12 tracking-tight">
            New Arrival
        </h2>

        <!-- Grid: 2 columns on mobile, 4 columns on desktop -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
            @foreach ($newArrivals as $product)
                <a href="{{ url($product->slug) }}" class="block group">
<div
    class="flex items-center h-24 md:h-[140px] bg-[#f9f9f9] border border-[#F0E9F2] shadow-md md:shadow-lg transition-all hover:shadow-xl rounded-sm overflow-hidden">

    <!-- Left side: Product Image -->
    <div
        class="w-[35%] h-full flex items-center justify-center border-r border-[#F0E9F2] p-1 md:p-4 bg-white relative overflow-hidden">
        <img src="{{ $product->thumbnail_url ?? '' }}" height="140" width="100" alt="{{ $product->title }}"
            loading="lazy"
            class="max-h-full object-contain transition-transform duration-500 group-hover:scale-110" />
    </div>

    <!-- Right side: Product Details -->
    <div class="w-[65%] p-2 md:p-5 flex flex-col justify-center">
        <!-- Price -->
        <div class="flex items-center mb-0.5 md:mb-1">
            <span class="text-[#041533] font-bold text-[13px] md:text-lg">
                {{ $setup->currency ?? '৳' }}{{ number_format($product->sale_price) }}
            </span>
        </div>

        <!-- Product Title -->
        <h3
            class="text-[#041533] font-bold text-[11px] md:text-[15px] leading-tight md:leading-[1.3] mb-1 line-clamp-2 group-hover:text-[var(--primary-color)] transition-colors">
            {{ $product->title }}
        </h3>

        <!-- Brand or Subtitle (Dynamic) -->
        <p class="text-[#374151] text-[9px] md:text-xs font-normal truncate hidden sm:block">
            {{ $product->brand->name ?? 'Premium Quality' }}
        </p>
    </div>
</div>
</a>
@endforeach
</div>
</div>
</section> --}}
<!-- ABOUT / SEO TEXT SECTION -->
@if($homePageData->description)
<section class="w-full bg-[#fcfcfc] px-4 font-manrope">
    <div class="p-0 container mx-auto    ">
        <div
            class=" bg-gray-100  border border-gray-200 rounded-lg px-6 py-6
                    prose prose-slate max-w-none
                    prose-headings:text-[#041533] prose-headings:font-bold
                    prose-h2:text-[24px] md:prose-h2:text-[28px] prose-h2:tracking-tight prose-h2:mb-4
                    prose-h3:text-[22px] md:prose-h3:text-[24px] prose-h3:mb-4
                    prose-p:text-[#4b5563] prose-p:text-base prose-p:leading-[1.7] prose-p:text-justify md:prose-p:text-left prose-p:mb-8">

            {!! $homePageData->description !!}

        </div>
    </div>
</section>
@endif
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (document.querySelector('.mainHeroSwiper')) {
            new Swiper('.mainHeroSwiper', {
                loop: true,
                effect: 'fade',
                speed: 1000,
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
            });
        }
    });
</script>
@endpush