@extends('template5.layouts.front')
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
        <div class="lg:hidden mb-3 flex items-center justify-between">
            <button onclick="toggleMobileSidebar()"
                class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-bold text-gray-700 shadow-xs">
                <i class="fas fa-filter text-[var(--primary-color)]"></i> Filters
            </button>
            <span class="text-xs text-gray-400">{{ $products->total() }} products</span>
        </div>


        <!-- Main Card Container -->
        <div class="archiveTopInfo">

            <!-- Breadcrumb -->
            <nav class="hidden md:flex items-center gap-2 mb-2 text-sm font-medium text-gray-500">
                <a href="/" class="hover:text-gray-500 transition-colors">Home</a>

                <!-- Chevron Icon -->
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" class="w-4 h-4 text-gray-500"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                </svg>

                <a href="{{ route('shop.index') }}" class="text-gray-500 hover:text-[var(--primary-color)] transition-colors">
                    {{ request()->routeIs('brand.products') ? 'Brand' : 'Category' }}
                </a>

                <!-- Chevron Icon -->
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" class="w-4 h-4 text-gray-500"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                </svg>

                <span class="text-brand">{{ $category->name ?? 'Shop' }}</span>
            </nav>
            <div class="meta_info">
                @if (isset($category->meta_title) && $category->meta_title)
                    <h1 class="text-[24px] py-1">{{$category->meta_title}}</h1>
                @endif
                <!-- Description Section -->
                @if (isset($category->meta_description) && $category->meta_description)
                        <p class="text-[15px] text-gray-600 l">
                            {!! $category->meta_description !!}
                        </p>
                @endif
            </div>
        </div>
