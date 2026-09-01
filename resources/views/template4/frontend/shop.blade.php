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
                                <a href="{{ route('category.products', $item['slug']) }}" class="hover:text-[var(--primary-color)] transition-colors">
                                    {{ $item['name'] }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                </nav>
                <div class="meta_info py-4">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-3">
                        {{ $category->name ?? 'Shop' }} Price in Bangladesh
                    </h1>

                    <div class="text-sm md:text-base text-gray-600 leading-relaxed max-w-5xl">
                        @if (isset($category->meta_description) && $category->meta_description)
                            <p>{!! $category->meta_description ?? '' !!}</p>
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

                <!-- ② Filter By Brand (Select Dropdown) -->
                @if ($brands->isNotEmpty())
                    <div>
                        <label for="mobile-brand-select" class="block text-sm font-bold text-gray-800 uppercase tracking-wider mb-2">Select
                            Brand</label>
                        <select id="mobile-brand-select" name="brand[]"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)]">
                            <option value="">All Brands</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ in_array($brand->id, (array) request('brand')) ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- ③ Dynamic Attributes (Size, Color, etc. from $attributeGroups) -->
                @foreach ($attributeGroups as $group)
                    <div>
                        <label for="" class="block text-sm font-bold text-gray-800 uppercase tracking-wider mb-2">Filter By
                            {{ $group->name }}</label>
                        <select name="attributes[{{ $group->id }}][]" aria-label="Filter By {{ $group->name }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[var(--primary-color)]">
                            <option value="">All {{ $group->name }}s</option>
                            @foreach ($group->values as $value)
                                <option value="{{ $value->id }}"
                                    {{ isset(request('attributes')[$group->id]) && in_array($value->id, request('attributes')[$group->id]) ? 'selected' : '' }}>
                                    {{ $value->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

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
                <h2 class="text-xl font-bold text-[#0f172a] border-b border-gray-50 pb-4 mb-6 uppercase tracking-wider">
                    All Categories
                </h2>

                <div class="space-y-2">
                    @foreach ($headerCategories as $mega)
                        <div class="category-item border-b border-gray-50 last:border-0 pb-1">
                            <div class="flex justify-between items-center py-2 group">
                                <a href="{{ route('category.products', $mega->slug) }}" aria-label="{{ $mega->name }}"
                                    class="text-base font-bold text-gray-700 hover:text-[var(--primary-color)] transition-colors uppercase">
                                    {{ $mega->name }}
                                </a>

                                @if ($mega->subCategories->count() > 0)
                                    <button
                                        class="toggle-btn w-8 h-8 flex items-center justify-center rounded-lg hover:bg-purple-50 transition cursor-pointer"
                                        onclick="toggleAccordion('cat-{{ $mega->id }}', this)">
                                        <span class="icon text-lg text-gray-400 font-light">+</span>
                                    </button>
                                @endif
                            </div>

                            {{-- ২. সাব-ক্যাটাগরি লিস্ট --}}
                            @if ($mega->subCategories->count() > 0)
                                <div id="cat-{{ $mega->id }}"
                                    class="hidden pl-4 space-y-2 mt-1 pb-4 transition-all duration-300">
                                    @foreach ($mega->subCategories as $sub)
                                        <div class="sub-category-item">
                                            <div class="flex justify-between items-center py-1.5 group/sub">
                                                <a href="{{ route('category.products', $sub->slug) }}" aria-label="sub category"
                                                    class="text-sm font-semibold text-gray-600 hover:text-[var(--primary-color)] transition-colors">
                                                    {{ $sub->name }}
                                                </a>

                                                @if ($sub->miniCategories->count() > 0)
                                                    <button
                                                        class="w-6 h-6 flex items-center justify-center text-gray-300 hover:text-[var(--primary-color)] cursor-pointer"
                                                        onclick="toggleAccordion('sub-{{ $sub->id }}', this)">
                                                        <span class="sub-icon text-base font-light">+</span>
                                                    </button>
                                                @endif
                                            </div>

                                            {{-- ৩. মিনি ক্যাটাগরি লিস্ট --}}
                                            @if ($sub->miniCategories->count() > 0)
                                                <div id="sub-{{ $sub->id }}"
                                                    class="hidden pl-4 space-y-1.5 mt-1 pb-2 border-l border-gray-100 ml-1">
                                                    @foreach ($sub->miniCategories as $mini)
                                                        <a href="{{ route('category.products', $mini->slug) }}" aria-label="mini category"
                                                            class="block text-sm text-gray-500 hover:text-[var(--primary-color)] py-1 transition-all hover:pl-1">
                                                            {{ $mini->name }}
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
            </aside>



            <!-- ════════════════════════════════════════
                             RIGHT SIDE: TOP FILTER BAR & CARD GRID CONTAINER (col-span-9)
                            ════════════════════════════════════════ -->
            <div class="lg:col-span-9 w-full overflow-hidden">
                <!-- Top Filter Bar & Sorting (Side-by-Side on Mobile) -->
                <div class="bg-[#F5F5F5] rounded-lg px-2 sm:px-4 py-2 sm:py-4 mb-4 md:mb-6">
                    <div class="flex flex-row items-center justify-between gap-2 sm:gap-4">
                        <!-- flex-1 ensures it shares 50% width on mobile -->
                        <form action="{{ url()->current() }}" method="GET" id="desktop-filter-form">
                            <input type="hidden" name="sort" value="{{ request('sort', 'default') }}">
                            <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                            <input type="hidden" name="max_price" value="{{ request('max_price') }}">

                            <div class="flex items-center gap-3 ">

                                <div class="hidden lg:flex flex-wrap items-center gap-3">

                                    {{-- ১. ডাইনামিক অ্যাট্রিবিউট লুপ --}}
                                    @foreach ($attributeGroups as $group)
                                        @php
                                            $selectedIds = request('attributes')[$group->id] ?? [];
                                            // সিলেক্ট করা আইডিগুলো থেকে নামগুলো বের করা হচ্ছে
                                            $selectedNames = $group->values
                                                ->whereIn('id', $selectedIds)
                                                ->pluck('name')
                                                ->toArray();
                                            // যদি কিছু সিলেক্ট থাকে তবে নামগুলো দেখাবে, নাহলে মেইন গ্রুপ নেম
                                            $displayText =
                                                count($selectedNames) > 0
                                                    ? implode(', ', $selectedNames)
                                                    : $group->name;
                                            $isActive = count($selectedNames) > 0;
                                        @endphp

                                        <div class="relative group">
                                            <button type="button"
                                                class="border {{ $isActive ? 'border-[var(--primary-color)] bg-purple-50 text-[var(--primary-color)]' : 'border-gray-300 bg-white text-gray-700' }} rounded px-4 py-2 text-sm font-bold uppercase tracking-wider flex items-center gap-2 hover:bg-gray-50 transition cursor-pointer max-w-[180px]">

                                                {{-- নাম এখানে দেখানো হচ্ছে এবং বেশি বড় হলে ডট ডট হবে --}}
                                                <span class="truncate">{{ $displayText }}</span>

                                                <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-[var(--primary-color)]' : 'text-gray-500' }} group-hover:rotate-180 transition-transform shrink-0"
                                                    fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>

                                            <div class="absolute left-0 mt-0 pt-2 w-48 hidden group-hover:block z-[100]">
                                                <div
                                                    class="bg-white border border-gray-200 rounded-xl shadow-2xl overflow-hidden divide-y divide-gray-50 max-h-64 overflow-y-auto">
                                                    @foreach ($group->values as $value)
                                                        @php $isSelected = in_array($value->id, $selectedIds); @endphp
                                                        <label
                                                            class="flex items-center justify-between px-5 py-3 text-sm font-semibold {{ $isSelected ? 'text-[var(--primary-color)] bg-purple-50' : 'text-gray-600' }} hover:bg-gray-50 cursor-pointer transition">
                                                            <span>{{ $value->name }}</span>
                                                            <input type="checkbox"
                                                                name="attributes[{{ $group->id }}][]"
                                                                value="{{ $value->id }}" onchange="this.form.submit()"
                                                                {{ $isSelected ? 'checked' : '' }}
                                                                class="w-4 h-4 accent-[var(--primary-color)] cursor-pointer">
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- ২. ডাইনামিক ব্র্যান্ড ফিল্টার --}}
                                    @if ($brands->isNotEmpty())
                                        @php
                                            $selectedBrandIds = (array) request('brand');
                                            $selectedBrandNames = $brands
                                                ->whereIn('id', $selectedBrandIds)
                                                ->pluck('name')
                                                ->toArray();
                                            $brandText =
                                                count($selectedBrandNames) > 0
                                                    ? implode(', ', $selectedBrandNames)
                                                    : 'Brand';
                                            $isBrandActive = count($selectedBrandNames) > 0;
                                        @endphp
                                        <div class="relative group">
                                            <button type="button"
                                                class="border {{ $isBrandActive ? 'border-[var(--primary-color)] bg-purple-50 text-[var(--primary-color)]' : 'border-gray-300 bg-white text-gray-700' }} rounded px-4 py-2 text-sm font-bold uppercase tracking-wider flex items-center gap-2 hover:bg-gray-50 transition cursor-pointer max-w-[180px]">
                                                <span class="truncate">{{ $brandText }}</span>
                                                <svg class="w-3.5 h-3.5 {{ $isBrandActive ? 'text-[var(--primary-color)]' : 'text-gray-500' }} group-hover:rotate-180 transition-transform shrink-0"
                                                    fill="none" stroke="currentColor" stroke-width="2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <div class="absolute left-0 mt-0 pt-2 w-56 hidden group-hover:block z-[100]">
                                                <div
                                                    class="bg-white border border-gray-200 rounded-xl shadow-2xl overflow-hidden divide-y divide-gray-50 max-h-64 overflow-y-auto">
                                                    @foreach ($brands as $brand)
                                                        @php $isBrandSelected = in_array($brand->id, $selectedBrandIds); @endphp
                                                        <label
                                                            class="flex items-center justify-between px-5 py-3 text-sm font-semibold {{ $isBrandSelected ? 'text-[var(--primary-color)] bg-purple-50' : 'text-gray-600' }} hover:bg-gray-50 cursor-pointer transition">
                                                            <span>{{ $brand->name }}</span>
                                                            <input type="checkbox" name="brand[]"
                                                                value="{{ $brand->id }}" onchange="this.form.submit()"
                                                                {{ $isBrandSelected ? 'checked' : '' }}
                                                                class="w-4 h-4 accent-[var(--primary-color)] cursor-pointer">
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if (request()->has('attributes') || request()->has('brand'))
                                        <a href="{{ url()->current() }}" aria-label="attributes name"
                                            class="text-xs font-black text-red-500 hover:underline uppercase tracking-widest ml-2">
                                            Clear All
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <!-- Right Side: Native Sort By Dropdown -->
                        <div class="flex-1 lg:flex-none flex items-center justify-end">
                            <label for="input-sort"
                                class="hidden xl:block text-sm font-bold text-gray-700 uppercase mr-3 shrink-0">
                                Sort By :
                            </label>

                            <div class="relative w-full lg:w-56">
                                <form action="" method="GET" id="sortForm">
                                    <select name="sort" onchange="document.getElementById('sortForm').submit()"
                                        aria-label="Sort products"
                                        class="w-full appearance-none bg-white border border-gray-200 text-gray-600 text-sm md:text-base rounded-md pr-10 pl-3 py-2 outline-none focus:ring-1 focus:ring-[var(--primary-color)] cursor-pointer shadow-sm">
                                        <option value="default" class=""
                                            {{ request('sort') == 'default' ? 'selected' : '' }}>
                                            Default Sorting</option>
                                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>
                                            Price: Low to High</option>
                                        <option value="price_high"
                                            {{ request('sort') == 'price_high' ? 'selected' : '' }}>
                                            Price: High to Low</option>
                                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest
                                            First</option>
                                    </select>
                                </form>

                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
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
                <div class="flex justify-center sm:justify-end mt-8 md:mt-12 mb-4 md:mb-6">
                    {{ $products->appends(request()->query())->links('components.template1.custom-pagiantion') }}
                </div>
                @if (isset($category->description) && $category->description)
                    <section class="mt-12  border-t border-gray-100">
                        <div class="prose prose-slate max-w-none text-gray-700 leading-relaxed">
                            {{-- <h2 class="text-2xl font-bold text-gray-800 mb-6">{{ $category->name }}</h2> --}}
                            {!! $category->description !!}
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
        document.addEventListener("DOMContentLoaded", function() {
            // 1. SIDEBAR ACCORDION TOGGLE (MAIN CATEGORIES)
            const categoryHeaders = document.querySelectorAll(".category-header");

            categoryHeaders.forEach((header) => {
                header.addEventListener("click", function() {
                    const targetId = this.getAttribute("data-target");
                    const content = document.getElementById(targetId);
                    const icon = this.querySelector(".icon");

                    if (content.classList.contains("hidden")) {
                        content.classList.remove("hidden");
                        icon.textContent = "-";
                        this.classList.add("text-[var(--primary-color)]", "font-bold");
                    } else {
                        content.classList.add("hidden");
                        icon.textContent = "+";
                        this.classList.remove("text-[var(--primary-color)]", "font-bold");
                        this.classList.add("text-gray-700");
                    }
                });
            });

            // 2. SIDEBAR SUB-ACCORDION TOGGLE (SUB-CATEGORIES)
            const subcategoryHeaders = document.querySelectorAll(
                ".subcategory-header",
            );

            subcategoryHeaders.forEach((subHeader) => {
                subHeader.addEventListener("click", function() {
                    const targetId = this.getAttribute("data-target");
                    const content = document.getElementById(targetId);
                    const icon = this.querySelector(".sub-icon");

                    if (content.classList.contains("hidden")) {
                        content.classList.remove("hidden");
                        icon.textContent = "-";
                        this.classList.add("text-[var(--primary-color)]", "font-bold");
                    } else {
                        content.classList.add("hidden");
                        icon.textContent = "+";
                        this.classList.remove("text-[var(--primary-color)]", "font-bold");
                        this.classList.add("text-gray-700");
                    }
                });
            });

            // 3. TOP BAR FILTER DROPDOWNS CONTROL
            const dropdownContainers = document.querySelectorAll(
                ".filter-dropdown-container",
            );

            dropdownContainers.forEach((container) => {
                const btn = container.querySelector(".filter-dropdown-btn");
                const menu = container.querySelector(".filter-menu");

                btn.addEventListener("click", function(event) {
                    event.stopPropagation();

                    // Close all other dropdown menus except this one
                    dropdownContainers.forEach((c) => {
                        const otherMenu = c.querySelector(".filter-menu");
                        if (c !== container && otherMenu) {
                            otherMenu.classList.add("hidden");
                        }
                    });

                    // Toggle visibility of current dropdown menu
                    if (menu) {
                        menu.classList.toggle("hidden");
                    }
                });
            });

            // Close dropdowns if clicked outside
            document.addEventListener("click", function() {
                dropdownContainers.forEach((container) => {
                    const menu = container.querySelector(".filter-menu");
                    if (menu) {
                        menu.classList.add("hidden");
                    }
                });
            });
        });
    </script>
@endpush
