@extends('template1.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.index-meta', ['setup' => $setup])
    @if(isset($mainSliders) && $mainSliders->isNotEmpty())
        @php $firstSlider = $mainSliders->first(); @endphp
        <link rel="preload" as="image" href="{{ $firstSlider->mobile_image_url ?? asset('images/template1/frontend/cover.webp') }}" media="(max-width: 767px)" fetchpriority="high">
        <link rel="preload" as="image" href="{{ $firstSlider->image_url ?? asset('images/template1/frontend/cover.webp') }}" media="(min-width: 768px)" fetchpriority="high">
    @endif
@endsection
@section('content')
    <!-- HERO SECTION -->
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
        <!-- Main 3-Column Layout -->
        <div class="flex flex-col lg:flex-row gap-4 items-stretch h-[200px] sm:h-[280px] md:h-[380px] lg:h-[480px]">

            <!-- 1. LEFT SIDEBAR: Cascading Multi-Level Menu (260px wide) -->
            <div class="relative flex h-full min-h-0 w-[250px] flex-col rounded-lg bg-white pb-2 shadow-xs hidden lg:flex">
                <div class="primary-bg text-primary shrink-0 py-3 text-lg text-center font-semibold">
                    Explore Categories</div>
                <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain">
                    @foreach ($categories as $category)
                    <div class="group border-b border-gray-200">
                        <a href="{{ url($category->slug) }}"
                            class="w-full flex items-center justify-between p-3 hover:bg-orange-50 rounded-xl transition-all">
                            <div class="flex items-center gap-3 ">
                                <img src="{{ !empty($category->image) ? $category->image_url : asset('./images/template1/frontend/default.webp') }}"
                                    class="w-8 h-8 rounded-full object-cover border border-gray-100"
                                    alt="{{ $category->name }}">
                                <span class="text-lg text-gray-800">{{ $category->name }}</span>
                            </div>
                            @if ($category->subCategories->count() > 0)
                                <i class="fas fa-chevron-right text-[10px] text-gray-400"></i>
                            @endif
                        </a>

                        <!-- subcategory panel -->
                        @if ($category->subCategories->count() > 0)
                            <div
                                class="absolute left-full top-0 w-[240px] h-full min-h-max bg-white shadow-xs rounded-lg border border-gray-100 py-2 hidden group-hover:block z-40 transition-all duration-300">

                                @foreach ($category->subCategories as $subCategory)
                                    <!-- sub category -->
                                    <div class="group/sub">
                                        <a href="{{ url($subCategory->slug) }}"
                                            class="flex items-center justify-between px-4 py-2.5 hover:bg-orange-50 text-sm text-gray-700 hover:text-[var(--primary-color)] transition-colors">
                                            <span>{{ $subCategory->name }}</span>
                                            @if ($subCategory->miniCategories && $subCategory->miniCategories->count() > 0)
                                                <i class="fas fa-chevron-right text-[9px]"></i>
                                            @endif
                                        </a>

                                        <!-- mini category pannel -->
                                        @if ($subCategory->miniCategories && $subCategory->miniCategories->count() > 0)
                                            <div
                                                class="absolute left-full top-0 w-[220px] h-full bg-white shadow-2xl rounded-xl border border-gray-100 py-2 hidden group-hover/sub:block z-50 ml-0.5 transition-all duration-200">
                                                @foreach ($subCategory->miniCategories as $miniCategory)
                                                    <a href="{{ url($miniCategory->slug) }}"
                                                        class="block px-4 py-2 text-sm text-gray-600 hover:text-[var(--primary-color)] hover:bg-orange-50 transition-colors">
                                                        {{ $miniCategory->name }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>

            </div>

            <!-- 2. CENTER: Main Horizontal Auto-Slider -->
            <div class="flex-1 min-w-0 h-full">
                <div class="relative h-full w-full rounded-lg overflow-hidden shadow-xs  bg-white">
                    <div id="main-slider" class="flex transition-transform duration-700 ease-in-out h-full w-full">
                        @forelse($mainSliders as $slider)
                            <div class="min-w-full h-full">
                                <a href="{{ $slider->url ?? '#' }}" class="block w-full h-full">
                                    <picture class="block w-full h-full">
                                        <source media="(max-width: 767px)" srcset="{{ $slider->mobile_image_url }}">
                                        <source media="(min-width: 768px)" srcset="{{ $slider->image_url }}">
                                        <img src="{{ $slider->image_url }}" class="w-full h-full object-cover"
                                            alt="{{ $slider->title }}" @if($loop->first) fetchpriority="high" loading="eager" @else loading="lazy" @endif>
                                    </picture>
                                </a>
                            </div>
                        @empty
                            <div class="min-w-full h-full"><img src="{{ asset('./images/template1/frontend/default.webp') }}"
                                    alt="default image" class="w-full h-full object-cover" loading="lazy" width="800" height="800"></div>
                        @endforelse
                    </div>

                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5">
                        @foreach ($mainSliders as $index => $slider)
                            <div onclick="goToSlide({{ $index }})"
                                class="main-dot cursor-pointer {{ $index == 0 ? 'w-6 bg-[var(--primary-color)]' : 'w-2 bg-white/50' }} h-1 rounded-full transition-all">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- 3. RIGHT SIDEBAR: Vertical Banner Slider -->
            <div class="hidden lg:block w-[260px] shrink-0">
                <div class="relative h-full rounded-lg  overflow-hidden bg-white">
                    <div id="vertical-slider"
                        class="flex flex-col transition-transform duration-700 ease-in-out h-full w-full">
                        @foreach ($sidebarSliders as $slider)
                            <div class="min-h-full w-full">
                                <a href="{{ $slider->url ?? '#' }}">
                                    <img src="{{ $slider->image_url }}" class="w-full h-full object-cover rounded-lg"
                                        alt="{{ $slider->title }}">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- TOP CATEGORIES SECTION -->
    @if (!empty($featureCategory) && count($featureCategory['items']) > 0)
        <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
            <div class="bg-white rounded-lg shadow-xs  md:p-6 p-2 relative">
                <h2 class="text-lg font-bold text-gray-900 uppercase tracking-tight mb-8 px-2">
                    {{ $featureCategory['name'] ?? 'Top Categories' }}
                </h2>
                <div class="relative group">
                    <button onclick="scrollCats(-200)" aria-label="Scroll left"
                        class="hidden md:flex absolute -left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                        <i class="fas fa-chevron-left text-xs text-gray-600 cursor-pointer"></i>
                    </button>
                    <button onclick="scrollCats(200)" aria-label="Scroll right"
                        class="hidden md:flex absolute -right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                        <i class="fas fa-chevron-right text-xs text-gray-600 cursor-pointer"></i>
                    </button>
                    <div id="cat-slider" class="flex items-start gap-3 md:gap-8 overflow-x-auto no-scrollbar scroll-smooth">
                        @foreach ($featureCategory['items'] as $tItem)
                            @if (data_get($tItem, 'visible') === true)

                                <a href="{{ url($tItem['link'] ?? '#') }}"
                                    class="flex flex-col items-center md:min-w-[110px] min-w-[85px] ">
                                    <div
                                        class="w-16 h-16 md:w-24 md:h-24 rounded-full group overflow-hidden mb-2 md:mb-3 border border-gray-100">
                                        <img src="{{ $tItem['image'] ?? asset('images/template1/frontend/default.webp') }}"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            alt="{{ $tItem['label'] }}"
                                            onerror="this.onerror=null;this.src='{{ asset('images/template1/frontend/default.webp') }}';" loading="lazy" width="800" height="800">
                                    </div>
                                    <span class="md:text-md text-sm text-gray-800 text-center w-full px-1">
                                        {{ $tItem['label'] }}
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
            <div class="bg-white rounded-lg shadow-xs  md:p-6 p-2 relative">
                <h2 class="text-lg font-bold text-gray-900 uppercase tracking-tight mb-8 px-2">
                    Top Categories
                </h2>
                <div class="relative group">
                    <button onclick="scrollCats(-200)" aria-label="Scroll left"
                        class="hidden md:flex absolute -left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                        <i class="fas fa-chevron-left text-xs text-gray-600 cursor-pointer"></i>
                    </button>
                    <button onclick="scrollCats(200)" aria-label="Scroll right"
                        class="hidden md:flex absolute -right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                        <i class="fas fa-chevron-right text-xs text-gray-600 cursor-pointer"></i>
                    </button>
                    <div id="cat-slider" class="flex items-start gap-3 md:gap-8 overflow-x-auto no-scrollbar scroll-smooth">
                        @foreach ($categories as $category)
                            <a href="{{ url($category->slug) }}"
                                class="flex flex-col items-center md:min-w-[110px] min-w-[85px] ">
                                <div
                                    class="w-16 h-16 md:w-24 md:h-24 rounded-full group overflow-hidden mb-2 md:mb-3 border border-gray-100">
                                    <img src="{{ $category->image_url ?? asset('images/template1/frontend/default.webp') }}"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                        alt="{{ $category->name }}"
                                        onerror="this.onerror=null;this.src='{{ asset('images/template1/frontend/default.webp') }}';">
                                </div>
                                <span class="md:text-md text-sm text-gray-800 text-center w-full px-1">
                                    {{ $category->name ?? '' }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!-- NEW ARRIVALS SECTION -->
    @if ($newArrivals->count() > 0)
        <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
            <div class="bg-white rounded-lg shadow-xs  md:p-6 p-2 relative">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg md:text-xl font-bold uppercase tracking-tight">New Arrivals</h2>
                    <a href="{{ route('shop.index') }}">
                        <button aria-label="View all new arrivals"
                            class="primary-bg primary-bg-hover font-semibold text-primary text-xs md:text-sm px-4 py-1.5 md:px-5 md:py-2 rounded transition-colors shadow-sm">
                            View all
                        </button>
                    </a>
                </div>

                <!-- Carousel Wrapper -->
                <div class="relative">

                    <!-- Left Arrow -->
                    <button onclick="scrollNA(-280)" aria-label="left arrow"
                        class="cursor-pointer hidden md:flex absolute -left-2 md:-left-5 top-[35%] -translate-y-1/2 z-20 w-7 h-7 bg-gray-50 border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>

                    <!-- Track -->
                    <div id="na-track" class="flex md:gap-4 gap-2 overflow-x-auto scroll-smooth no-scrollbar pb-4"
                        style="-ms-overflow-style:none; scrollbar-width:none;">

                        <!-- NEW ARRIVALS -->
                        @foreach ($newArrivals as $product)
                            <div class="flex-shrink-0 w-[165px] md:w-[240px] h-auto">
                                <x-template1.product-card :product="$product" />
                            </div>
                        @endforeach

                    </div><!-- /#na-track -->

                    <!-- Right Arrow -->
                    <button onclick="scrollNA(280)" aria-label="Scroll right"
                        class="cursor-pointer hidden md:flex absolute right-1 top-[35%] -translate-y-1/2 z-20 w-7 h-7 bg-gray-50 border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>

                </div><!-- /.relative -->



            </div>
        </section>
    @endif
    <!-- PRODUCT GROUPS SECTION -->
    @foreach ($productGroups as $group)
        <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
            <div class="bg-white rounded-lg shadow-xs  p-2 md:p-6 relative">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg md:text-xl font-bold uppercase tracking-tight">{{ $group->name }}</h2>
                    <a href="{{ route('shop.index', ['group' => $group->slug]) }}">
                        <button
                            class="primary-bg primary-bg-hover text-primary text-xs md:text-sm px-4 py-1.5 md:px-5 md:py-2 rounded transition-colors shadow-sm">
                            View all
                        </button>
                    </a>
                </div>

                <!-- Carousel Wrapper -->
                <div class="relative">
                    <!-- Left Arrow -->
                    <button onclick="scrollGroup('track-{{ $group->id }}', -280)" aria-label="Scroll left"
                        class="cursor-pointer hidden md:flex absolute -left-2 md:-left-5 top-[40%] -translate-y-1/2 z-20 w-7 h-7 bg-gray-50 border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <!-- Track: ID -->
                    <div id="track-{{ $group->id }}"
                        class="flex gap-3 md:gap-4 overflow-x-auto scroll-smooth no-scrollbar pb-2 touch-pan-x"
                        style="-ms-overflow-style:none; scrollbar-width:none;">
                        @foreach ($group->products as $product)
                            <div class="flex-shrink-0 w-[165px] md:w-[240px] h-auto">
                                <x-template1.product-card :product="$product" />
                            </div>
                        @endforeach
                    </div>

                    <!-- Right Arrow -->
                    <button onclick="scrollGroup('track-{{ $group->id }}', 280)" aria-label="Scroll right"
                        class="cursor-pointer hidden md:flex absolute -right-2 md:-right-5 top-[40%] -translate-y-1/2 z-20 w-7 h-7 bg-gray-50 border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>
    @endforeach

    <!-- DUAL BANNER SECTION -->
    @if ($middleSliders->count() > 0)
        <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
            <div class="flex flex-col md:flex-row gap-3 md:gap-5">

                @foreach ($middleSliders as $slider)
              
                    <div
                        class="flex-1 h-32 sm:h-40 md:h-48 lg:h-76 overflow-hidden rounded-lg shadow-xs hover:shadow-md transition-shadow duration-300 cursor-pointer group">
                        <a href="{{ $slider->url ?? '#' }}" class="block w-full h-full">
                            <picture class="block w-full h-full">
                                <source media="(max-width: 767px)" srcset="{{ $slider->mobile_image_url ?? asset('./images/template1/frontend/default.webp') }}">
                                <source media="(min-width: 768px)" srcset="{{ $slider->image_url ?? asset('./images/template1/frontend/default.webp') }}">
                                <img src="{{ $slider->image_url ?? asset('./images/template1/frontend/default.webp') }}"
                                    alt="{{ $slider->title }}" loading="lazy"
                                    class="w-full h-full rounded-md object-cover transition-transform duration-700 ease-in-out group-hover:scale-105">
                            </picture>
                        </a>
                    </div>
                @endforeach

                @if ($middleSliders->count() == 0)
                    <div class="flex-1 bg-gray-100 h-48 rounded-lg animate-pulse"></div>
                    <div class="flex-1 bg-gray-100 h-48 rounded-lg animate-pulse"></div>
                @endif

            </div>
        </section>
    @endif
    <!-- POPULAR BRANDS SECTION -->
    @if ($brands->count() > 0)
        <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
            <!-- Main Card Container -->
            <div class="bg-white rounded-lg shadow-xs  md:p-6 p-2 relative">

                <!-- Section Heading -->
                <h2 class="text-md md:text-lg font-bold text-gray-900 uppercase tracking-tight mb-6 px-2">
                    Popular Brands
                </h2>

                <!-- Carousel Wrapper -->
                <div class="relative group">

                    <!-- Navigation Buttons -->
                    <button onclick="scrollBrands(-200)" aria-label="Scroll left"
                        class="hidden md:flex absolute -left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                        <i class="fas fa-chevron-left text-xs text-gray-600 cursor-pointer"></i>
                    </button>

                    <button onclick="scrollBrands(200)" aria-label="Scroll right"
                        class="hidden md:flex absolute -right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                        <i class="fas fa-chevron-right text-xs text-gray-600 cursor-pointer"></i>
                    </button>

                    <!-- Brands Scroll Area -->
                    <div id="brand-track" class="flex items-start gap-4 md:gap-8 overflow-x-auto no-scrollbar scroll-smooth">
                        @foreach ($brands as $brand)
                            @if (!empty($brand->slug))
                                <a href="{{ url($brand->slug) }}"
                                    class="flex flex-col items-center min-w-[80px] md:min-w-[105px] group">

                                    <!-- Circular Image Wrapper -->
                                    <div
                                        class="w-20 h-20 md:w-24 md:h-24 rounded-full overflow-hidden mb-3 border border-gray-100 bg-white flex items-center justify-center p-2">
                                        <img src="{{ $brand->logo_url ?? asset('./images/template1/frontend/default.webp') }}"
                                            class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                            alt="{{ $brand->name }}">
                                    </div>

                                    <!-- Brand Name -->
                                    <span class="text-xs md:text-sm font-semibold text-gray-800 text-center truncate w-full px-1">
                                        {{ $brand->name ?? '' }}
                                    </span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($popularProducts->count() > 0)
        <!-- YOU MAY LIKE SECTION -->
        <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
            <div class="bg-white rounded-lg shadow-xs  p-2 md:p-6">

                <!-- Header -->
                <div class="mb-6">
                    <h2 class="text-md md:text-lg font-bold mb-4 md:mb-6 uppercase tracking-tight">You May Like</h2>
                </div>

                <!-- Product Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 md:gap-5">
                    @foreach ($popularProducts as $product)
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
    @endif
    @if ($faqs->count() > 0)
        <!-- FAQ SECTION (Styled like You May Like) -->
        <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
            <div class="bg-white rounded-lg shadow-xs p-4 md:p-10 border border-gray-50">

                <!-- Section Header -->
                <div class="mb-10 border-b border-gray-100 pb-4">
                    <h2 class="text-lg md:text-3xl font-bold uppercase tracking-tight text-gray-900">
                        Frequently Asked Questions
                    </h2>
                </div>

                <!-- FAQ Items Container -->
                <div class="space-y-10">
                    @forelse($faqs as $faq)
                        <div class="faq-item">
                            <!-- Question -->
                            <h3
                                class="text-base md:text-xl font-semibold md:font-bold text-gray-800 mb-4 uppercase tracking-wide flex items-start gap-3">
                                <span class="text-blue-600">Q.</span>
                                {{ $faq->title ?? '' }}
                            </h3>

                            <!-- Answer -->
                            <div class="text-gray-600 text-sm md:text-base leading-relaxed blog-content-area pl-0 md:pl-8">
                                {!! $faq->content !!}
                            </div>

                            @if (!$loop->last)
                                <hr class="md:mt-10 border-gray-200">
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <p class="text-gray-600 italic">No information found.</p>
                        </div>
                    @endforelse
                </div>

            </div>
        </section>
    @endif
@endsection
@push('scripts')
    <script>
        function scrollCats(distance) {
            const slider = document.getElementById('cat-slider');
            slider.scrollBy({
                left: distance,
                behavior: 'smooth'
            });
        }

        function scrollNA(distance) {
            const track = document.getElementById('na-track');
            track.scrollBy({
                left: distance,
                behavior: 'smooth'
            });
        }

        function scrollBrands(distance) {
            const track = document.getElementById('brand-track');
            track.scrollBy({
                left: distance,
                behavior: 'smooth'
            });
        }

        function toggleDropdown(id) {
            const menu = document.getElementById('menu-' + id);
            const icon = document.getElementById('icon-' + id);

            if (menu.style.maxHeight === '0px' || menu.style.maxHeight === '') {
                menu.style.maxHeight = menu.scrollHeight + "px";
                icon.style.transform = "rotate(180deg)";
            } else {
                menu.style.maxHeight = "0px";
                icon.style.transform = "rotate(0deg)";
            }
        }

        const mainSlider = document.getElementById('main-slider');
        const mainDots = document.querySelectorAll('.main-dot');
        let mainIdx = 0;
        let mainInterval;

        if (mainSlider && mainDots.length > 0) {

            // slide to specific index when dot is clicked
            function goToSlide(index) {
                mainIdx = index;
                updateSliderUI();
                resetInterval(); // Reset timer when clicked to prevent automatic sliding
            }

            // Function to update slider and dots
            function updateSliderUI() {
                mainSlider.style.transform = `translateX(-${mainIdx * 100}%)`;
                mainDots.forEach((dot, i) => {
                    if (i === mainIdx) {
                        dot.classList.remove('w-2', 'bg-white/50');
                        dot.classList.add('w-6', 'bg-[var(--primary-color)]');
                    } else {
                        dot.classList.remove('w-6', 'bg-[var(--primary-color)]');
                        dot.classList.add('w-2', 'bg-white/50');
                    }
                });
            }

            // Auto sliding function
            function startInterval() {
                mainInterval = setInterval(() => {
                    mainIdx = (mainIdx + 1) % mainDots.length;
                    updateSliderUI();
                }, 4000);
            }

            function resetInterval() {
                clearInterval(mainInterval);
                startInterval();
            }

            // Add click event to dots
            mainDots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    goToSlide(index);
                });
            });

            // Start the slider
            startInterval();
        }

        const verticalSlider = document.getElementById('vertical-slider');
        if (verticalSlider) {
            let vertIdx = 0;
            const totalVert = verticalSlider.children.length;

            function slideVertical() {
                vertIdx = (vertIdx + 1) % totalVert;
                verticalSlider.style.transform = `translateY(-${vertIdx * 100}%)`;
            }
            setInterval(slideVertical, 5000);
        }

        function scrollGroup(trackId, distance) {
            const track = document.getElementById(trackId);
            if (track) {
                track.scrollBy({
                    left: distance,
                    behavior: 'smooth'
                });
            }
        }
    </script>
@endpush