</div>
    </section>
    <!-- SHOP PAGE SECTION -->
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0 pt-0">

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- ══════════════════════════ SIDEBAR ═════════════════════════════ -->
            <aside class="w-full lg:w-[220px] shrink-0" id="shop-sidebar">
                <form action="{{ url()->current() }}" method="GET" id="sidebar-filter-form">
                    <input type="hidden" name="sort" value="{{ request('sort', 'default') }}">

                    <div id="sidebar-filter-box"
                        class="hidden lg:block bg-white rounded-lg  overflow-hidden lg:sticky lg:top-[70px] lg:max-h-[calc(100vh-90px)] lg:overflow-y-auto no-scrollbar">
                        <!-- ① Filter By Price -->
                        <div class="p-4 ">
                            <h3 class="text-sm font-bold text-gray-800 mb-4">Filter By Price</h3>

                            <!-- Slider Container -->
                            <div id="price-slider" class="relative h-1.5 bg-gray-100 rounded-full mb-6 mx-2 cursor-pointer">
                                <!-- Orange Progress Bar -->
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
                                <input type="number" id="min_price" name="min_price" aria-label="Minimum Price"  value="{{ request('min_price', 0) }}"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs outline-none focus:border-[var(--primary-color)]">
                                <input type="number" id="max_price" name="max_price" aria-label="Maximum Price"
                                    value="{{ request('max_price', (int) $maxPriceLimit) }}"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-xs outline-none focus:border-[var(--primary-color)]">
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="submit"
                                    class="primary-bg text-primary px-4 py-1.5 rounded text-xs font-bold hover:bg-orange-600 transition-colors uppercase">
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
                            class="{{ request()->has('mega_category') || request()->has('attributes') ? '' : 'hidden' }}">

                            <!-- Brand List -->
                           <div class="px-4 pt-2 pb-3 border-b border-gray-50">
    <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Categories</h3>
    <div class="flex flex-col gap-0 text-gray-800 font-medium">
        @foreach ($categories as $category)
            @php
                $isSelectedMega = in_array($category->id, (array) request('mega_category'));
            @endphp
            <div class="border-b border-gray-50 last:border-0">
                <div class="flex items-center justify-between py-1.5">
                    <label class="flex items-center gap-2 cursor-pointer group flex-1">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="mega_category[]" value="{{ $category->id }}"
                                onchange="this.form.submit()" {{ $isSelectedMega ? 'checked' : '' }}
                                class="absolute opacity-0 w-4 h-4 cursor-pointer z-10">
                            <div
                                class="w-4 h-4 border {{ $isSelectedMega ? 'border-[var(--primary-color)] bg-[var(--primary-color)]/10' : 'border-gray-300' }} rounded-full group-hover:border-[var(--primary-color)] shrink-0 flex items-center justify-center">
                                @if ($isSelectedMega)
                                    <div class="w-1.5 h-1.5 bg-[var(--primary-color)] rounded-full"></div>
                                @endif
                            </div>
                        </div>
                        <span
                            class="text-sm group-hover:text-[var(--primary-color)] {{ $isSelectedMega ? 'text-[var(--primary-color)]' : '' }}">
                            {{ $category->name }}
                        </span>
                    </label>

                    @if ($category->subCategories->count() > 0)
                    <button type="button" class="filter-accordion-btn p-1 text-gray-400 hover:text-gray-700"
                        data-target="filter-sub-{{ $category->id }}">
                        <i class="fa-solid fa-chevron-down text-[10px] transition-transform"></i>
                    </button>
                    @endif
                </div>

                @if ($category->subCategories->count() > 0)
                <div id="filter-sub-{{ $category->id }}" class="hidden pl-6 flex flex-col gap-0 pb-1">
                    @foreach ($category->subCategories as $sub)
                        @php $isSelectedSub = in_array($sub->id, (array) request('sub_category')); @endphp
                        <label class="flex items-center gap-2 py-1 cursor-pointer group">
                            <div class="relative flex items-center">
                                <input type="checkbox" name="sub_category[]" value="{{ $sub->id }}"
                                    onchange="this.form.submit()" {{ $isSelectedSub ? 'checked' : '' }}
                                    class="absolute opacity-0 w-3.5 h-3.5 cursor-pointer z-10">
                                <div
                                    class="w-3.5 h-3.5 border {{ $isSelectedSub ? 'border-[var(--primary-color)] bg-[var(--primary-color)]/10' : 'border-gray-300' }} rounded-full group-hover:border-[var(--primary-color)] shrink-0 flex items-center justify-center">
                                    @if ($isSelectedSub)
                                        <div class="w-1 h-1 bg-[var(--primary-color)] rounded-full"></div>
                                    @endif
                                </div>
                            </div>
                            <span
                                class="text-xs text-gray-600 group-hover:text-[var(--primary-color)] {{ $isSelectedSub ? 'text-[var(--primary-color)]' : '' }}">
                                {{ $sub->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @endif
            </div>
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
                                                    class="w-3.5 h-3.5 accent-[var(--primary-color)]">
                                                <span
                                                    class="text-sm group-hover:text-[var(--primary-color)]">{{ $value->name }}</span>
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
            <main class="flex-1 ">
                <div class="bg-white rounded-lg shadow-xs overflow-hidden">
                    <!-- Shop Header -->
                    <div class="px-5 py-3.5 flex items-center justify-between">
                        <h2 class="text-base md:text-xl font-bold text-gray-900 mb-1">
                All items ({{ $products->total() }})
                        </h2>
                        <div class="relative">
                            <form action="" method="GET" id="sortForm">
                                <select name="sort" onchange="document.getElementById('sortForm').submit()"
                                    aria-label="Sort products"
                                    class="appearance-none bg-white border border-gray-200 text-gray-600 text-md rounded-md pr-8 pl-3 py-1.5 outline-none focus:ring-1 focus:ring-[var(--primary-color)] cursor-pointer">
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
                    <div class="mt-12 flex flex-col items-center">
                        {{-- pagination --}}
                        {{ $products->appends(request()->query())->links('components.template1.custom-pagiantion') }}

                        {{-- <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest mt-2">
                            Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }} Products
                        </p> --}}
                    </div>
                </div>


                 @if (isset($category->description) && $category->description)
                <div class="bg-white rounded-lg shadow-xs  mt-4 px-4 py-4 text-[16px] prose w-full min-w-full">
                    {!! $category->description !!}
                </div>
                @endif
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
    
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.filter-accordion-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.getElementById(this.getAttribute('data-target'));
            const icon = this.querySelector('i');
            if (target) {
                target.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            }
        });
    });
});
</script>
@endpush
