@extends('template2.layouts.front')

@section('content')
    <!-- HERO SECTION (Updated to 2-Column Layout) -->
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
        <div class="flex flex-col lg:flex-row gap-4 items-stretch h-[220px] sm:h-[300px] md:h-[400px] lg:h-[500px]">

            <!-- 1. LEFT: Main Slider -->
            <div class="flex-1 min-w-0 h-full relative group">
                <div class="relative h-full w-full rounded-lg overflow-hidden shadow-sm bg-white">

                    <!-- Slider Container -->
                    <div id="main-slider" class="flex transition-transform duration-700 ease-in-out h-full w-full">
                        @forelse($mainSliders as $slider)
                            <div class="min-w-full h-full">
                                <a href="{{ $slider->url ?? '#' }}">
                                    <img src="{{ $slider->image_url ?? asset('images/template1/frontend/cover.webp') }}"
                                        class="w-full h-full object-cover" alt="{{ $slider->title }}">
                                </a>
                            </div>
                        @empty
                            <div class="min-w-full h-full">
                                <img src="{{ asset('./images/template1/frontend/default.webp') }}" alt="default image"
                                    class="w-full h-full object-cover">
                            </div>
                        @endforelse
                    </div>

                    <!-- Navigation Arrows (Click logic added) -->
                    <button onclick="prevSlide()"
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full border border-white/50 flex items-center justify-center text-primary bg-black/20 hover:bg-black/40 transition-all opacity-0 group-hover:opacity-100 z-10 cursor-pointer">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button onclick="nextSlide()"
                        class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full border border-white/50 flex items-center justify-center text-primary bg-black/20 hover:bg-black/40 transition-all opacity-0 group-hover:opacity-100 z-10 cursor-pointer">
                        <i class="fas fa-chevron-right"></i>
                    </button>

                </div>
            </div>

            <!-- 2. RIGHT SIDEBAR -->
            <div class="hidden lg:block w-[320px] shrink-0">
                <div class="relative h-full rounded-lg overflow-hidden shadow-sm bg-white">
                    <div id="vertical-slider"
                        class="flex flex-col transition-transform duration-700 ease-in-out h-full w-full">
                        @foreach ($sidebarSliders as $slider)
                            <div class="min-h-full w-full">
                                <a href="{{ $slider->url ?? '#' }}">
                                    <img src="{{ $slider->image_url ?? asset('images/template1/frontend/hero-right1.jpg') }}"
                                        class="w-full h-full object-cover rounded-lg" alt="{{ $slider->title }}">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- TOP CATEGORIES SECTION (Updated as per Image) -->
    <section class="py-10 md:py-14 container mx-auto px-4 lg:px-0">
        <div class="relative">
            <!-- Section Heading -->
            <h2 class="text-center text-2xl md:text-3xl font-extrabold text-black mb-10">
                প্রোডাক্ট ক্যাটাগরি
            </h2>

            <!-- Carousel Wrapper -->
            <div class="relative group px-2 md:px-6">

                <!-- Navigation Buttons (Green as per Image) -->
                <button onclick="scrollCats(-240)" aria-label="Scroll left"
                    class="absolute left-0 top-1/2 -translate-y-1/2 w-8 h-8 md:w-8 md:h-8 primary-bg text-primary rounded-full flex items-center justify-center shadow-lg z-20 hover:scale-110 transition-all cursor-pointer">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>

                <button onclick="scrollCats(240)" aria-label="Scroll right"
                    class="absolute right-0 top-1/2 -translate-y-1/2 w-8 h-8 md:w-8 md:h-8 primary-bg text-primary rounded-full flex items-center justify-center shadow-lg z-20 hover:scale-110 transition-all cursor-pointer">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>

                <!-- Categories Scroll Area -->
                <div id="cat-slider" class="flex items-stretch gap-3 overflow-x-auto no-scrollbar scroll-smooth py-2">
                    @foreach ($categories as $category)
                        <a href="{{ url('category/' . $category->slug) }}"
                            class="flex flex-col items-center justify-between min-w-[160px] md:min-w-[210px] bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-all duration-300 group">

                            <!-- Image Wrapper -->
                            <div class="w-full h-32  flex items-center justify-center mb-4">
                                <img src="{{ $category->image_url ?? asset('images/template1/frontend/default.webp') }}"
                                    onerror="this.onerror=null;this.src='{{ $category->image_url ?? asset('images/template1/frontend/default.webp') }}';"
                                    class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500"
                                    alt="{{ $category->name }}">
                            </div>

                            <!-- Category Name -->
                            <span class="text-base md:text-lg font-bold text-gray-900 text-center leading-tight">
                                {{ $category->name }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @foreach ($productGroups as $group)
        <section class="py-8 md:py-12 container mx-auto px-4 lg:px-0">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg md:text-xl font-bold uppercase tracking-tight text-[#016738]">{{ $group->name }}</h2>
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
                            class="w-[110px] md:w-[140px] flex-shrink-0 relative overflow-hidden flex items-center justify-center bg-[#F9F9F9] rounded-md">
                            <a href="{{ route('product.details', $product->slug) }}" class="block w-full h-full">
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->title }}"
                                    class="w-full h-24 md:h-32 object-contain transform group-hover:scale-110 transition-transform duration-500 p-2">
                            </a>
                        </div>

                        <!-- Right: Content Area -->
                        <div class="flex-1 pl-4 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm md:text-base font-bold text-gray-900 leading-snug line-clamp-2 mb-2">
                                    <a href="{{ route('product.details', $product->slug) }}" class="hover:text-[#016738]">
                                        {{ $product->title }}
                                    </a>
                                </h3>

                                <!-- Price Logic (Corrected Fields) -->
                                <div class="text-[#016738] font-black text-sm md:text-lg flex flex-wrap items-center gap-2">
                                    @if ($isVar && $minPrice > 0)
                                        {{-- ভ্যারিয়েশন: Lowest - Highest Regular Price --}}
                                        <span>{{ number_format($minPrice, 0) }}{{ $currency }} –
                                            {{ number_format($maxPrice, 0) }}{{ $currency }}</span>
                                    @else
                                        {{-- সিঙ্গেল: Discount থাকলে কাটা দামসহ দেখাবে --}}
                                        @if ($salePrice < $regularPrice && $salePrice > 0)
                                            <span
                                                class="line-through text-gray-400 text-xs md:text-sm font-bold">{{ number_format($regularPrice, 0) }}{{ $currency }}</span>
                                            <span>{{ number_format($salePrice, 0) }}{{ $currency }}</span>
                                        @elseif($regularPrice > 0)
                                            <span>{{ number_format($regularPrice, 0) }}{{ $currency }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Button -->
                            <div class="mt-3">
                                <a href="{{ route('product.details', $product->slug) }}"
                                    class="block w-full text-center primary-bg text-primary py-2 rounded font-bold text-xs md:text-sm hover:bg-opacity-95 transition-all shadow-sm">
                                    পণ্য দেখুন
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach


    <!-- All Products SECTION -->
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
        <div class="bg-white rounded-lg shadow-xs  p-2 md:p-6">

            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-md md:text-lg font-bold mb-4 md:mb-6 uppercase tracking-tight">All Products</h2>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 md:gap-5">
                @foreach ($allProducts as $product)
                    <x-template1.product-card :product="$product" />
                @endforeach

            </div>

            <!-- View More Button -->
            <div class="flex justify-center mt-10">
                <a href="{{ route('shop.index') }}"
                    class="primary-bg text-primary hover:bg-gray-50 primary-bg-hover hover:font-semibold text-[#ff9800] font-semibold  py-2.5 px-6 md:py-2 md:px-4 text-xs md:text-sm rounded-md transition-colors shadow-sm">
                    View More
                </a>
            </div>

        </div>
    </section>
@endsection
@push('scripts')
    <script>
        // Hero Slider Logic
        const mainSlider = document.getElementById('main-slider');
        let mainIdx = 0;
        const totalSlides = {{ count($mainSliders) }};
        let mainInterval;

        // স্লাইডার আপডেট করার ফাংশন
        function updateSliderUI() {
            if (mainSlider) {
                mainSlider.style.transform = `translateX(-${mainIdx * 100}%)`;
            }
        }

        // পরবর্তী স্লাইড
        function nextSlide() {
            if (totalSlides > 0) {
                mainIdx = (mainIdx + 1) % totalSlides;
                updateSliderUI();
                resetInterval();
            }
        }

        // পূর্ববর্তী স্লাইড
        function prevSlide() {
            if (totalSlides > 0) {
                mainIdx = (mainIdx - 1 + totalSlides) % totalSlides;
                updateSliderUI();
                resetInterval();
            }
        }

        // অটো স্লাইড টাইমার রিসেট
        function resetInterval() {
            clearInterval(mainInterval);
            startInterval();
        }

        // অটো স্লাইড শুরু
        function startInterval() {
            if (totalSlides > 1) {
                mainInterval = setInterval(nextSlide, 5000);
            }
        }

        // পেজ লোড হলে শুরু হবে
        document.addEventListener('DOMContentLoaded', () => {
            if (mainSlider && totalSlides > 0) {
                startInterval();
            }

            // Vertical Slider Logic
            const verticalSlider = document.getElementById('vertical-slider');
            if (verticalSlider && verticalSlider.children.length > 1) {
                let vertIdx = 0;
                const totalVert = verticalSlider.children.length;
                setInterval(() => {
                    vertIdx = (vertIdx + 1) % totalVert;
                    verticalSlider.style.transform = `translateY(-${vertIdx * 100}%)`;
                }, 6000);
            }
        });

        // অন্যান্য স্ক্রল ফাংশন
        function scrollCats(distance) {
            document.getElementById('cat-slider').scrollBy({
                left: distance,
                behavior: 'smooth'
            });
        }

        function scrollNA(distance) {
            document.getElementById('na-track').scrollBy({
                left: distance,
                behavior: 'smooth'
            });
        }

        function scrollGroup(trackId, distance) {
            const track = document.getElementById(trackId);
            if (track) track.scrollBy({
                left: distance,
                behavior: 'smooth'
            });
        }

        function scrollBrands(distance) {
            document.getElementById('brand-track').scrollBy({
                left: distance,
                behavior: 'smooth'
            });
        }
    </script>
@endpush
