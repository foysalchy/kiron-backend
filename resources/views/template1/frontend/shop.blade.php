@extends('template1.layouts.front')

@section('content')
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
        <div class="lg:hidden mb-3 flex items-center justify-between">
            <button onclick="toggleMobileSidebar()"
                class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-bold text-gray-700 shadow-xs">
                <i class="fas fa-filter text-[#f15a24]"></i> Filters
            </button>
            <span class="text-xs text-gray-400">{{ $products->total() }} products</span>
        </div>

        <div class="flex flex-col lg:flex-row gap-6"></div>

        <!-- Main Card Container -->
        <div class="archiveTopInfo">

            <!-- Breadcrumb -->
            <nav class="hidden md:flex items-center gap-2 mb-6 text-md font-medium text-gray-500">
                <a href="/" class="hover:text-gray-500 transition-colors">Home</a>

                <!-- Chevron Icon -->
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" class="w-4 h-4 text-gray-500"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                </svg>

                <a href="{{ route('shop.index') }}" class="text-gray-500 hover:text-[#f15a24] transition-colors">
                    {{ request()->routeIs('brand.products') ? 'Brand' : 'Category' }}
                </a>

                <!-- Chevron Icon -->
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" class="w-4 h-4 text-gray-500"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                </svg>

                <span class="text-[#f15a24]">{{ $category->name ?? 'Shop' }}</span>
            </nav>

            <!-- Description Section -->
            @if (isset($category->description) && $category->description)
                {{-- isset  --}}
                <div class="mb-6">
                    @if (request()->routeIs('brand.products'))
                        {{-- brand description --}}
                        <div class="text-sm text-gray-600 leading-relaxed border-l-4 border-orange-500 pl-4 italic">
                            {!! $category->description !!}
                        </div>
                    @else
                        {{-- category description --}}
                        <div class="text-sm text-gray-600 leading-relaxed">
                            {!! $category->description !!}
                        </div>
                    @endif
                </div>
            @endif


        </div>

    </section>
    <!-- SHOP PAGE SECTION -->
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- ══════════════════════════ SIDEBAR ═════════════════════════════ -->
            <aside class="w-full lg:w-[220px] shrink-0" id="shop-sidebar">
                <form action="{{ url()->current() }}" method="GET" id="sidebar-filter-form">
                    <input type="hidden" name="sort" value="{{ request('sort', 'default') }}">

                    <div id="sidebar-filter-box"
                        class="hidden lg:block bg-white rounded-lg shadow-xs border border-gray-200 overflow-hidden lg:sticky lg:top-[70px] lg:max-h-[calc(100vh-90px)] lg:overflow-y-auto no-scrollbar">
                        <!-- ① Filter By Price -->
                        <div class="p-4 border-b border-gray-50">
                            <h3 class="text-sm font-bold text-gray-800 mb-4">Filter By Price</h3>

                            <!-- Slider Container -->
                            <div id="price-slider" class="relative h-1.5 bg-gray-100 rounded-full mb-6 mx-2 cursor-pointer">
                                <!-- Orange Progress Bar -->
                                <div id="slider-range" class="absolute h-full bg-[#f15a24] rounded-full"
                                    style="left:0%; right:0%;"></div>

                                <!-- Left Handle -->
                                <div id="handle-min"
                                    class="absolute w-4 h-4 bg-white rounded-full border-2 border-[#f15a24] -top-1.5 cursor-grab active:cursor-grabbing shadow-sm z-20"
                                    style="left:0%;"></div>

                                <!-- Right Handle -->
                                <div id="handle-max"
                                    class="absolute w-4 h-4 bg-white rounded-full border-2 border-[#f15a24] -top-1.5 cursor-grab active:cursor-grabbing shadow-sm z-20"
                                    style="left:100%; transform: translateX(-100%);"></div>
                            </div>

                            <!-- Inputs -->
                            <div class="flex items-center gap-2 mb-4">
                                <input type="number" id="min_price" name="min_price" value="{{ request('min_price', 0) }}"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs outline-none focus:border-[#f15a24]">
                                <input type="number" id="max_price" name="max_price"
                                    value="{{ request('max_price', (int) $maxPriceLimit) }}"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs outline-none focus:border-[#f15a24]">
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="submit"
                                    class="bg-[#f15a24] text-primary px-4 py-1.5 rounded text-xs font-bold hover:bg-orange-600 transition-colors uppercase">
                                    Filter
                                </button>
                                <span class="text-[10px] text-gray-500 font-medium">
                                    {{ $setup->currency }} <span id="display-min">{{ request('min_price', 0) }}</span> —
                                    {{ $setup->currency }} <span
                                        id="display-max">{{ request('max_price', (int) $maxPriceLimit) }}</span>
                                </span>
                            </div>
                        </div>

                        <!-- ② Filter By Brand header — Toggle Button -->
                        <button type="button" onclick="toggleAllFilters()"
                            class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50">
                            <span class="text-sm font-bold text-gray-800">More Filters</span>
                            <svg id="all-filters-arrow" class="w-4 h-4 text-gray-400 transition-transform duration-300"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- ③ ALL FILTERS PANEL (Initially Hidden) -->
                        <div id="all-filters-panel"
                            class="{{ request()->has('brand') || request()->has('attributes') ? '' : 'hidden' }}">

                            <!-- Brand List -->
                            <div class="px-4 pt-2 pb-3 border-b border-gray-50">
                                <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Brands</h3>
                                <div class="flex flex-col gap-0 text-gray-800 font-medium">
                                    @foreach ($brands as $brand)
                                        @php $isSelectedBrand = in_array($brand->id, (array)request('brand')); @endphp
                                        <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                            <span
                                                class="text-sm group-hover:text-[#f15a24] {{ $isSelectedBrand ? 'text-[#f15a24]' : '' }}">
                                                {{ $brand->name }}
                                            </span>
                                            <div class="relative flex items-center">
                                                <input type="checkbox" name="brand[]" value="{{ $brand->id }}"
                                                    onchange="this.form.submit()" {{ $isSelectedBrand ? 'checked' : '' }}
                                                    class="absolute opacity-0 w-4 h-4 cursor-pointer z-10">
                                                <div
                                                    class="w-4 h-4 border {{ $isSelectedBrand ? 'border-[#f15a24] bg-[#f15a24]/10' : 'border-gray-300' }} rounded-full group-hover:border-[#f15a24] shrink-0 flex items-center justify-center">
                                                    @if ($isSelectedBrand)
                                                        <div class="w-1.5 h-1.5 bg-[#f15a24] rounded-full"></div>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Dynamic Attributes (Color, Size, Style) -->
                            @foreach ($attributeGroups as $group)
                                <div class="px-4 pt-3 pb-3 border-b border-gray-50">
                                    <h3 class="text-sm font-bold text-gray-800 mb-3">Filter By {{ $group->name }}</h3>
                                    <div class="flex flex-col gap-2 text-gray-800 font-medium">
                                        @foreach ($group->values as $value)
                                            <label class="flex items-center gap-2 cursor-pointer group">
                                                <input type="checkbox" name="attributes[{{ $group->id }}][]"
                                                    value="{{ $value->id }}" onchange="this.form.submit()"
                                                    {{ isset(request('attributes')[$group->id]) && in_array($value->id, request('attributes')[$group->id]) ? 'checked' : '' }}
                                                    class="w-3.5 h-3.5 accent-[#f15a24]">
                                                <span
                                                    class="text-sm group-hover:text-[#f15a24]">{{ $value->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            <!-- Clear Button -->
                            <div class="p-4">
                                <a href="{{ url()->current() }}"
                                    class="text-xs text-red-500 font-bold hover:underline uppercase">Clear All
                                    Filters</a>
                            </div>

                        </div><!-- /#all-filters-panel -->
                    </div>
                </form>
            </aside>

            <!-- ══════════════════════════════════════
                                                            MAIN CONTENT
                                                        ══════════════════════════════════════ -->
            <main class="flex-1 bg-white rounded-lg shadow-xs border border-gray-200 overflow-hidden">

                <!-- Shop Header -->
                <div class="px-5 py-3.5 flex items-center justify-between">
                    <h2 class="text-base md:text-xl font-bold text-gray-900 mb-1">
                        {{ $category ? $category->name : 'All Products' }}
                    </h2>
                    <div class="relative">
                        <form action="" method="GET" id="sortForm">
                            <select name="sort" onchange="document.getElementById('sortForm').submit()" aria-label="Sort products"
                                class="appearance-none bg-white border border-gray-200 text-gray-600 text-md rounded-md pr-8 pl-3 py-1.5 outline-none focus:ring-1 focus:ring-[#f15a24] cursor-pointer">
                                <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Default
                                    Sorting</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price:
                                    Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price:
                                    High to Low</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First
                                </option>
                            </select>
                        </form>
                        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-gray-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Product Grid  -->
                <div class="p-3 md:p-4 grid gap-3 md:gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">

                    @forelse($products as $product)
                        <x-template1.product-card :product="$product" />
                    @empty
                        <div class="col-span-full py-20 text-center">
                            <i class="fas fa-box-open text-5xl text-gray-200 mb-4"></i>
                            <p class="text-gray-500 font-medium">No products found in this category.</p>
                        </div>
                    @endforelse


                </div>
                <div class="mt-12 flex flex-col items-center gap-4 border-t border-gray-100 pt-8 pb-8">
                    <p class="text-sm text-gray-500 font-medium">
                        Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of
                        {{ $products->total() }} products
                    </p>

                    <div class="flex justify-center">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                </div>
            </main>

        </div>

    </section>
@endsection
@push('scripts')
    <script>
        function toggleMobileSidebar() {
            const box = document.getElementById('sidebar-filter-box');
            box.classList.toggle('hidden');
        }

        function toggleAllFilters() {
            const panel = document.getElementById('all-filters-panel');
            const arrow = document.getElementById('all-filters-arrow');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                arrow.style.transform = 'rotate(180deg)';
            } else {
                panel.classList.add('hidden');
                arrow.style.transform = 'rotate(0deg)';
            }
        }

        // page load e already filter active thakle arrow rotate
        window.addEventListener('DOMContentLoaded', () => {
            const panel = document.getElementById('all-filters-panel');
            const arrow = document.getElementById('all-filters-arrow');
            if (panel && !panel.classList.contains('hidden')) {
                arrow.style.transform = 'rotate(180deg)';
            }
        });
        const slider = document.getElementById('price-slider');
        const range = document.getElementById('slider-range');
        const handleMin = document.getElementById('handle-min');
        const handleMax = document.getElementById('handle-max');
        const inputMin = document.getElementById('min_price');
        const inputMax = document.getElementById('max_price');
        const displayMin = document.getElementById('display-min');
        const displayMax = document.getElementById('display-max');

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

            displayMin.innerText = minVal;
            displayMax.innerText = maxVal;
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

        window.onload = updateSlider;
    </script>
@endpush
