@extends('template2.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.index-meta', ['setup' => $setup])
    @if(isset($mainSliders) && $mainSliders->isNotEmpty())
        @php $firstSlider = $mainSliders->first(); @endphp
        <link rel="preload" as="image" href="{{ $firstSlider->mobile_image_url ?? asset('images/template1/frontend/cover.webp') }}" media="(max-width: 767px)">
        <link rel="preload" as="image" href="{{ $firstSlider->image_url ?? asset('images/template1/frontend/cover.webp') }}" media="(min-width: 768px)">
    @endif
@endsection
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
                                <a href="{{ $slider->url ?? '#' }}" class="block w-full h-full">
                                    <picture class="block w-full h-full">
                                        <source media="(max-width: 767px)" srcset="{{ $slider->mobile_image_url ?? asset('images/template1/frontend/cover.webp') }}">
                                        <source media="(min-width: 768px)" srcset="{{ $slider->image_url ?? asset('images/template1/frontend/cover.webp') }}">
                                        <img src="{{ $slider->image_url ?? asset('images/template1/frontend/cover.webp') }}"
                                            class="w-full h-full object-cover" alt="{{ $slider->title }}"
                                            @if($loop->first) fetchpriority="high" loading="eager" @else loading="lazy" @endif>
                                    </picture>
                                </a>
                            </div>
                        @empty
                            <div class="min-w-full h-full">
                                <img src="{{ asset('./images/template1/frontend/default.webp') }}" alt="default image"
                                    class="w-full h-full object-cover" loading="lazy" width="800" height="800">
                            </div>
                        @endforelse
                    </div>

                    <!-- Navigation Arrows (Click logic added) -->
                    <button onclick="prevSlide()" aria-label="Previous slide"
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full border border-white/50 flex items-center justify-center text-primary bg-black/20 hover:bg-black/40 transition-all opacity-0 group-hover:opacity-100 z-10 cursor-pointer">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button onclick="nextSlide()" aria-label="Next slide"
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
    @if (!empty($featureCategory) && count($featureCategory['items']) > 0)
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
        <div class="relative">
            <!-- Section Heading -->
            <h2 class="text-md md:text-lg font-bold  uppercase tracking-tight text-center mb-0">
              {{ $featureCategory['name'] ?? 'Featured Category' }}
            </h2>
            <p class="text-center lg:text-xl text-[13px] text-black lg:mb-4 mb-2 mt-0">Buy Your Desired Products from Featured Categories</p>

            <!-- Carousel Wrapper -->
            <div class="relative px-2 md:px-6">

                <!-- Navigation Buttons (Green as per Image) -->
                <button onclick="scrollCats(-240)" aria-label="Scroll left"
                    class="absolute left-0 top-1/2 -translate-y-1/2 w-8 h-8 md:w-8 md:h-8 primary-bg text-primary rounded-full items-center justify-center shadow-lg z-20 hover:scale-110 transition-all cursor-pointer hidden md:flex">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>

                <button onclick="scrollCats(240)" aria-label="Scroll right"
                    class="absolute right-0 top-1/2 -translate-y-1/2 w-8 h-8 md:w-8 md:h-8 primary-bg text-primary rounded-full items-center justify-center shadow-lg z-20 hover:scale-110 transition-all cursor-pointer hidden md:flex">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>

                <!-- Categories Scroll Area -->
                <div id="cat-slider" class="flex items-stretch gap-3 overflow-x-auto no-scrollbar scroll-smooth py-2">
                    @foreach ($featureCategory['items'] as $tItem)
                        @if (data_get($tItem, 'visible') === true)
                            <a href="{{ url($tItem['link'] ?? '#') }}"
                                class="bg-white flex flex-col items-center justify-between min-w-[100px] w-[100px] md:w-auto md:min-w-[210px] flex-shrink-0 bg-white border border-gray-200 rounded-lg p-2 md:p-6 hover:shadow-lg transition-all duration-300 group">

                                <!-- Image Wrapper -->
                                <div class="w-full h-16 md:h-32 flex items-center justify-center mb-2 md:mb-4">
                                    <img src="{{ $tItem['image'] ?? asset('images/template1/frontend/default.webp') }}"
                                        onerror="this.onerror=null;this.src='{{ asset('images/template1/frontend/default.webp') }}';"
                                        class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500"
                                        alt="{{ $tItem['label'] }}" loading="lazy" width="800" height="800">
                                </div>

                                <!-- Category Name -->
                                <span class="text-[#0f172a] font-normal leading-tight text-center group-hover:text-[var(--primary-color)] transition-colors line-clamp-2 text-[12px] md:text-[16px]">
                                    {{ $tItem['label'] }}
                                </span>
                                <span class="text-[#0f172a9c] font-normal leading-tight text-center group-hover:text-[var(--primary-color)] transition-colors line-clamp-2 text-[10px] md:text-[12px]">
                                    {{ $tItem['product_count'] ?? 0 }} Items 
                                </span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @elseif (isset($categories) && $categories->count() > 0)
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
        <div class="relative">
            <!-- Section Heading -->
            <h2 class="text-md md:text-lg font-bold  uppercase tracking-tight text-center mb-0">
              {{ $featureCategory['name'] ?? 'Featured Category' }}
            </h2>
            <p class="text-center lg:text-xl text-[13px] text-black lg:mb-4 mb-2 mt-0">Buy Your Desired Products from Featured Categories</p>

            <!-- Carousel Wrapper -->
            <div class="relative px-2 md:px-6">

                <!-- Navigation Buttons (Green as per Image) -->
                <button onclick="scrollCats(-240)" aria-label="Scroll left"
                    class="absolute left-0 top-1/2 -translate-y-1/2 w-8 h-8 md:w-8 md:h-8 primary-bg text-primary rounded-full items-center justify-center shadow-lg z-20 hover:scale-110 transition-all cursor-pointer hidden md:flex">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>

                <button onclick="scrollCats(240)" aria-label="Scroll right"
                    class="absolute right-0 top-1/2 -translate-y-1/2 w-8 h-8 md:w-8 md:h-8 primary-bg text-primary rounded-full items-center justify-center shadow-lg z-20 hover:scale-110 transition-all cursor-pointer hidden md:flex">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>

                <!-- Categories Scroll Area -->
                <div id="cat-slider" class="flex items-stretch gap-3 overflow-x-auto no-scrollbar scroll-smooth py-2">
                    @foreach ($categories as $category)
                        <a href="{{ url($category->slug) }}"
                            class="flex flex-col bg-white items-center justify-between min-w-[100px] w-[100px] md:w-auto md:min-w-[210px] flex-shrink-0 bg-white border border-gray-200 rounded-lg p-2 md:p-6 hover:shadow-lg transition-all duration-300 group">

                            <!-- Image Wrapper -->
                            <div class="w-full h-16 md:h-32 flex items-center justify-center mb-2 md:mb-4">
                                <img src="{{ $category->image_url ?? asset('images/template1/frontend/default.webp') }}"
                                    onerror="this.onerror=null;this.src='{{ $category->image_url ?? asset('images/template1/frontend/default.webp') }}';"
                                    class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500"
                                    alt="{{ $category->name }}">
                            </div>
                             <span class="text-[#0f172a] font-normal leading-tight text-center group-hover:text-[var(--primary-color)] transition-colors line-clamp-2 text-[12px] md:text-[16px]">
                                {{ $category->name }}
                            </span>

                            <!-- Category Name -->
                            <span class="text-[#0f172a9c] font-normal leading-tight text-center group-hover:text-[var(--primary-color)] transition-colors line-clamp-2 text-[10px] md:text-[12px]">
                                {{ $category->product_count ?? 0 }} Items 
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif
    @foreach ($productGroups as $group)
        <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
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
                        $currency = $setup->currency ?? 'à§³';
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
                            <a href="{{ url($product->slug) }}" class="block w-full h-full">
                                <img src="{{ $product->thumbnail_url }}" alt="{{ $product->title }}" loading="lazy"
                                    class="w-full h-24 md:h-32 object-contain transform group-hover:scale-110 transition-transform duration-500 p-2">
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
                                    @if ($isVar && $minPrice > 0)
                                        {{-- à¦­à§à¦¯à¦¾à¦°à¦¿à§Ÿà§‡à¦¶à¦¨: Lowest - Highest Regular Price --}}
                                        <span>{{ number_format($minPrice, 0) }}{{ $currency }} â€“
                                            {{ number_format($maxPrice, 0) }}{{ $currency }}</span>
                                    @else
                                        {{-- à¦¸à¦¿à¦™à§à¦—à§‡à¦²: Discount à¦¥à¦¾à¦•à¦²à§‡ à¦•à¦¾à¦Ÿà¦¾ à¦¦à¦¾à¦®à¦¸à¦¹ à¦¦à§‡à¦–à¦¾à¦¬à§‡ --}}
                                        @if ($salePrice < $regularPrice && $salePrice > 0)
                                            <span
                                                class="line-through text-gray-500 text-xs md:text-sm font-bold">{{ number_format($regularPrice, 0) }}{{ $currency }}</span>
                                            <span>{{ number_format($salePrice, 0) }}{{ $currency }}</span>
                                        @elseif($regularPrice > 0)
                                            <span>{{ number_format($regularPrice, 0) }}{{ $currency }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Button -->
                            <div class="mt-3">
                                <a href="{{ url($product->slug) }}"
                                    class="block w-full text-center primary-bg text-primary py-2 rounded font-bold text-xs md:text-sm hover:bg-opacity-95 transition-all shadow-sm">
                                    à¦ªà¦£à§à¦¯ à¦¦à§‡à¦–à§à¦¨
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
             
                <h2 class="text-md md:text-lg font-bold lg:mb-4 mb-2 uppercase tracking-tight">All Products</h2>
            

            <!-- Product Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 md:gap-5">
                @foreach ($allProducts as $product)
                    <x-template1.product-card :product="$product" />
                @endforeach

            </div>

            <!-- View More Button -->
            <div class="flex justify-center mt-10">
                <a href="{{ route('shop.index') }}"
                    class="primary-bg text-primary hover:bg-[var(--secondary-color)] hover:text-[var(--secondary-text)] hover:font-semibold font-semibold  py-2.5 px-6 md:py-2 md:px-4 text-xs md:text-sm rounded-md transition-colors shadow-sm">
                    View More
                </a>
            </div>

        </div>
    </section>
     @if($homePageData->description)
        <section class="w-full  px-4 font-manrope">
            <div class="p-0 container mx-auto    ">
                <div
                    class=" bg-white  border border-gray-100 rounded-lg px-6 py-6
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
        // Hero Slider Logic
        const mainSlider = document.getElementById('main-slider');
        let mainIdx = 0;
        const totalSlides = {{ count($mainSliders) }};
        let mainInterval;

        // à¦¸à§à¦²à¦¾à¦‡à¦¡à¦¾à¦° à¦†à¦ªà¦¡à§‡à¦Ÿ à¦•à¦°à¦¾à¦° à¦«à¦¾à¦‚à¦¶à¦¨
        function updateSliderUI() {
            if (mainSlider) {
                mainSlider.style.transform = `translateX(-${mainIdx * 100}%)`;
            }
        }

        // à¦ªà¦°à¦¬à¦°à§à¦¤à§€ à¦¸à§à¦²à¦¾à¦‡à¦¡
        function nextSlide() {
            if (totalSlides > 0) {
                mainIdx = (mainIdx + 1) % totalSlides;
                updateSliderUI();
                resetInterval();
            }
        }

        // à¦ªà§‚à¦°à§à¦¬à¦¬à¦°à§à¦¤à§€ à¦¸à§à¦²à¦¾à¦‡à¦¡
        function prevSlide() {
            if (totalSlides > 0) {
                mainIdx = (mainIdx - 1 + totalSlides) % totalSlides;
                updateSliderUI();
                resetInterval();
            }
        }

        // à¦…à¦Ÿà§‹ à¦¸à§à¦²à¦¾à¦‡à¦¡ à¦Ÿà¦¾à¦‡à¦®à¦¾à¦° à¦°à¦¿à¦¸à§‡à¦Ÿ
        function resetInterval() {
            clearInterval(mainInterval);
            startInterval();
        }

        // à¦…à¦Ÿà§‹ à¦¸à§à¦²à¦¾à¦‡à¦¡ à¦¶à§à¦°à§
        function startInterval() {
            if (totalSlides > 1) {
                mainInterval = setInterval(nextSlide, 5000);
            }
        }

        // à¦ªà§‡à¦œ à¦²à§‹à¦¡ à¦¹à¦²à§‡ à¦¶à§à¦°à§ à¦¹à¦¬à§‡
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

        // à¦…à¦¨à§à¦¯à¦¾à¦¨à§à¦¯ à¦¸à§à¦•à§à¦°à¦² à¦«à¦¾à¦‚à¦¶à¦¨
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

