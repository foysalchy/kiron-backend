@extends('template4.layouts.front')
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

        /* ১ম সারি (Notch Bottom) */
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

        /* ২য় সারি (Notch Top) */
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
@endpush
@section('content')
    <!-- hero section -->
    <section class="container mx-auto mt-6 px-4 font-manrope">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-start">

            <!-- LEFT: Dynamic Swiper Slider -->
            <div
                class="lg:col-span-3 relative group overflow-hidden shadow-lg [&_.swiper-pagination-bullet]:!w-4 [&_.swiper-pagination-bullet]:!h-4 [&_.swiper-pagination-bullet]:!rounded-full [&_.swiper-pagination-bullet]:!bg-white [&_.swiper-pagination-bullet]:!opacity-100 [&_.swiper-pagination-bullet]:mx-2 [&_.swiper-pagination-bullet]:shadow-md [&_.swiper-pagination-bullet]:transition-all [&_.swiper-pagination-bullet-active]:!bg-[#66267b] [&_.swiper-pagination-bullet-active]:scale-110">
                <div class="swiper mainHeroSwiper w-full h-[300px] md:h-[450px] lg:h-[450px]">
                    <div class="swiper-wrapper">
                        @forelse($mainSliders as $slider)
                            <div class="swiper-slide">
                                <a href="{{ $slider->url ?? '#' }}">
                                    <img src="{{ $slider->image_url }}" alt="{{ $slider->title }}"
                                        class="w-full h-full object-cover" />
                                </a>
                            </div>
                        @empty
                            <div class="swiper-slide">
                                <img src="{{ asset('images/babyshop/images/hero1.jpg') }}"
                                    class="w-full h-full object-cover" />
                            </div>
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
                    <a href="{{ $sideBanner->url ?? '#' }}">
                        <img src="{{ $sideBanner->image_url }}" alt="{{ $sideBanner->title }}"
                            class="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
                    </a>
                @else
                    <img src="{{ asset('images/babyshop/images/right1.jpg') }}" class="w-full h-full object-cover" />
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
                        <i class="fa-solid fa-truck-fast text-[#66267b]"></i>
                        <span>Inside Charge: {{ $setup->currency?? 'BDT' }}{{ $setup->inside_charge ?? '0' }} | Outside:
                            {{ $setup->currency?? 'BDT' }}{{ $setup->outside_charge ?? '0' }}</span>
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
                        <i class="fa-solid fa-truck-fast text-[#66267b]"></i>
                        <span>Delivery Charges Apply</span>
                    </div>
                    <span class="text-gray-900 font-black text-2xl">•</span>
                </div>
            </div>
        </div>
    </section>
