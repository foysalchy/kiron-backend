@extends('template1.layouts.front')

@section('content')
    <!-- HERO SECTION -->
    <section class="py-6 container mx-auto ">
        <!-- Main 3-Column Layout -->
        <div class="flex flex-col lg:flex-row gap-4 items-stretch h-auto lg:h-[480px] h-[200px]">

            <!-- 1. LEFT SIDEBAR: Cascading Multi-Level Menu (260px wide) -->
            <div class="relative w-[250px] bg-white shadow-xs rounded-lg border border-gray-200 p-4 hidden lg:block">

                @foreach ($categories as $category)
                    <div class="group">
                        <a href="{{ url('category/' . $category->slug) }}"
                            class="w-full flex items-center justify-between p-3 hover:bg-orange-50 rounded-xl transition-all">
                            <div class="flex items-center gap-3">
                                <img src="{{ !empty($category->image) ? asset('storage/' . $category->image) : asset('./images/template1/frontend/default.webp') }}"
                                    class="w-8 h-8 rounded-full object-cover border border-gray-100"
                                    alt="{{ $category->name }}">
                                <span class="text-md text-gray-800">{{ $category->name }}</span>
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
                                        <a href="{{ url('category/' . $subCategory->slug) }}"
                                            class="flex items-center justify-between px-4 py-2.5 hover:bg-orange-50 text-sm text-gray-700 hover:text-[#FF6A00] transition-colors">
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
                                                    <a href="{{ url('category/' . $miniCategory->slug) }}"
                                                        class="block px-4 py-2 text-sm text-gray-600 hover:text-[#FF6A00] hover:bg-orange-50 transition-colors">
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

            <!-- 2. CENTER: Main Horizontal Auto-Slider -->
            <div class="flex-1 min-w-0 h-[300px] md:h-[400px] lg:h-full">
                <div class="relative h-full w-full rounded-lg overflow-hidden shadow-xs border border-gray-200 bg-white">
                    <div id="main-slider" class="flex transition-transform duration-700 ease-in-out h-full w-full">
                        <div class="min-w-full h-full"><img src="{{ asset('images/template1/frontend/hero1.jpg') }}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{ asset('images/template1/frontend/hero2.jpg') }}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{ asset('images/template1/frontend/hero3.jpg') }}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{ asset('images/template1/frontend/hero4.jpg') }}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{ asset('images/template1/frontend/hero-right65.jpg') }}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{ asset('images/template1/frontend/hero1.jpg') }}"
                                class="w-full h-full object-cover"></div>
                    </div>

                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-1.5">
                        <div class="main-dot w-6 h-1 rounded-full bg-[#FF6A00] transition-all"></div>
                        <div class="main-dot w-2 h-1 rounded-full bg-white/50 transition-all"></div>
                        <div class="main-dot w-2 h-1 rounded-full bg-white/50 transition-all"></div>
                        <div class="main-dot w-2 h-1 rounded-full bg-white/50 transition-all"></div>
                        <div class="main-dot w-2 h-1 rounded-full bg-white/50 transition-all"></div>
                        <div class="main-dot w-2 h-1 rounded-full bg-white/50 transition-all"></div>
                    </div>
                </div>
            </div>

            <!-- 3. RIGHT SIDEBAR: Vertical Banner Slider -->
            <div class="hidden lg:block w-[260px] shrink-0">
                <div class="relative h-full rounded-lg border border-gray-200 overflow-hidden bg-white">
                    <!-- Vertical Slider Container -->
                    <div id="vertical-slider"
                        class="flex flex-col transition-transform duration-700 ease-in-out h-full w-full">
                        <!-- Banner 1 -->
                        <div class="min-h-full w-full">
                            <img src="{{ asset('images/template1/frontend/hero-right1.jpg') }}"
                                class="w-full h-full object-cover rounded-lg">
                        </div>
                        <!-- Banner 2 -->
                        <div class="min-h-full w-full">
                            <img src="{{ asset('images/template1/frontend/hero-right2.jpg') }}"
                                class="w-full h-full object-cover rounded-lg">
                        </div>
                        <!-- Banner 3 -->
                        <div class="min-h-full w-full">
                            <img src="{{ asset('images/template1/frontend/hero-right3.jpg') }}"
                                class="w-full h-full object-cover rounded-lg">
                        </div>
                        <!-- Banner 4 -->
                        <div class="min-h-full w-full">
                            <img src="{{ asset('images/template1/frontend/hero-right4.jpg') }}"
                                class="w-full h-full object-cover rounded-lg">
                        </div>
                        <!-- Banner 5 -->
                        <div class="min-h-full w-full">
                            <img src="{{ asset('images/template1/frontend/hero-right65.jpg') }}"
                                class="w-full h-full object-cover rounded-lg">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- TOP CATEGORIES SECTION -->
    <section class="py-6 container mx-auto">
        <!-- Main Card Container -->
        <div class="bg-white rounded-lg shadow-xs border border-gray-200 p-6 relative">

            <!-- Section Heading -->
            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-tight mb-8 px-2">
                Top Categories
            </h2>

            <!-- Carousel Wrapper -->
            <div class="relative group">

                <!-- Navigation Buttons -->
                <button onclick="scrollCats(-200)"
                    class="absolute -left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                    <i class="fas fa-chevron-left text-xs text-gray-600 cursor-pointer"></i>
                </button>

                <button onclick="scrollCats(200)"
                    class="absolute -right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                    <i class="fas fa-chevron-right text-xs text-gray-600 cursor-pointer"></i>
                </button>

                <!-- Categories Scroll Area (Dynamic) -->
                <div id="cat-slider" class="flex items-start gap-8 overflow-x-auto no-scrollbar scroll-smooth">
                    @foreach ($categories as $category)
                        <a href="{{ url('category/' . $category->slug) }}"
                            class="flex flex-col items-center min-w-[105px] group">
                            <div class="w-24 h-24 rounded-full overflow-hidden mb-3 border border-gray-100">
                                <img src="{{ !empty($category->image) ? asset('storage/' . $category->image) : asset('./images/template1/frontend/default.webp') }}"
                                    onerror="this.onerror=null;this.src='{{ asset('./images/template1/frontend/default.webp') }}';"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    alt="{{ $category->name }}">
                            </div>
                            <span class="text-sm font-semibold text-gray-800 text-center truncate w-full px-1">
                                {{ $category->name }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    <!-- NEW ARRIVALS SECTION -->
    <section class="py-6 container mx-auto">
        <div class="bg-white rounded-lg shadow-xs border border-gray-200 p-6 relative">

            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-black uppercase tracking-tight">New Arrivals</h2>
                <a href="{{ url('/shop') }}">
                    <button
                        class="bg-[#FF6A00] hover:bg-[#d44d1f] text-white text-sm font-bold px-5 py-2 rounded transition-colors shadow-sm">
                        View all
                    </button>
                </a>
            </div>

            <!-- Carousel Wrapper -->
            <div class="relative">

                <!-- Left Arrow -->
                <button onclick="scrollNA(-280)"
                    class="cursor-pointer absolute left-1 top-[35%] -translate-y-1/2 z-20 w-7 h-7 bg-gray-50 border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>

                <!-- Track -->
                <div id="na-track" class="flex gap-4 overflow-x-auto scroll-smooth no-scrollbar pb-4"
                    style="-ms-overflow-style:none; scrollbar-width:none;">

                    <!-- NEW ARRIVALS -->
                    @foreach ($newArrivals as $product)
                        <div class="flex-shrink-0 w-[240px] h-auto">
                            <x-template1.product-card :product="$product" />
                        </div>
                    @endforeach

                </div><!-- /#na-track -->

                <!-- Right Arrow -->
                <button onclick="scrollNA(280)"
                    class="cursor-pointer absolute right-1 top-[35%] -translate-y-1/2 z-20 w-7 h-7 bg-gray-50 border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

            </div><!-- /.relative -->



        </div>
    </section>
    <!-- PRODUCT GROUPS SECTION -->
    @foreach ($productGroups as $group)
        <section class="py-6 container mx-auto">
            <div class="bg-white rounded-lg shadow-xs border border-gray-200 p-6 relative">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-black uppercase tracking-tight">{{ $group->name }}</h2>
                    <a href="{{ url('/shop?group=' . $group->slug) }}">
                        <button
                            class="bg-[#FF6A00] hover:bg-[#d44d1f] text-white text-sm font-bold px-5 py-2 rounded transition-colors shadow-sm">
                            View all
                        </button>
                    </a>
                </div>

                <!-- Carousel Wrapper -->
                <div class="relative">
                    <!-- Left Arrow -->
                    <button onclick="scrollGroup('track-{{ $group->id }}', -280)"
                        class="cursor-pointer absolute left-1 top-[35%] -translate-y-1/2 z-20 w-7 h-7 bg-gray-50 border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <!-- Track: ID -->
                    <div id="track-{{ $group->id }}"
                        class="flex gap-4 overflow-x-auto scroll-smooth no-scrollbar pb-4"
                        style="-ms-overflow-style:none; scrollbar-width:none;">
                        @foreach ($group->products as $product)
                            <div class="flex-shrink-0 w-[240px] h-auto">
                                <x-template1.product-card :product="$product" />
                            </div>
                        @endforeach
                    </div>

                    <!-- Right Arrow -->
                    <button onclick="scrollGroup('track-{{ $group->id }}', 280)"
                        class="cursor-pointer absolute right-1 top-[35%] -translate-y-1/2 z-20 w-7 h-7 bg-gray-50 border border-gray-200 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 transition-all text-gray-600">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </section>
    @endforeach

    <!-- DUAL BANNER SECTION -->
    <section class="py-6 container mx-auto">
        <div class="flex flex-row gap-3 md:gap-5">

            <!-- Left Banner -->
            <div
                class="flex-1 overflow-hidden rounded-lg shadow-xs hover:shadow-md transition-shadow duration-300 cursor-pointer">
                <img src="https://orenmart.sgp1.digitaloceanspaces.com/banner/da1e494c-e05c-4725-b0cf-c90e56ccba1f.jpg"
                    alt="Promo Banner 1" loading="lazy"
                    class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-500">
            </div>

            <!-- Right Banner -->
            <div
                class="flex-1 overflow-hidden rounded-lg shadow-xs hover:shadow-md transition-shadow duration-300 cursor-pointer">
                <img src="https://orenmart.sgp1.digitaloceanspaces.com/banner/2e43fa93-a2ca-4371-bd4f-b6b4d4dca46e.jpg"
                    alt="Promo Banner 2" loading="lazy"
                    class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-500">
            </div>

        </div>
    </section>

    <!-- POPULAR BRANDS SECTION -->
    <section class="py-6 container mx-auto">
        <!-- Main Card Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">

            <!-- Section Heading -->
            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-tight mb-8 px-2">
                Popular Brands
            </h2>

            <!-- Carousel Wrapper -->
            <div class="relative group">

                <!-- Navigation Buttons -->
                <button onclick="scrollBrands(-200)"
                    class="absolute -left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                    <i class="fas fa-chevron-left text-xs text-gray-600 cursor-pointer"></i>
                </button>

                <button onclick="scrollBrands(200)"
                    class="absolute -right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center shadow-md z-10 hover:bg-gray-50 transition-all">
                    <i class="fas fa-chevron-right text-xs text-gray-600 cursor-pointer"></i>
                </button>

                <!-- Brands Scroll Area -->
                <div id="brand-track" class="flex items-start gap-8 overflow-x-auto no-scrollbar scroll-smooth">
                    @foreach ($brands as $brand)
                        @if (!empty($brand->slug))
                            <a href="{{ route('brand.products', ['slug' => $brand->slug]) }}"
                                class="flex flex-col items-center min-w-[105px] group">

                                <!-- Circular Image Wrapper -->
                                <div
                                    class="w-24 h-24 rounded-full overflow-hidden mb-3 border border-gray-100 bg-white flex items-center justify-center p-2">
                                    <img src="{{ $brand->logo_url ?? asset('./images/template1/frontend/default.webp') }}"
                                        onerror="this.onerror=null;this.src='{{ asset('./images/template1/frontend/default.webp') }}';"
                                        class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                        alt="{{ $brand->name }}">
                                </div>

                                <!-- Brand Name -->
                                <span class="text-sm font-semibold text-gray-800 text-center truncate w-full px-1">
                                    {{ $brand->name }}
                                </span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- YOU MAY LIKE SECTION -->
    <section class="py-6 container mx-auto">
        <div class="bg-white rounded-lg shadow-xs border border-gray-200 p-6">

            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-lg font-bold text-black uppercase tracking-tight">You May Like</h2>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-5">
                @foreach ($popularProducts as $product)
                    <x-template1.product-card :product="$product" />
                @endforeach

            </div>

            <!-- View More Button -->
            <div class="flex justify-center mt-10">
                <a href="{{ route('shop.index') }}"
                    class="bg-[#FF6A00] text-white hover:bg-gray-50 hover:text-[#FF6A00] hover:font-semibold text-[#ff9800] font-semibold py-2 px-4 rounded-md transition-colors shadow-sm text-sm">
                    View More
                </a>
            </div>

        </div>
    </section>
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

        if (mainSlider && mainDots.length > 0) {
            function slideMain() {
                mainIdx = (mainIdx + 1) % mainDots.length;
                mainSlider.style.transform = `translateX(-${mainIdx * 100}%)`;
                mainDots.forEach((dot, i) => {
                    if (i === mainIdx) {
                        dot.classList.replace('w-2', 'w-6');
                        dot.classList.replace('bg-white/50', 'bg-[#FF6A00]');
                    } else {
                        dot.classList.replace('w-6', 'w-2');
                        dot.classList.replace('bg-[#FF6A00]', 'bg-white/50');
                    }
                });
            }
            setInterval(slideMain, 4000);
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
