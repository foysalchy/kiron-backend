@extends('template4.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.product-meta', [
        'setup'         => $setup,
        'megaCategory'  => $megaCategory ?? null,
        'subCategory'   => $subCategory ?? null,
        'miniCategory'  => $miniCategory ?? null,
    ])
@endsection
@section('content')
<section class="bg-white border-t-1 border-t border-gray-300 pb-4">
        <div class="py-2 md:py-2 container mx-auto px-4 lg:px-0">
            <!-- Main Card Container -->
            <div class="archiveTopInfo">

                <!-- Breadcrumb -->
                <nav class="hidden md:flex items-center gap-2 mb-4 text-sm font-medium text-gray-500 overflow-x-auto no-scrollbar whitespace-nowrap">
                    <a href="{{ route('home') }}" class="hover:text-[var(--primary-color)] transition-colors flex items-center gap-1">
                        <i class="fas fa-home text-xs"></i> Home
                    </a>

                    @if(isset($breadcrumb) && count($breadcrumb) > 0)
                        @foreach($breadcrumb as $item)
                            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" class="w-4 h-4 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                            </svg>

                            @if($loop->last)
                                <span class="text-[var(--primary-color)] font-bold">{{ $item['name'] }}</span>
                            @else
                                <a href="{{ url($item['slug']) }}" class="hover:text-[var(--primary-color)] transition-colors">
                                    {{ $item['name'] }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                </nav>
                <div class="meta_info py-4">
                    @php
                        $pageData = null;
                        if (!($category && $category->name)) {
                            $pageData = \App\Services\Saas\SystemPageService::get(
                                \App\Enums\SystemPageType::SHOP,
                                $setup->company_id ?? null
                            );
                        }
                    @endphp

                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-3">
                        @if($category && $category->name)
                            {{ $category->name }} Price in Bangladesh
                        @elseif($pageData && $pageData->meta_title)
                            {{ $pageData->meta_title }}
                        @else
                            Shop Price in Bangladesh
                        @endif
                    </h1>

                    <div class="text-sm md:text-base text-gray-600 leading-relaxed max-w-5xl">
                        @if (isset($category->meta_description) && $category->meta_description)
                            <p>{!! $category->meta_description ?? '' !!}</p>
                        @elseif ($pageData && $pageData->meta_description)
                            <p>{!! $pageData->meta_description !!}</p>
                        @else
                            @php
                                $isL = ($setup->currency_position ?? 'left') == 'left';
                                $currency = $setup->currency;

                                $minP = $products->min('sale_price') > 0 ? $products->min('sale_price') : $products->min('regular_price');
                                $maxP = $products->max('sale_price') > 0 ? $products->max('sale_price') : $products->max('regular_price');
                                $catName = $category->name ?? 'Product';
                            @endphp

                            {{ $catName }} price in Bangladesh range from
                            <span class="font-bold text-gray-800">
                                {{ $isL ? $currency : '' }} {{ number_format($minP) }} {{ !$isL ? $currency : '' }}
                            </span> to
                            <span class="font-bold text-gray-800">
                                {{ $isL ? $currency : '' }} {{ number_format($maxP) }} {{ !$isL ? $currency : '' }}
                            </span>,
                            depending on size, material, design, and features. Visit <strong>{{ $setup->shop_name }}</strong> and compare options to find the best {{ strtolower($catName) }} at lowest price in BD.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Mobile Filter Overlay (Right Drawer) -->
    <div id="right-filter-overlay" class="fixed inset-0 bg-black/60 z-[60] hidden lg:hidden transition-opacity"
        aria-hidden="true"></div>

    <!-- ════════════════════════════════════════
         RIGHT SIDEBAR FILTER DRAWER (Mobile Only)
        ════════════════════════════════════════ -->
    <aside id="right-filter-drawer" id="category-menu"
        class="fixed inset-y-0 right-0 z-[70] w-[280px] sm:w-[320px] h-full bg-white shadow-2xl transform translate-x-full transition-transform duration-300 overflow-y-auto lg:hidden flex flex-col ">

        <!-- Drawer Header -->
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-5 mb-2">
            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide">
                Filter Products
            </h2>
            <button id="close-right-filter-btn"
                class="text-3xl leading-none text-gray-400 hover:text-[var(--primary-color)] focus:outline-none transition">
                &times;
            </button>
        </div>

        <!-- Mobile Filter Form -->
        <form action="{{ url()->current() }}" method="GET" class="flex flex-col flex-1">
            <input type="hidden" name="sort" value="{{ request('sort', 'default') }}">

            <div class="px-4 py-2 space-y-6 flex-1">

                <!-- ① Filter By Price (Min/Max Input) -->
                <div>
                    <label class="block text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">Price Range
                        ({{ $setup->currency }})</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="min_price" placeholder="Min" value="{{ request('min_price', 0) }}"
                            class="w-1/2 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[var(--primary-color)]">
                        <span class="text-gray-300">-</span>
                        <input type="number" name="max_price" placeholder="Max"
                            value="{{ request('max_price', (int) $maxPriceLimit) }}"
                            class="w-1/2 border border-gray-200 rounded-lg px-3 py-2 text-sm outline-none focus:border-[var(--primary-color)]">
                    </div>
                </div>

                <!-- Availability Filter (Mobile) -->
                <div>
                    <label class="block text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">Availability</label>
                    <div class="flex flex-col gap-2.5 text-gray-800 font-medium">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="availability[]" value="in_stock"
                                {{ in_array('in_stock', (array)request('availability', [])) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 accent-[var(--primary-color)]">
                            <span class="text-sm text-gray-700">In Stock</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="availability[]" value="out_of_stock"
                                {{ in_array('out_of_stock', (array)request('availability', [])) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 accent-[var(--primary-color)]">
                            <span class="text-sm text-gray-700">Out Of Stock</span>
                        </label>
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" name="availability[]" value="upcoming"
                                {{ in_array('upcoming', (array)request('availability', [])) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 accent-[var(--primary-color)]">
                            <span class="text-sm text-gray-700">Up Coming</span>
                        </label>
                    </div>
                </div>


                <!-- ③ Dynamic Filters (RAM, ROM, Material, etc. from $filters) -->
                @if(isset($filters) && $filters->isNotEmpty())
                    @foreach ($filters as $filter)
                        @if($filter->values->isNotEmpty())
                            <div>
                                <label for="" class="block text-sm font-bold text-gray-800 uppercase tracking-wider mb-2">Filter By
                                    {{ $filter->name }}</label>
                                <select name="filter_values[]" aria-label="Filter By {{ $filter->name }}" onchange="this.form.submit()"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)]">
                                    <option value="">All {{ $filter->name }}s</option>
                                    @foreach ($filter->values as $value)
                                        <option value="{{ $value->id }}"
                                            {{ in_array($value->id, (array)request('filter_values', [])) ? 'selected' : '' }}>
                                            {{ $value->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endforeach
                @endif

                <!-- Clear All Option -->
                <div class="pt-2">
                    <a href="{{ url()->current() }}"
                        class="text-xs text-red-500 font-bold hover:underline uppercase tracking-widest min-h-[48px] flex items-center">
                        Clear All Filters
                    </a>
                </div>
            </div>

            <!-- Apply Button (Fixed at Bottom) -->
            <div class="mt-auto p-4 border-t border-gray-100 bg-gray-50">
                <button type="submit" id="apply-filters-btn"
                    class="w-full primary-bg hover:bg-[#52166d] text-white font-bold py-3.5 rounded-xl text-base shadow-lg transition active:scale-95">
                    Apply Filters
                </button>
            </div>
        </form>
    </aside>

    <div class="container mx-auto px-3 sm:px-4 py-4 md:py-8" style="font-family: &quot;Manrope&quot;, sans-serif">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
            <!-- ════════════════════════════════════════
                             LEFT SIDEBAR: CATEGORIES (Desktop Only)
                            ════════════════════════════════════════ -->
            <aside
                class="hidden lg:block lg:col-span-3 bg-white px-5 py-6 border border-gray-100 rounded-2xl shadow-sm h-fit sticky top-24 ">
                <!-- Price & Dynamic Product Filters -->
                <form action="{{ url()->current() }}" method="GET" id="sidebar-filter-form">
                    <input type="hidden" name="sort" value="{{ request('sort', 'default') }}">

                    <!-- ① Filter By Price -->
                    <div class="mb-6">
                        <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">Filter By Price</h3>

                        <!-- Slider Container -->
                        <div id="price-slider" class="relative h-1.5 bg-gray-100 rounded-full mb-6 mx-2 cursor-pointer">
                            <!-- Progress Bar -->
                            <div id="slider-range" class="absolute h-full primary-bg rounded-full"
                                style="left:0%; right:0%;"></div>

                            <!-- Left Handle -->
                            <div id="handle-min"
                                class="absolute w-4 h-4 bg-white rounded-full border-2 border-[var(--primary-color)] -top-1.5 cursor-grab active:cursor-grabbing shadow-sm z-20"
                                style="left:0%;"></div>

                            <!-- Right Handle -->
                            <div id="handle-max"
                                class="absolute w-4 h-4 bg-white rounded-full border-2 border-[var(--primary-color)] -top-1.5 cursor-grab active:cursor-grabbing shadow-sm z-20"
                                style="left:100%; transform: translateX(-100%);"></div>
                        </div>

                        <!-- Inputs -->
                        <div class="flex items-center gap-2 mb-4">
                            <input type="number" id="min_price" name="min_price" aria-label="Minimum Price"
                                value="{{ request('min_price', 0) }}"
                                class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[var(--primary-color)]">
                            <input type="number" id="max_price" name="max_price" aria-label="Maximum Price"
                                value="{{ request('max_price', (int) $maxPriceLimit) }}"
                                class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs outline-none focus:border-[var(--primary-color)]">
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit"
                                class="primary-bg text-white px-4 py-1.5 rounded-lg text-xs font-bold hover:opacity-90 transition-opacity uppercase">
                                Filter
                            </button>
                            <span class="text-[10px] text-gray-500 font-medium">
                                {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}
                                <span id="display-min">{{ request('min_price', 0) }}</span>
                                {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                                —
                                {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}
                                <span id="display-max">{{ request('max_price', (int) $maxPriceLimit) }}</span>
                                {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                            </span>
                        </div>
                    </div>

                    <!-- Availability Filter (Static) -->
                    <div class="border-t border-gray-100 pt-4">
                        <button type="button" onclick="toggleAvailabilityFilter()"
                            class="w-full flex items-center justify-between py-2 hover:text-[var(--primary-color)] transition-colors">
                            <span class="text-sm font-bold text-gray-800 uppercase tracking-wider">Availability</span>
                            <svg id="availability-arrow" class="w-4 h-4 text-gray-500 transition-transform duration-300"
                                style="{{ request()->has('availability') ? 'transform: rotate(180deg);' : '' }}"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="availability-panel" class="{{ request()->has('availability') ? '' : 'hidden' }} space-y-2.5 pt-3">
                            <div class="flex flex-col gap-2.5 text-gray-800 font-medium">
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input type="checkbox" name="availability[]" value="in_stock"
                                        onchange="this.form.submit()"
                                        {{ in_array('in_stock', (array)request('availability', [])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 accent-[var(--primary-color)]">
                                    <span class="text-xs sm:text-sm text-gray-600 group-hover:text-[var(--primary-color)]">In Stock</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input type="checkbox" name="availability[]" value="out_of_stock"
                                        onchange="this.form.submit()"
                                        {{ in_array('out_of_stock', (array)request('availability', [])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 accent-[var(--primary-color)]">
                                    <span class="text-xs sm:text-sm text-gray-600 group-hover:text-[var(--primary-color)]">Out Of Stock</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input type="checkbox" name="availability[]" value="upcoming"
                                        onchange="this.form.submit()"
                                        {{ in_array('upcoming', (array)request('availability', [])) ? 'checked' : '' }}
                                        class="w-4 h-4 rounded border-gray-300 accent-[var(--primary-color)]">
                                    <span class="text-xs sm:text-sm text-gray-600 group-hover:text-[var(--primary-color)]">Up Coming</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- ② More Filters (Accordion) -->
                    @if(isset($filters) && $filters->isNotEmpty())
                        <div class="border-t border-gray-100 pt-4">
                            <button type="button" onclick="toggleAllFilters()"
                                class="w-full flex items-center justify-between py-2 hover:text-[var(--primary-color)] transition-colors">
                                <span class="text-sm font-bold text-gray-800 uppercase tracking-wider">More Filters</span>
                                <svg id="all-filters-arrow" class="w-4 h-4 text-gray-400 transition-transform duration-300"
                                    style="{{ request()->has('filter_values') ? 'transform: rotate(180deg);' : '' }}"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div id="all-filters-panel" class="{{ request()->has('filter_values') ? '' : 'hidden' }} space-y-4 pt-3">
                                @foreach ($filters as $filter)
                                    @if($filter->values->isNotEmpty())
                                        <div class="pb-3 border-b border-gray-50 last:border-0">
                                            <h4 class="text-xs font-bold text-gray-700 uppercase mb-2">Filter By {{ $filter->name }}</h4>
                                            <div class="flex flex-col gap-1.5 text-gray-800 font-medium">
                                                @foreach ($filter->values as $value)
                                                    <label class="flex items-center gap-2 cursor-pointer group">
                                                        <input type="checkbox" name="filter_values[]"
                                                            value="{{ $value->id }}" onchange="this.form.submit()"
                                                            {{ in_array($value->id, (array)request('filter_values', [])) ? 'checked' : '' }}
                                                            class="w-3.5 h-3.5 accent-[var(--primary-color)]">
                                                        <span class="text-xs sm:text-sm text-gray-600 group-hover:text-[var(--primary-color)]">
                                                            {{ $value->name }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @if (request()->has('filter_values'))
                                    <div class="pt-2">
                                        <a href="{{ url()->current() }}"
                                            class="text-xs text-red-500 font-bold hover:underline uppercase">
                                            Clear All Filters
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </form>
            </aside>



            <!-- ════════════════════════════════════════
                             RIGHT SIDE: TOP SORT BAR & CARD GRID CONTAINER (col-span-9)
                            ════════════════════════════════════════ -->
            <div class="lg:col-span-9 w-full overflow-hidden">
                <!-- Top Sort Bar -->
                <div class="bg-white border border-gray-100 rounded-2xl px-4 py-3 mb-6 shadow-sm flex items-center justify-between">
                    <h2 class="text-base md:text-xl font-bold text-gray-900">
                        {{ $category ? $category->name : 'All Products' }}
                    </h2>

                    <!-- Native Sort By Dropdown -->
                    <div class="flex items-center gap-3">
                        <label for="input-sort" class="hidden sm:block text-xs font-bold text-gray-500 uppercase tracking-wider shrink-0">
                            Sort By:
                        </label>
                        <div class="relative w-44 sm:w-52">
                            <form action="" method="GET" id="sortForm">
                                <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                                <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                                @foreach((array)request('availability', []) as $val)
                                    <input type="hidden" name="availability[]" value="{{ $val }}">
                                @endforeach
                                @foreach((array)request('filter_values', []) as $val)
                                    <input type="hidden" name="filter_values[]" value="{{ $val }}">
                                @endforeach
                                <select name="sort" onchange="document.getElementById('sortForm').submit()"
                                    aria-label="Sort products"
                                    class="w-full appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-xs sm:text-sm rounded-xl pr-9 pl-3.5 py-2.5 outline-none focus:ring-1 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] cursor-pointer">
                                    <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Default Sorting</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                                </select>
                            </form>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ════════════════════════════════════════
                                 PRODUCT CARD GRID (2 per row on Mobile, 3 on Desktop)
                                ════════════════════════════════════════ -->
                <div class=" py-2 md:py-4 px-0">
                    <div
                        class="grid grid-cols-2 md:grid-cols-3 gap-x-2 sm:gap-x-4 lg:gap-x-6 gap-y-6 sm:gap-y-8 lg:gap-y-10">
                        @forelse($products as $product)
                            <x-template1.product-card :product="$product" />
                        @empty
                            <div class="col-span-full py-20 text-center">
                                <i class="fas fa-box-open text-5xl text-gray-200 mb-4"></i>
                                <p class="text-gray-500 font-medium">No products found in this category.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="px-3 md:px-4 py-4 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="shrink-0">
                        {{ $products->appends(request()->query())->links('components.template1.custom-pagiantion') }}
                    </div>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} ({{ $products->lastPage() }} Pages)
                    </p>
                </div>
                @if (isset($category->description) && $category->description)
                    <section class="mt-12 border-t border-gray-100">
                        <div class="prose prose-slate max-w-none text-gray-700 leading-relaxed">
                            {!! $category->description !!}
                        </div>
                    </section>
                @elseif (isset($pageData->description) && !empty($pageData->description))
                    <section class="mt-12 border-t border-gray-100 pt-6">
                        <div class="bg-gray-50 border border-gray-100 rounded-lg px-6 py-6 prose prose-slate max-w-none
                            prose-headings:text-[#041533] prose-headings:font-bold
                            prose-h2:text-[24px] md:prose-h2:text-[28px] prose-h2:tracking-tight prose-h2:mb-4
                            prose-h3:text-[22px] md:prose-h3:text-[24px] prose-h3:mb-4
                            prose-p:text-[#4b5563] prose-p:text-base prose-p:leading-[1.7] prose-p:text-justify md:prose-p:text-left prose-p:mb-8">
                            {!! $pageData->description !!}
                        </div>
                    </section>
                @endif
            </div>
        </div>
        <!-- Pagination -->

    </div>
@endsection
@push('scripts')
<script>
    function toggleCategoryMenu() {
        const menu = document.getElementById('category-menu');
        // এটি মোবাইলে ক্লিক করলে টাইটেল ও ডেসক্রিপশনের উপরে ক্যাটাগরি কার্ডটি শো করবে
        menu.classList.toggle('hidden');
    }
</script>
    <script>
        function toggleAccordion(id, btn) {
            const content = document.getElementById(id);
            const icon = btn.querySelector('.icon, .sub-icon');

            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.textContent = '-';
                btn.closest('.flex').querySelector('a').classList.add('text-[var(--primary-color)]');
            } else {
                content.classList.add('hidden');
                icon.textContent = '+';
                btn.closest('.flex').querySelector('a').classList.remove('text-[var(--primary-color)]');
            }
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const filterDrawer = document.getElementById("right-filter-drawer");
            const filterOverlay = document.getElementById("right-filter-overlay");
            const openFilterBtn = document.getElementById("open-right-filter-btn");
            const closeFilterBtn = document.getElementById(
                "close-right-filter-btn",
            );
            const applyFiltersBtn = document.getElementById("apply-filters-btn");

            function toggleFilter() {
                filterDrawer.classList.toggle("translate-x-full");
                filterOverlay.classList.toggle("hidden");
                document.body.classList.toggle("overflow-hidden"); // Prevent background scrolling
            }

            if (openFilterBtn && closeFilterBtn && filterOverlay) {
                openFilterBtn.addEventListener("click", toggleFilter);
                closeFilterBtn.addEventListener("click", toggleFilter);
                filterOverlay.addEventListener("click", toggleFilter);
                applyFiltersBtn.addEventListener("click", toggleFilter);
            }
        });
    </script>
    <script>
        function toggleAvailabilityFilter() {
            const panel = document.getElementById('availability-panel');
            const arrow = document.getElementById('availability-arrow');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            } else {
                panel.classList.add('hidden');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }

        function toggleAllFilters() {
            const panel = document.getElementById('all-filters-panel');
            const arrow = document.getElementById('all-filters-arrow');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            } else {
                panel.classList.add('hidden');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            const panel = document.getElementById('all-filters-panel');
            const arrow = document.getElementById('all-filters-arrow');
            if (panel && !panel.classList.contains('hidden') && arrow) {
                arrow.style.transform = 'rotate(180deg)';
            }

            const availPanel = document.getElementById('availability-panel');
            const availArrow = document.getElementById('availability-arrow');
            if (availPanel && !availPanel.classList.contains('hidden') && availArrow) {
                availArrow.style.transform = 'rotate(180deg)';
            }

            const slider = document.getElementById('price-slider');
            const range = document.getElementById('slider-range');
            const handleMin = document.getElementById('handle-min');
            const handleMax = document.getElementById('handle-max');
            const inputMin = document.getElementById('min_price');
            const inputMax = document.getElementById('max_price');
            const displayMin = document.getElementById('display-min');
            const displayMax = document.getElementById('display-max');

            if (!slider || !handleMin || !handleMax || !inputMin || !inputMax) return;

            const maxLimit = {{ (int) $maxPriceLimit }};

            function updateSlider() {
                let minVal = parseInt(inputMin.value) || 0;
                let maxVal = parseInt(inputMax.value) || maxLimit;

                if (minVal < 0) minVal = 0;
                if (maxVal > maxLimit) maxVal = maxLimit;
                if (minVal > maxVal) minVal = maxVal;

                const minPercent = (minVal / maxLimit) * 100;
                const maxPercent = (maxVal / maxLimit) * 100;

                handleMin.style.left = minPercent + '%';
                handleMax.style.left = maxPercent + '%';

                range.style.left = minPercent + '%';
                range.style.right = (100 - maxPercent) + '%';

                if (displayMin) displayMin.innerText = minVal;
                if (displayMax) displayMax.innerText = maxVal;
            }

            function initDraggable(handle, type) {
                handle.onmousedown = function(event) {
                    document.onmousemove = function(event) {
                        let rect = slider.getBoundingClientRect();
                        let offsetX = event.clientX - rect.left;
                        let percent = Math.min(Math.max((offsetX / rect.width) * 100, 0), 100);
                        let value = Math.round((percent / 100) * maxLimit);

                        if (type === 'min') {
                            if (value < parseInt(inputMax.value)) inputMin.value = value;
                        } else {
                            if (value > parseInt(inputMin.value)) inputMax.value = value;
                        }
                        updateSlider();
                    };
                    document.onmouseup = function() {
                        document.onmousemove = null;
                        document.onmouseup = null;
                    };
                };

                // For Touch devices
                handle.ontouchmove = function(event) {
                    let rect = slider.getBoundingClientRect();
                    let offsetX = event.touches[0].clientX - rect.left;
                    let percent = Math.min(Math.max((offsetX / rect.width) * 100, 0), 100);
                    let value = Math.round((percent / 100) * maxLimit);

                    if (type === 'min') {
                        if (value < parseInt(inputMax.value)) inputMin.value = value;
                    } else {
                        if (value > parseInt(inputMin.value)) inputMax.value = value;
                    }
                    updateSlider();
                };
            }

            initDraggable(handleMin, 'min');
            initDraggable(handleMax, 'max');

            inputMin.oninput = updateSlider;
            inputMax.oninput = updateSlider;

            updateSlider();
        });
    </script>
@endpush