<!-- PRODUCT CATEGORIES SECTION -->
<section class="w-full mx-auto bg-[#fcfcfc] px-4 font-manrope">
    <div class="container mx-auto py-4 md:py-10">
        <h2 class="text-2xl font-semibold text-[#0f172a] mb-12">
            Product Categories
        </h2>

        <!-- Categories Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-y-8 gap-x-6">
            @foreach($headerCategories as $category)
                @php
                    $index = $loop->index; // ০ থেকে শুরু

                    // ১. ইমেজ কি ডানে হবে নাকি বামে? (জোড় সংখ্যায় বামে, বিজোড় সংখ্যায় ডানে)
                    $isReverse = ($index % 2 != 0);

                    // ২. নচ কি উপরে হবে নাকি নিচে? (প্রথম ৬টি নিচে, পরের গুলো উপরে)
                    $isTopNotch = ($index >= 6);

                    // ৩. সঠিক ক্লাস সিলেক্ট করা
                    if (!$isTopNotch) {
                        $notchClass = $isReverse ? 'notch-bottom-left' : 'notch-bottom-right';
                    } else {
                        $notchClass = $isReverse ? 'notch-top-left' : 'notch-top-right';
                    }
                @endphp

                <!-- Dynamic Category Card -->
                <a href="" class="notch-border hover:-translate-y-1 transition-transform block group">
                    <div class="bg-white p-4 h-32 flex {{ $isReverse ? 'flex-row-reverse text-left' : 'flex-row text-right' }} items-center justify-between {{ $notchClass }} border-gray-50 shadow-sm group-hover:shadow-md transition-all">

                        {{-- ক্যাটাগরি ইমেজ --}}
                        <div class="w-16 h-16 shrink-0 {{ !$isTopNotch ? 'mb-4' : 'mt-4' }}">
                            <img src="{{ $category->image_url ?? asset('images/babyshop/images/card.png') }}"
                                 alt="{{ $category->name }}"
                                 class="w-full h-full object-contain">
                        </div>

                        {{-- টেক্সট ডিটেইলস --}}
                        <div class="{{ !$isTopNotch ? 'mb-6' : 'mt-6' }}">
                            <h3 class="font-bold text-[#0f172a] text-sm md:text-[15px] leading-tight">
                                {{ $category->name }}
                            </h3>
                            <p class="text-[11px] text-gray-500 mt-1">
                                {{-- যদি আপনার রিলেশন থাকে তবে আইটেম সংখ্যা দেখাবে --}}
                                {{ $category->products_count ?? '12,203' }} Items
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

    <!-- LATEST OFFERS SECTION -->
    <section class="container mx-auto py-4 md:py-10  px-4">
        <!-- Section Title -->
        <h2 class="text-xl md:text-2xl font-semibold text-[#041533] mb-6 md:mb-10 tracking-tight">
            Latest Offers
        </h2>

        <!-- Offers Grid (2 columns on mobile, 3 columns on desktop) -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-6">
            <!-- Single Offer Card (Repeated) -->
            <div
                class="bg-[#fcfcfc] flex items-center h-28 md:h-40 border border-[#F0E9F2] shadow-md md:shadow-xl transition-shadow duration-300 rounded-sm overflow-hidden">
                <!-- Left Part: Image -->
                <div class="w-[35%] h-full flex items-center justify-center border-r border-[#F0E9F2] p-2 md:p-4 bg-white">
                    <img src="{{ asset('images/babyshop/images/dress.png') }}" alt="Product Image"
                        class="max-h-full object-contain" />
                </div>

                <!-- Right Part: Product Details -->
                <div class="w-[65%] p-2 md:p-6 flex flex-col justify-center">
                    <!-- Price Section -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-0.5 md:gap-3 mb-1">
                        <span class="text-[#f1468b] font-bold text-[13px] md:text-xl leading-none">BDT 2056</span>
                        <span class="text-[#999999] line-through text-xs md:text-base font-semibold leading-none">BDT
                            3000</span>
                    </div>

                    <!-- Title -->
                    <h3
                        class="text-[#041533] font-medium text-[11px] md:text-base leading-tight md:leading-[1.3] mb-1 line-clamp-2">
                        Luxury Essentials for Growing Families
                    </h3>

                    <!-- Subtitle/Category (Hidden on small mobile to save space) -->
                    <p class="text-[#777777] text-[9px] md:text-sm font-medium truncate hidden sm:block">
                        Feeding Item
                    </p>
                </div>
            </div>

            <!-- Single Offer Card (Repeated) -->
            <div
                class="bg-[#fcfcfc] flex items-center h-28 md:h-40 border border-[#F0E9F2] shadow-md md:shadow-xl transition-shadow duration-300 rounded-sm overflow-hidden">
                <!-- Left Part: Image -->
                <div class="w-[35%] h-full flex items-center justify-center border-r border-[#F0E9F2] p-2 md:p-4 bg-white">
                    <img src="{{ asset('images/babyshop/images/dress.png') }}" alt="Product Image"
                        class="max-h-full object-contain" />
                </div>

                <!-- Right Part: Product Details -->
                <div class="w-[65%] p-2 md:p-6 flex flex-col justify-center">
                    <!-- Price Section -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-0.5 md:gap-3 mb-1">
                        <span class="text-[#f1468b] font-bold text-[13px] md:text-xl leading-none">BDT 2056</span>
                        <span class="text-[#999999] line-through text-xs md:text-base font-semibold leading-none">BDT
                            3000</span>
                    </div>

                    <!-- Title -->
                    <h3
                        class="text-[#041533] font-medium text-[11px] md:text-base leading-tight md:leading-[1.3] mb-1 line-clamp-2">
                        Luxury Essentials for Growing Families
                    </h3>

                    <!-- Subtitle/Category (Hidden on small mobile to save space) -->
                    <p class="text-[#777777] text-[9px] md:text-sm font-medium truncate hidden sm:block">
                        Feeding Item
                    </p>
                </div>
            </div>
            <!-- Single Offer Card (Repeated) -->
            <div
                class="bg-[#fcfcfc] flex items-center h-28 md:h-40 border border-[#F0E9F2] shadow-md md:shadow-xl transition-shadow duration-300 rounded-sm overflow-hidden">
                <!-- Left Part: Image -->
                <div class="w-[35%] h-full flex items-center justify-center border-r border-[#F0E9F2] p-2 md:p-4 bg-white">
                    <img src="{{ asset('images/babyshop/images/dress.png') }}" alt="Product Image"
                        class="max-h-full object-contain" />
                </div>

                <!-- Right Part: Product Details -->
                <div class="w-[65%] p-2 md:p-6 flex flex-col justify-center">
                    <!-- Price Section -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-0.5 md:gap-3 mb-1">
                        <span class="text-[#f1468b] font-bold text-[13px] md:text-xl leading-none">BDT 2056</span>
                        <span class="text-[#999999] line-through text-xs md:text-base font-semibold leading-none">BDT
                            3000</span>
                    </div>

                    <!-- Title -->
                    <h3
                        class="text-[#041533] font-medium text-[11px] md:text-base leading-tight md:leading-[1.3] mb-1 line-clamp-2">
                        Luxury Essentials for Growing Families
                    </h3>

                    <!-- Subtitle/Category (Hidden on small mobile to save space) -->
                    <p class="text-[#777777] text-[9px] md:text-sm font-medium truncate hidden sm:block">
                        Feeding Item
                    </p>
                </div>
            </div>
            <!-- Single Offer Card (Repeated) -->
            <div
                class="bg-[#fcfcfc] flex items-center h-28 md:h-40 border border-[#F0E9F2] shadow-md md:shadow-xl transition-shadow duration-300 rounded-sm overflow-hidden">
                <!-- Left Part: Image -->
                <div class="w-[35%] h-full flex items-center justify-center border-r border-[#F0E9F2] p-2 md:p-4 bg-white">
                    <img src="{{ asset('images/babyshop/images/dress.png') }}" alt="Product Image"
                        class="max-h-full object-contain" />
                </div>

                <!-- Right Part: Product Details -->
                <div class="w-[65%] p-2 md:p-6 flex flex-col justify-center">
                    <!-- Price Section -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-0.5 md:gap-3 mb-1">
                        <span class="text-[#f1468b] font-bold text-[13px] md:text-xl leading-none">BDT 2056</span>
                        <span class="text-[#999999] line-through text-xs md:text-base font-semibold leading-none">BDT
                            3000</span>
                    </div>

                    <!-- Title -->
                    <h3
                        class="text-[#041533] font-medium text-[11px] md:text-base leading-tight md:leading-[1.3] mb-1 line-clamp-2">
                        Luxury Essentials for Growing Families
                    </h3>

                    <!-- Subtitle/Category (Hidden on small mobile to save space) -->
                    <p class="text-[#777777] text-[9px] md:text-sm font-medium truncate hidden sm:block">
                        Feeding Item
                    </p>
                </div>
            </div>
        </div>
    </section>
    <!-- OUR FEATURED PRODUCTS SECTION -->
    <section class="w-full bg-[#fcfcfc] px-4">
        <div class="container mx-auto py-4 md:py-10">
            <!-- Section Title -->
            <h2 class="text-2xl font-semibold text-[#041533] mb-12 tracking-tight">
                Our Featured Products
            </h2>

            <!-- Products Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                    <div class="relative">
                        <div
                            class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                            <div class="w-full h-full p-8 flex items-center justify-center">
                                <img src="{{ asset('images/babyshop/images/girl.png') }}" alt="Product Image"
                                    class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                            </div>
                        </div>

                        <!-- উইশলিস্ট বাটন -->
                        <div class="absolute -top-1 -right-1">
                            <div class="bg-white p-1 rounded-full">
                                <button
                                    class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                    aria-label="Add to Wishlist">
                                    <i class="fa-regular fa-heart text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1 p-4">
                        <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                            Luxury Essentials for Growing Families
                        </h3>

                        <!-- Price Section inspired by the image -->
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                            <!-- Prices -->
                            <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">{{ $setup->currency?? 'BDT' }}1,040</span>
                            <span class="text-[#999999] text-sm md:text-base line-through">{{ $setup->currency?? 'BDT' }}1,340</span>

                            <div class="basis-full h-0 sm:hidden"></div>

                            <!-- Discount Percentage Badge -->
                            <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                -22%
                            </span>
                        </div>
                        <button
                            class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                            Add To Cart
                        </button>
                    </div>
                </div>
                <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                    <div class="relative">
                        <div
                            class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                            <div class="w-full h-full p-8 flex items-center justify-center">
                                <img src="{{ asset('images/babyshop/images/girl.png') }}" alt="Product Image"
                                    class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                            </div>
                        </div>

                        <!-- উইশলিস্ট বাটন -->
                        <div class="absolute -top-1 -right-1">
                            <div class="bg-white p-1 rounded-full">
                                <button
                                    class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                    aria-label="Add to Wishlist">
                                    <i class="fa-regular fa-heart text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1 p-4">
                        <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                            Luxury Essentials for Growing Families
                        </h3>

                        <!-- Price Section inspired by the image -->
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                            <!-- Prices -->
                            <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">{{ $setup->currency?? 'BDT' }}1,040</span>
                            <span class="text-[#999999] text-sm md:text-base line-through">{{ $setup->currency?? 'BDT' }}1,340</span>

                            <div class="basis-full h-0 sm:hidden"></div>

                            <!-- Discount Percentage Badge -->
                            <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                -22%
                            </span>
                        </div>
                        <button
                            class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                            Add To Cart
                        </button>
                    </div>
                </div>
                <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                    <div class="relative">
                        <div
                            class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                            <div class="w-full h-full p-8 flex items-center justify-center">
                                <img src="{{ asset('images/babyshop/images/girl.png') }}" alt="Product Image"
                                    class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                            </div>
                        </div>

                        <!-- উইশলিস্ট বাটন -->
                        <div class="absolute -top-1 -right-1">
                            <div class="bg-white p-1 rounded-full">
                                <button
                                    class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                    aria-label="Add to Wishlist">
                                    <i class="fa-regular fa-heart text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1 p-4">
                        <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                            Luxury Essentials for Growing Families
                        </h3>

                        <!-- Price Section inspired by the image -->
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                            <!-- Prices -->
                            <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">{{ $setup->currency?? 'BDT' }}1,040</span>
                            <span class="text-[#999999] text-sm md:text-base line-through">{{ $setup->currency?? 'BDT' }}1,340</span>

                            <div class="basis-full h-0 sm:hidden"></div>

                            <!-- Discount Percentage Badge -->
                            <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                -22%
                            </span>
                        </div>
                        <button
                            class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                            Add To Cart
                        </button>
                    </div>
                </div>
                <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                    <div class="relative">
                        <div
                            class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                            <div class="w-full h-full p-8 flex items-center justify-center">
                                <img src="{{ asset('images/babyshop/images/girl.png') }}" alt="Product Image"
                                    class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                            </div>
                        </div>

                        <!-- উইশলিস্ট বাটন -->
                        <div class="absolute -top-1 -right-1">
                            <div class="bg-white p-1 rounded-full">
                                <button
                                    class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                    aria-label="Add to Wishlist">
                                    <i class="fa-regular fa-heart text-xl"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1 p-4">
                        <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                            Luxury Essentials for Growing Families
                        </h3>

                        <!-- Price Section inspired by the image -->
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                            <!-- Prices -->
                            <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">{{ $setup->currency?? 'BDT' }}1,040</span>
                            <span class="text-[#999999] text-sm md:text-base line-through">{{ $setup->currency?? 'BDT' }}1,340</span>

                            <div class="basis-full h-0 sm:hidden"></div>

                            <!-- Discount Percentage Badge -->
                            <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                -22%
                            </span>
                        </div>
                        <button
                            class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                            Add To Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- NEW ARRIVAL SECTION -->
    <section class="container mx-auto py-4 md:py-10 px-4">
        <!-- Section Title -->
        <h2 class="text-xl md:text-2xl font-semibold text-[#041533] mb-6 md:mb-10 tracking-tight">
            New Arrival
        </h2>

        <!-- Grid: 2 columns on mobile, 4 columns on desktop -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">
            <!-- Single Horizontal Product Card -->
            <div
                class="flex items-center h-20 md:h-[140px] bg-[#f9f9f9] border border-[#F0E9F2] shadow-md md:shadow-lg transition-all hover:shadow-xl group cursor-pointer rounded-sm overflow-hidden">
                <!-- Left side: Product Image -->
                <div class="w-[35%] h-full flex items-center justify-center border-r border-[#F0E9F2] p-2 md:p-4 bg-white">
                    <img src="{{ asset('images/babyshop/images/dress.png') }}" alt="New Arrival Product"
                        class="max-h-full object-contain transition-transform duration-500 group-hover:scale-110" />
                </div>

                <!-- Right side: Product Details -->
                <div class="w-[65%] p-2 md:p-5 flex flex-col justify-center">
                    <!-- Price -->
                    <div class="flex items-center mb-0.5 md:mb-1">
                        <span class="text-[#041533] font-bold text-[13px] md:text-lg">BDT 2056</span>
                    </div>

                    <!-- Product Title -->
                    <h3 class="text-[#041533] font-semibold text-[11px] md:text-lg leading-tight line-clamp-2">
                        Princess Dress
                    </h3>

                    <!-- Category/Subtitle (Optional, hidden on mobile to keep it clean) -->
                    <p class="text-[#7a818c] text-xs md:text-sm font-normal hidden sm:block">
                        Feeding Item
                    </p>
                </div>
            </div>

            <!-- Single Horizontal Product Card -->
            <div
                class="flex items-center h-20 md:h-[140px] bg-[#f9f9f9] border border-[#F0E9F2] shadow-md md:shadow-lg transition-all hover:shadow-xl group cursor-pointer rounded-sm overflow-hidden">
                <!-- Left side: Product Image -->
                <div class="w-[35%] h-full flex items-center justify-center border-r border-[#F0E9F2] p-2 md:p-4 bg-white">
                    <img src="{{ asset('images/babyshop/images/dress.png') }}" alt="New Arrival Product"
                        class="max-h-full object-contain transition-transform duration-500 group-hover:scale-110" />
                </div>

                <!-- Right side: Product Details -->
                <div class="w-[65%] p-2 md:p-5 flex flex-col justify-center">
                    <!-- Price -->
                    <div class="flex items-center mb-0.5 md:mb-1">
                        <span class="text-[#041533] font-bold text-[13px] md:text-lg">BDT 2056</span>
                    </div>

                    <!-- Product Title -->
                    <h3 class="text-[#041533] font-semibold text-[11px] md:text-lg leading-tight line-clamp-2">
                        Princess Dress
                    </h3>

                    <!-- Category/Subtitle (Optional, hidden on mobile to keep it clean) -->
                    <p class="text-[#7a818c] text-xs md:text-sm font-normal hidden sm:block">
                        Feeding Item
                    </p>
                </div>
            </div>
            <!-- Single Horizontal Product Card -->
            <div
                class="flex items-center h-20 md:h-[140px] bg-[#f9f9f9] border border-[#F0E9F2] shadow-md md:shadow-lg transition-all hover:shadow-xl group cursor-pointer rounded-sm overflow-hidden">
                <!-- Left side: Product Image -->
                <div class="w-[35%] h-full flex items-center justify-center border-r border-[#F0E9F2] p-2 md:p-4 bg-white">
                    <img src="{{ asset('images/babyshop/images/dress.png') }}" alt="New Arrival Product"
                        class="max-h-full object-contain transition-transform duration-500 group-hover:scale-110" />
                </div>

                <!-- Right side: Product Details -->
                <div class="w-[65%] p-2 md:p-5 flex flex-col justify-center">
                    <!-- Price -->
                    <div class="flex items-center mb-0.5 md:mb-1">
                        <span class="text-[#041533] font-bold text-[13px] md:text-lg">BDT 2056</span>
                    </div>

                    <!-- Product Title -->
                    <h3 class="text-[#041533] font-semibold text-[11px] md:text-lg leading-tight line-clamp-2">
                        Princess Dress
                    </h3>

                    <!-- Category/Subtitle (Optional, hidden on mobile to keep it clean) -->
                    <p class="text-[#7a818c] text-xs md:text-sm font-normal hidden sm:block">
                        Feeding Item
                    </p>
                </div>
            </div>
        </div>
    </section>
    <!-- ABOUT / SEO TEXT SECTION -->
    <section class="w-full bg-[#fcfcfc] px-4">
        <div class="container mx-auto py-4 md:py-10">
            <!-- Item 1 -->
            <div class="mb-12">
                <h2 class="text-[24px] md:text-[28px] font-bold text-[#041533] mb-4 tracking-tight">
                    Welcome to BabyShoppers: Your Trusted Partner in Parenthood
                </h2>
                <p class="text-[#4b5563] text-base md:text-[16px] leading-[1.7] text-justify md:text-left">
                    Lorem Ipsum is simply dummy text of the printing and typesetting
                    industry. Lorem Ipsum has been the industry's standard dummy text
                    ever since 1966, when designers at Letraset and James Mosley, the
                    librarian at St Bride Printing Library in London, took a 1914
                    Cicero translation and scrambled it to make dummy text for
                    Letraset's Body Type sheets. It has survived not only many
                    decades, but also the leap into electronic typesetting, remaining
                    essentially unchanged. It was popularised thanks to these sheets
                    and more recently with desktop publishing software including
                    versions of Lorem Ipsum.
                </p>
            </div>

            <!-- Item 2 (Repeated Structure) -->
            <div class="mb-12">
                <h3 class="text-[22px] md:text-[24px] font-bold text-[#041533] mb-4 tracking-tight">
                    Why Bangladeshi Parents Choose BabyShoppers
                </h3>
                <p class="text-[#4b5563] text-base md:text-[16px] leading-[1.7] text-justify md:text-left">
                    It is a long established fact that a reader will be distracted by
                    the readable content of a page when looking at its layout. The
                    point of using Lorem Ipsum is that it has a more-or-less normal
                    distribution of letters, as opposed to using 'Content here,
                    content here', making it look like readable English. Many desktop
                    publishing packages and web page editors now use Lorem Ipsum as
                    their default model text, and a search for 'lorem ipsum' will
                    uncover many web sites still in their infancy. Various versions
                    have evolved over the years, sometimes by accident, sometimes on
                    purpose (injected humour and the like).
                </p>
            </div>

            <!-- Repeat Item 2 for more sections as shown in your image -->
            <div class="mb-12">
                <h3 class="text-[22px] md:text-[24px] font-bold text-[#041533] mb-4 tracking-tight">
                    Why Bangladeshi Parents Choose BabyShoppers
                </h3>
                <p class="text-[#4b5563] text-base md:text-[16px] leading-[1.7] text-justify md:text-left">
                    It is a long established fact that a reader will be distracted by
                    the readable content of a page when looking at its layout. The
                    point of using Lorem Ipsum is that it has a more-or-less normal
                    distribution of letters, as opposed to using 'Content here,
                    content here', making it look like readable English.
                </p>
            </div>
        </div>
    </section>
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
