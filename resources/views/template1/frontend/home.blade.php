@extends('template1.layouts.front')

@section('content')
    <!-- HERO SECTION -->
    <section class="py-6 container mx-auto ">
        <!-- Main 3-Column Layout -->
        <div class="flex flex-col lg:flex-row gap-4 items-stretch h-auto lg:h-[480px]">

            <!-- 1. LEFT SIDEBAR: Scrollable Categories (260px wide) -->
            <div class="hidden lg:block w-[260px] shrink-0">
                <div class="bg-white rounded-2xl shadow-sm h-full overflow-y-auto custom-scrollbar">
                    <div class="p-2 space-y-1">

                        @foreach($categories as $category)
                            @if($category->subCategories->count() > 0)
                                <!-- CATEGORY WITH DROPDOWN -->
                                <div class="category-item">
                                    <button onclick="toggleDropdown('cat-{{ $category->id }}')"
                                        class="w-full flex items-center justify-between p-3 hover:bg-gray-50 rounded-xl transition-all group">
                                        <div class="flex items-center gap-3">
                                            <!-- ইমেজ পাথ আপনার ডাটাবেজ অনুযায়ী চেক করে নিবেন -->
                                            <img src="{{ asset('storage/' . $category->image) ?? 'https://via.placeholder.com/150' }}"
                                                class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="{{ $category->name }}">
                                            <span class="text-[14px] font-bold text-gray-800">{{ $category->name }}</span>

                                        </div>
                                        <i id="icon-cat-{{ $category->id }}"
                                            class="fas fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"></i>
                                    </button>

                                    <div id="menu-cat-{{ $category->id }}" class="overflow-hidden transition-all duration-300 max-h-0">
                                        <div class="flex flex-col pb-2">
                                            @foreach($category->subCategories as $subCategory)
                                                <a href="{{ url('category/' . $subCategory->slug) }}"
                                                    class="py-2 pl-14 text-[13px] text-gray-600 hover:text-[#FF6A00] transition-colors">
                                                    {{ $subCategory->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <!-- ITEM WITHOUT DROPDOWN -->
                                <a href="{{ url('category/' . $category->slug) }}" class="flex items-center gap-3 p-3 hover:bg-gray-50 rounded-xl transition-all">
                                    <img src="{{ $category->image ? asset('storage/' . $category->image) : asset('./images/template1/frontend/default.webp')  }}"
                                        class="w-8 h-8 rounded-full object-cover border border-gray-100" alt="{{ $category->name }}">
                                    <span class="text-[14px] font-bold text-gray-800">{{ $category->name }}</span>
                                </a>
                            @endif
                        @endforeach

                    </div>
                </div>
            </div>

            <!-- 2. CENTER: Main Horizontal Auto-Slider -->
            <div class="flex-1 min-w-0 h-[300px] md:h-[400px] lg:h-full">
                <div class="relative h-full w-full rounded-2xl overflow-hidden shadow-sm bg-white">
                    <div id="main-slider" class="flex transition-transform duration-700 ease-in-out h-full w-full">
                        <div class="min-w-full h-full"><img src="{{ asset('images/template1/frontend/hero1.jpg') }}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{asset('images/template1/frontend/hero2.jpg')}}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{asset('images/template1/frontend/hero3.jpg')}}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{asset('images/template1/frontend/hero4.jpg')}}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{asset('images/template1/frontend/hero-right65.jpg')}}"
                                class="w-full h-full object-cover"></div>
                        <div class="min-w-full h-full"><img src="{{asset('images/template1/frontend/hero1.jpg')}}"
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
                <div class="relative h-full rounded-2xl overflow-hidden bg-white">
                    <!-- Vertical Slider Container (৫টি ইমেজ) -->
                    <div id="vertical-slider"
                        class="flex flex-col transition-transform duration-700 ease-in-out h-full w-full">
                        <!-- Banner 1 -->
                        <div class="min-h-full w-full">
                            <img src="{{asset('images/template1/frontend/hero-right1.jpg')}}" class="w-full h-full object-cover rounded-2xl">
                        </div>
                        <!-- Banner 2 -->
                        <div class="min-h-full w-full">
                            <img src="{{asset('images/template1/frontend/hero-right2.jpg')}}" class="w-full h-full object-cover rounded-2xl">
                        </div>
                        <!-- Banner 3 -->
                        <div class="min-h-full w-full">
                            <img src="{{asset('images/template1/frontend/hero-right3.jpg')}}" class="w-full h-full object-cover rounded-2xl">
                        </div>
                        <!-- Banner 4 -->
                        <div class="min-h-full w-full">
                            <img src="{{asset('images/template1/frontend/hero-right4.jpg')}}" class="w-full h-full object-cover rounded-2xl">
                        </div>
                        <!-- Banner 5 -->
                        <div class="min-h-full w-full">
                            <img src="{{asset('images/template1/frontend/hero-right65.jpg')}}" class="w-full h-full object-cover rounded-2xl">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- TOP CATEGORIES SECTION -->
    <section class="py-6 container mx-auto">
        <!-- Main Card Container -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">

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
                    @foreach($categories as $category)
                    <a href="{{ url('category/' . $category->slug) }}" class="flex flex-col items-center min-w-[105px] group">
                        <div class="w-24 h-24 rounded-full overflow-hidden mb-3 border border-gray-100">
                            <img src="{{ !empty($category->image) ? asset('storage/' . $category->image) : asset('./images/template1/frontend/default.webp') }}"
                                onerror="this.onerror=null;this.src='{{ asset('./images/template1/frontend/default.webp') }}';"
                                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                alt="{{ $category->name }}">
                        </div>
                        <span class="text-[14px] font-semibold text-gray-800 text-center truncate w-full px-1">
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
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 relative">

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
            <div class="relative group">

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

                <!-- NEW ARRIVALS লুপের ভেতর -->
                @foreach($newArrivals as $product)
                <div class="flex-shrink-0 w-[240px] flex flex-col group/card cursor-pointer">
                    <a href="{{ url('product/' . $product->slug) }}" class="block">

                        <div class="w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                            <img src="{{ $product->thumbnail_url ?? asset('./images/template1/frontend/default.webp') }}"
                                onerror="this.src='{{ asset('./images/template1/frontend/default.webp') }}'"
                                class="w-full h-full object-cover group-hover/card:scale-105 transition-transform duration-500"
                                alt="{{ $product->title }}">
                        </div>

                        <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2 px-1">
                            {{ $product->title }}
                        </h3>

                        <!-- প্রাইস সেকশন (মডেলের Accessor ব্যবহার করে) -->
                        <div class="flex items-center gap-2 mb-2 px-1">
                            @if($product->type === 'single')
                                <span class="text-xl font-bold text-[#f15a24]">৳{{ number_format($product->sale_price, 0) }}</span>
                                @if($product->discount > 0)
                                    <span class="text-sm text-gray-400 line-through">৳{{ number_format($product->regular_price, 0) }}</span>
                                @endif
                            @else
                                @php $firstVar = $product->variations->first(); @endphp
                                @if($firstVar)
                                    <span class="text-xl font-bold text-[#f15a24]">৳{{ number_format($firstVar->regular_price - ($firstVar->discount_type == 'flat' ? $firstVar->discount : ($firstVar->regular_price * $firstVar->discount / 100)), 0) }}</span>
                                @endif
                            @endif
                        </div>
                    </a>
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

    <!-- DUAL BANNER SECTION -->
    <section class="py-6 container mx-auto">
        <div class="flex flex-row gap-3 md:gap-5">

            <!-- Left Banner -->
            <div
                class="flex-1 overflow-hidden rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 cursor-pointer">
                <img src="https://orenmart.sgp1.digitaloceanspaces.com/banner/da1e494c-e05c-4725-b0cf-c90e56ccba1f.jpg"
                    alt="Promo Banner 1" loading="lazy"
                    class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-500">
            </div>

            <!-- Right Banner -->
            <div
                class="flex-1 overflow-hidden rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 cursor-pointer">
                <img src="https://orenmart.sgp1.digitaloceanspaces.com/banner/2e43fa93-a2ca-4371-bd4f-b6b4d4dca46e.jpg"
                    alt="Promo Banner 2" loading="lazy"
                    class="w-full h-full object-cover hover:scale-[1.02] transition-transform duration-500">
            </div>

        </div>
    </section>

    <!-- POPULAR BRANDS SECTION -->
    <section class="py-6 container mx-auto">
        <!-- Main Card Container -->
        <div class="bg-white rounded-lg shadow-xs border border-gray-200 p-6 relative">

            <!-- Header -->
            <div class="flex items-center mb-8">
                <h2 class="text-[17px] font-bold text-black uppercase tracking-tight">Popular Brands</h2>
            </div>

            <!-- Brands Slider -->
            <div class="relative group">

                <!-- Left Arrow -->
                <button onclick="scrollBrands(-400)"
                    class="cursor-pointer absolute left-1 top-[38%] -translate-y-1/2 z-20 w-8 h-8 bg-white border border-gray-200 rounded-full shadow-md flex items-center justify-center hover:bg-gray-50 transition-all text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>

                <!-- Brand Track: Gap increased to 8 for more spacing -->
                <div id="brand-track" class="flex overflow-x-auto scroll-smooth no-scrollbar py-2 gap-8">
                    @foreach($brands as $brand)
                    <a href="{{ url('brand/' . $brand->slug) }}"
                        class="flex-shrink-0 w-[calc(11.11%-28px)] min-w-[100px] flex flex-col items-center group/brand">
                        <div class="w-full aspect-square rounded-full border border-gray-100 p-5 flex items-center justify-center bg-white transition-all duration-300 hover:border-gray-300">
                            <!-- আপনার মডেলের logo_url অ্যাক্সেসর ব্যবহার করা হয়েছে -->
                            <img src="{{ $brand->logo_url ?? asset('./images/template1/frontend/default.webp') }}"
                                alt="{{ $brand->name }}"
                                class="w-full max-h-full object-contain rounded-full">
                        </div>
                        <h4 class="mt-4 text-md font-semibold text-gray-800 text-center">{{ $brand->name }}</h4>
                    </a>
                    @endforeach

                </div><!-- /#brand-track -->

                <!-- Right Arrow -->
                <button onclick="scrollBrands(400)"
                    class="cursor-pointer absolute right-1 top-[38%] -translate-y-1/2 z-20 w-8 h-8 bg-white border border-gray-200 rounded-full shadow-md flex items-center justify-center hover:bg-gray-50 transition-all text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                        stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

            </div>
        </div>
    </section>

    <!-- YOU MAY LIKE SECTION -->
    <section class="py-6 container mx-auto">
        <div class="bg-white rounded-lg shadow-xs border border-gray-200 p-6">

            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-[18px] font-bold text-black uppercase tracking-tight">You May Like</h2>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6">
                @foreach($popularProducts as $product)
                <a href="{{ url('product/' . $product->slug) }}" class="group flex flex-col cursor-pointer">

                    <div class="relative w-full aspect-square overflow-hidden rounded-lg border border-gray-100 mb-3 bg-[#f9f9f9]">
                        <img src="{{ $product->thumbnail_url ?? asset('./images/template1/frontend/default.webp') }}"
                            onerror="this.src='{{ asset('./images/template1/frontend/default.webp') }}'"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="{{ $product->title }}">

                        @if($product->total_sales > 0)
                        <div class="absolute top-2 left-2 bg-black/70 text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm">
                            {{ $product->total_sales }} Sold
                        </div>
                        @endif
                    </div>

                    <h3 class="text-[14px] leading-[1.4] text-gray-600 font-medium line-clamp-2 h-[40px] mb-2">
                        {{ $product->title }}
                    </h3>

                    <div class="flex flex-col mt-auto gap-1">
                        <div class="flex items-center gap-2">
                            <span class="text-[17px] font-bold text-[#f15a24]">৳{{ number_format($product->sale_price, 0) }}</span>

                            @if($product->discount > 0)
                            <span class="text-[13px] text-gray-400 line-through">৳{{ number_format($product->regular_price, 0) }}</span>
                            @endif
                        </div>

                        @if($product->brand)
                        <div class="mt-1">
                            <span class="bg-[#f15a24] text-white text-[11px] font-bold px-2 py-0.5 rounded-xs shadow-sm inline-block uppercase">
                                {{ $product->brand->name }}
                            </span>
                        </div>
                        @endif
                    </div>
                </a>
                @endforeach

            </div>

            <!-- View More Button -->
            <div class="flex justify-center mt-10">
                <button
                    class="bg-[#FF6A00] text-white hover:bg-gray-50 hover:text-[#FF6A00] hover:font-semibold text-[#ff9800] font-semibold py-2 px-4 rounded-md transition-colors shadow-sm text-sm">
                    View More
                </button>
            </div>

        </div>
    </section>
@endsection
@push('scripts')
<script>
    // ১. ক্যাটাগরি স্লাইডার (Top Categories)
    function scrollCats(distance) {
        const slider = document.getElementById('cat-slider');
        slider.scrollBy({ left: distance, behavior: 'smooth' });
    }

    // ২. নিউ অ্যারাইভাল স্লাইডার (New Arrivals)
    function scrollNA(distance) {
        const track = document.getElementById('na-track');
        track.scrollBy({ left: distance, behavior: 'smooth' });
    }

    // ৩. ব্র্যান্ড স্লাইডার (Popular Brands)
    function scrollBrands(distance) {
        const track = document.getElementById('brand-track');
        track.scrollBy({ left: distance, behavior: 'smooth' });
    }

    // ৪. ক্যাটাগরি ড্রপডাউন (Left Sidebar)
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

    // ৫. হিরো সেকশন স্লাইডার (Main Slider)
    const mainSlider = document.getElementById('main-slider');
    const mainDots = document.querySelectorAll('.main-dot');
    let mainIdx = 0;

    if(mainSlider && mainDots.length > 0) {
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

    // ৬. ভার্টিক্যাল স্লাইডার (Right Sidebar Banner)
    const verticalSlider = document.getElementById('vertical-slider');
    if(verticalSlider) {
        let vertIdx = 0;
        const totalVert = verticalSlider.children.length;
        function slideVertical() {
            vertIdx = (vertIdx + 1) % totalVert;
            verticalSlider.style.transform = `translateY(-${vertIdx * 100}%)`;
        }
        setInterval(slideVertical, 5000);
    }
</script>
@endpush
