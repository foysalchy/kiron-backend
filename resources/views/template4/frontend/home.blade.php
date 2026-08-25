@extends('template4.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.index-meta', ['setup' => $setup])
@endsection
@push('styles')
    <style>
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
        }
    </style>
    <style>
        .mainHeroSwiper .swiper-pagination-bullet {
            width: 12px !important;
            height: 12px !important;
            background: #ffffff !important;
            opacity: 1 !important;
            margin: 0 12px !important;
            position: relative !important;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            transition: all 0.3s ease;
        }

        .mainHeroSwiper .swiper-pagination-bullet-active {
            background: #66267b !important;
            transform: scale(1.2);
        }

        .mainHeroSwiper .swiper-pagination-bullet::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 48px;
            height: 48px;
            background: transparent;
            cursor: pointer;
        }
    </style>
@endpush
@section('content')
    <!-- hero section -->
    <section class="container mx-auto mt-6 px-4 ">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-start">

            <!-- LEFT: Dynamic Swiper Slider -->
            <div class="lg:col-span-3 relative group overflow-hidden shadow-lg">
                <div class="swiper mainHeroSwiper w-full h-[300px] md:h-[450px] lg:h-[450px]">
                    <div class="swiper-wrapper">
                        @forelse($mainSliders as $slider)
                            <div class="swiper-slide">
                                <a href="{{ $slider->url ?? '#' }}" aria-label="{{ $slider->title ?? 'Slider Image' }}">
                                    <img src="{{ $slider->image_url ?? ''}}"
                                        alt="{{ $slider->title ?: 'Promotion Slider Image' }}" height="400" width="1200"
                                        class="w-full h-full object-cover" @if ($loop->first) fetchpriority="high"
                                        loading="eager" @else loading="lazy" @endif />
                                </a>
                            </div>
                        @empty
                        @endforelse
                    </div>

                    <div class="swiper-pagination !bottom-8"></div>
                </div>
            </div>

            <!-- RIGHT: Side Banner (Dynamic but Static Image) -->
            <div
                class="hidden lg:block lg:col-span-1 h-[450px] overflow-hidden shadow-lg border border-gray-100 rounded-2xl">
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
        <div class="mt-8 mb-10 overflow-hidden bg-white py-4 relative border-y border-gray-50">
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
    {{-- <!-- PRODUCT CATEGORIES SECTION -->
    <section class="w-full mx-auto bg-[#fcfcfc] px-4 ">
        <div class="p-0 container mx-auto  ">
            <h2 class="text-2xl font-semibold text-[#0f172a] mb-12">
                Product Categories
            </h2>

            <!-- Categories Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-y-8 gap-x-6">
                @foreach ($headerCategories as $category)
                @php
                $index = $loop->index;

                $isReverse = $index % 2 != 0;

                $isTopNotch = $index >= 6;

                if (!$isTopNotch) {
                $notchClass = $isReverse ? 'notch-bottom-left' : 'notch-bottom-right';
                } else {
                $notchClass = $isReverse ? 'notch-top-left' : 'notch-top-right';
                }
                @endphp

                <!-- Dynamic Category Card -->
                <a href="{{ route('category.products', $category->slug) }}"
                    class="notch-border hover:-translate-y-1 transition-transform block group">
                    <div
                        class="bg-white p-4 h-32 flex {{ $isReverse ? 'flex-row-reverse text-left' : 'flex-row text-right' }} items-center justify-between {{ $notchClass }} border-gray-50 shadow-sm group-hover:shadow-md transition-all">

                        <div class="w-16 h-16 shrink-0 {{ !$isTopNotch ? 'mb-4' : 'mt-4' }}">
                            @if($category->image_url)
                            <img src="{{ $category->image_url }}" height="64" width="64" aria-label="category image"
                                loading="lazy" alt="{{ $category->name }}" class="w-full h-full object-contain">
                            @else
                            <i class="fas fa-layer-group text-2xl md:text-4xl text-gray-400"></i>
                            @endif
                        </div>

                        <div class="{{ !$isTopNotch ? 'mb-6' : 'mt-6' }}">
                            <h3 class="font-bold text-[#0f172a] text-sm md:text-[15px] leading-tight">
                                {{ $category->name }}
                            </h3>
                            <p class="text-[11px] text-gray-500 mt-1">
                                {{ $category->products_count ?? '12,203' }} Items
                            </p>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section> --}}
    <!-- PRODUCT CATEGORIES SECTION -->
    <section class="w-full mx-auto  px-4">
        <div class="container mx-auto py-0">
            <h2 class="text-xl md:text-2xl font-bold text-[#0f172a] mb-4">
                Product Categories
            </h2>

            <!-- Categories Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($headerCategories as $category)
                    <a href="{{ route('category.products', $category->slug) }}"
                        class="bg-white shadow-sm  p-4 h-24 flex items-center justify-between hover:shadow-sm transition-all group">

                        

                        <!-- 2. Name on the Far RIGHT -->
                        <div class="flex-1 text-left ml-4">
                            <h3
                                class="font-bold text-[#0f172a] text-sm md:text-base leading-tight group-hover:text-[var(--primary-color)] transition-colors">
                                {{ $category->name }}
                            </h3>
                        </div>
                        <!-- 1. Image on the Far LEFT -->
                        <div class="w-14 md:w-18 h-14 md:h-18 shrink-0 flex items-center justify-end overflow-hidden">
                            @if($category->image_url)
                                <img src="{{ $category->image_url }}" alt="{{ $category->name }}" height="" width=""
                                    onerror="this.src='{{ $category->image_url }}'"
                                    class="w-full h-full object-contain">
                            @else
                                <i class="fas fa-layer-group text-2xl text-gray-400"></i>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    <!-- LATEST OFFERS SECTION  -->
    <section class="p-0 container mx-auto   px-4 ">
        <!-- Section Title -->
        <h2 class="text-xl md:text-2xl font-bold text-[#041533] mb-6 md:mb-10 tracking-tight">
            Latest Offers
        </h2>

        <!-- Offers Grid -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-6">
            @foreach ($latestOffers as $product)
                <a href="{{ route('product.details', $product->slug) }}" class="block group">
                    <div
                        class="bg-[#fcfcfc] flex items-center h-28 md:h-40 border border-[#F0E9F2] shadow-md transition-all duration-300 rounded-sm overflow-hidden hover:shadow-xl">

                        <!-- Left Part: Product Image -->
                        <div
                            class="w-[35%] h-full flex items-center justify-center border-r border-[#F0E9F2] p-2 md:p-4 bg-white relative">
                            <img src="{{ $product->thumbnail_url ?? '' }}" height="150" width="150" alt="{{ $product->title }}"
                                loading="lazy"
                                class="max-h-full object-contain group-hover:scale-110 transition-transform duration-500" />
                        </div>

                        <!-- Right Part: Product Details -->
                        <div class="w-[65%] p-2 md:p-6 flex flex-col justify-center">
                            <!-- Price Section (Dynamic) -->
                            <div class="flex flex-col sm:flex-row sm:items-center gap-0.5 md:gap-3 mb-1">
                                {{-- বর্তমান অফার প্রাইস --}}
                                <span class="text-[#9d174d] font-bold text-[13px] md:text-xl leading-none">
                                    {{ $setup->currency ?? 'BDT' }} {{ number_format($product->sale_price) }}
                                </span>

                                {{-- আগের রেগুলার প্রাইস --}}
                                <span class="text-[#52525b] line-through text-[10px] md:text-sm font-semibold leading-none">
                                    {{ $setup->currency ?? 'BDT' }} {{ number_format($product->regular_price) }}
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
    </section>
    <!-- OUR FEATURED PRODUCTS SECTION -->
    <section class="w-full bg-[#fcfcfc] px-4">
        <div class="p-0 container mx-auto  ">
            <!-- Section Title -->
            <h2 class="text-2xl font-semibold text-[#041533] mb-4 tracking-tight">
                Our Featured Products
            </h2>

            <!-- Products Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">

                @foreach ($popularProducts as $product)
                    <x-template1.product-card :product="$product" />
                @endforeach

            </div>
        </div>
    </section>
    <!-- NEW ARRIVAL SECTION -->
    <section class="p-0 container mx-auto   px-4 ">
        <!-- Section Title -->
        <h2 class="text-xl md:text-2xl font-bold text-[#041533] mb-6 md:mb-10 tracking-tight">
            New Arrival
        </h2>

        <!-- Grid: 2 columns on mobile, 4 columns on desktop -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
            @foreach ($newArrivals as $product)
                <a href="{{ route('product.details', $product->slug) }}" class="block group">
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
    </section>
    <!-- ABOUT / SEO TEXT SECTION -->
    @if($homePageData->description)
        <section class="w-full bg-[#fcfcfc] px-4 font-manrope">
            <div class="p-0 container mx-auto  ">
                <div
                    class="prose prose-slate max-w-none
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