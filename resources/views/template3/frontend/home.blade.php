@extends('template3.layouts.front')
@section('meta')
     <x-meta-info.meta /> 
@endsection
@section('content')
    <!-- HERO SECTION (Full Width Slider) -->
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
        <div class="w-full h-[220px] sm:h-[300px] md:h-[400px] lg:h-[500px]">
            <!-- Slider Container -->
            <div class="relative h-full w-full rounded-2xl overflow-hidden shadow-sm bg-white group">

                <!-- Main Slider Wrapper -->
                <div id="main-slider" class="flex transition-transform duration-700 ease-in-out h-full w-full">
                    @forelse($mainSliders as $slider)
                        <div class="min-w-full h-full">
                            <a href="{{ $slider->url ?? '#' }}">
                                <img src="{{ $slider->image_url ?? asset('images/template1/frontend/1-3-scaled.jpg') }}"
                                 height="" width=""   class="w-full h-full object-cover" alt="{{ $slider->title }}">
                            </a>
                        </div>
                    @empty
                        <div class="min-w-full h-full">
                            <img src="{{ asset('./images/template1/frontend/default.webp') }}" alt="default image"
                              height="" width=""   class="w-full h-full object-cover">
                        </div>
                    @endforelse
                </div>

                <!-- Navigation Arrows (Visible on Hover) -->
                <button onclick="prevSlide()" aria-label="pre slider"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full border border-white/50 flex items-center justify-center text-white bg-black/20 hover:bg-black/40 transition-all opacity-0 group-hover:opacity-100 z-10 cursor-pointer focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button onclick="nextSlide()" aria-label="next slider"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full border border-white/50 flex items-center justify-center text-white bg-black/20 hover:bg-black/40 transition-all opacity-0 group-hover:opacity-100 z-10 cursor-pointer focus:outline-none">
                    <i class="fas fa-chevron-right"></i>
                </button>

            </div>
        </div>
    </section>
    <!-- 2. SHOP BY CATEGORY (Sub-Category Grid Layout) -->
    <section class="py-12 md:py-20 container mx-auto px-4 lg:px-0">
        <!-- Section Heading -->
        <div class="flex items-center justify-center gap-4 mb-12">
            <div class="h-[2px] bg-black flex-1 hidden md:block"></div>
            <h2 class="text-2xl md:text-[28px] font-bold text-black uppercase tracking-tighter text-center">
                SHOP BY CATEGORY
            </h2>
            <div class="h-[2px] bg-black flex-1 hidden md:block"></div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $allSubCategories = $categories->flatMap->subCategories->take(8);
                $bgColors = [
                    'bg-orange-50',
                    'bg-blue-50',
                    'bg-green-50',
                    'bg-slate-50',
                    'bg-red-50',
                    'bg-indigo-50',
                    'bg-yellow-50',
                    'bg-teal-50',
                ];
            @endphp

            @foreach ($allSubCategories as $index => $subCat)
                <div
                    class="{{ $bgColors[$index % count($bgColors)] }} rounded-sm border border-gray-300 flex flex-col relative group overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <p class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-1">Trending Now</p>
                        <h3 class="text-xl md:text-2xl font-black text-gray-900 uppercase leading-none mb-4">
                            {{ $subCat->name }}
                        </h3>
                    </div>
                    <div class="px-6 pb-16 h-48 md:h-56 flex items-center justify-center">
                        <img src="{{ $subCat->image_url ?? asset('images/no-image.png') }}" loading="lazy" width="250" height="250" class="max-h-full max-w-full object-contain transform group-hover:scale-110 transition-transform duration-500"
                            alt="{{ $subCat->name }}">
                    </div>
                    <a href="{{ route('category.products', $subCat->slug) }}"
                        class="absolute bottom-0 left-0 w-full primary-bg text-primary py-3 text-center font-bold uppercase text-sm tracking-wider hover:opacity-90">
                        SHOP NOW
                    </a>
                </div>
            @endforeach
        </div>

        {{-- <div class="flex justify-end mt-12">
        <a href="{{ route('shop.index') }}"
           class="inline-flex items-end gap-3 primary-bg text-primary px-10 py-4 rounded-md font-black uppercase text-sm md:text-base tracking-widest shadow-xl hover:opacity-95 transition-all">
            VIEW ALL CATEGORIES
            <i class="fas fa-arrow-circle-right text-lg"></i>
        </a>
    </div> --}}
    </section>
    <!-- CATEGORY WISE PRODUCT FILTER SECTION -->
    @foreach ($categories->take(3) as $megaCat)
        @php
            $firstSub = $megaCat->subCategories->first();
        @endphp

        @if ($firstSub)
            <section class="py-10 md:py-16 container mx-auto px-4 lg:px-0" id="mega-section-{{ $megaCat->id }}">

                <!-- Header: Category Name & Sub-Category Tabs -->
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-200 pb-2 mb-8 gap-4">
                    <!-- Mega Category Name -->
                    <h2 class="text-3xl md:text-[28px] font-black uppercase text-gray-900">
                        {{ $megaCat->name }}
                    </h2>

                    <!-- Sub Category Tabs (No "All" Button) -->
                    <div class="flex flex-wrap gap-2 sub-tabs-wrapper-{{ $megaCat->id }}">
                        @foreach ($megaCat->subCategories->take(6) as $index => $sub)
                            <button onclick="categoryFilter({{ $megaCat->id }}, {{ $sub->id }}, this)"
                                class="px-4 py-1.5 rounded-sm text-xs md:text-sm font-bold uppercase cursor-pointer transition-all
                            {{ $index == 0 ? 'primary-bg text-white active-tab' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                {{ $sub->name }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Product Grid Container -->
                <div id="grid-{{ $megaCat->id }}"
                    class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 md:gap-5 transition-all duration-300">
                    @php
                        $initialProducts = \App\Models\Product::where('status', 1)
                            ->whereJsonContains('sub_category_ids', (int) $firstSub->id)
                            ->take(6)
                            ->get();
                    @endphp

                    @forelse ($initialProducts as $product)
                        <x-template1.product-card :product="$product" />
                    @empty
                        <div class="col-span-full py-10 text-center text-gray-400">এই সাব-ক্যাটাগরিতে কোনো পণ্য নেই।</div>
                    @endforelse
                </div>

                <!-- Hidden Loader -->
                <div id="loader-{{ $megaCat->id }}" class="hidden justify-center py-10">
                    <i class="fas fa-circle-notch fa-spin text-3xl text-blue-600"></i>
                </div>
            </section>
        @endif
    @endforeach

    <!-- Latest Products SECTION -->
    <section class="py-10 md:py-16 container mx-auto px-4 lg:px-0">
        <!-- Header: Title left, View All right, and a Border bottom -->
        <div class="flex items-end justify-between border-b border-gray-200 pb-2 mb-8">
            <!-- Section Title -->
            <h2 class="text-2xl md:text-[28px] font-black uppercase text-gray-900 leading-none">
                Latest Products
            </h2>

            <!-- View All Link (Matches your image) -->
            <a href="{{ route('shop.index') }}"
                class="text-gray-900 hover:text-blue-600 transition-colors flex items-center gap-2 text-xs md:text-sm uppercase tracking-wider">
                View All <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- Product Grid (Responsive 2 to 6 columns) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 md:gap-5">
            @foreach ($allProducts as $product)
                <x-template1.product-card :product="$product" />
            @endforeach
        </div>

        <!-- View More Button (Bottom - Optional, can keep or remove) -->
        <div class="flex justify-center mt-12">
            <a href="{{ route('shop.index') }}"
                class="primary-bg text-primary px-10 py-3 rounded-md font-black uppercase text-sm shadow-lg hover:opacity-90 transition-all">
                Browse All Products
            </a>
        </div>
    </section>
    <!-- DUAL BANNER SECTION -->
    @if ($middleSliders->count() > 0)
        <section class="py-10 md:py-16 container mx-auto px-4 lg:px-0">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">

                @foreach ($middleSliders->take(2) as $slider)
                    <div class="relative overflow-hidden rounded-md shadow-sm group">
                        <a href="{{ $slider->url ?? '#' }}" class="block w-full h-full">
                            <div
                                class="aspect-[641/320] rounded-md w-full border border-[var(--primary-color)] overflow-hidden bg-gray-100">
                                <img src="{{ $slider->image_url ?? asset('./images/template1/frontend/default.webp') }}"
                                    alt="{{ $slider->title }}" loading="lazy" width="641" height="320"
                                    class="w-full h-full rounded-md object-cover transition-transform duration-700 ease-in-out group-hover:scale-105">
                            </div>
                        </a>
                    </div>
                @endforeach

                {{-- যদি স্লাইডার না থাকে তবে প্লেসহোল্ডার দেখাবে --}}
                @if ($middleSliders->count() == 0)
                    <div class="aspect-[641/320] bg-gray-100 rounded-md animate-pulse"></div>
                    <div class="aspect-[641/320] bg-gray-100 rounded-md animate-pulse"></div>
                @endif

            </div>
        </section>
    @endif
    <!-- Customer Review Section -->
    @if (isset($allReviews) && $allReviews->isNotEmpty())
        <section class="py-16 bg-[#f8fafc]">
            <div class="container mx-auto px-4">
                <!-- Section Header -->
                <div class="text-center mb-12">
                    <!-- Star with Lines -->
                    <div class="flex items-center justify-center gap-4 mb-4">
                        <div class="h-[2px] w-12 md:w-20 bg-gray-800"></div>
                        <i class="fa-solid fa-star text-[#1a73e8] text-2xl"></i>
                        <div class="h-[2px] w-12 md:w-20 bg-gray-800"></div>
                    </div>

                    <!-- Heading -->
                    <h2 class="text-4xl md:text-[46px] font-extrabold text-black mb-3">
                        Customer Reviews
                    </h2>

                    <!-- Sub-heading -->
                    <p class="text-gray-600 text-lg md:text-xl">
                        See what our satisfied customers have to say about us
                    </p>
                </div>

                <!-- Review Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="review-container">
                    @foreach ($allReviews as $index => $review)
                        <div
                            class="review-card bg-white p-8 rounded-lg border border-[var(--primary-color)] shadow-sm flex flex-col justify-between transition-all hover:shadow-md {{ $index >= 6 ? 'hidden' : '' }}">

                            <div>
                                <!-- Quote Icon -->
                                <div class="mb-4">
                                    <svg class="w-10 h-10 text-gray-200" fill="currentColor" viewBox="0 0 32 32"
                                        aria-hidden="true">
                                        <path
                                            d="M9.352 4C4.456 7.456 1 13.12 1 19.36c0 5.088 3.072 8.064 6.624 8.064 3.36 0 5.856-2.688 5.856-5.856 0-3.168-2.208-5.472-5.088-5.472-.576 0-1.344.096-1.536.192.48-3.264 3.552-7.104 6.624-9.024L9.352 4zm16.512 0c-4.8 3.456-8.256 9.12-8.256 15.36 0 5.088 3.072 8.064 6.624 8.064 3.264 0 5.856-2.688 5.856-5.856 0-3.168-2.304-5.472-5.184-5.472-.576 0-1.248.096-1.44.192.48-3.264 3.456-7.104 6.528-9.024L25.864 4z" />
                                    </svg>
                                </div>

                                <!-- Review Comment -->
                                <p class="text-gray-700 text-base leading-relaxed mb-8">
                                    {{ $review->comment }}
                                </p>
                            </div>

                            <!-- Customer Info -->
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0">
                                    @if ($review->customer && $review->customer->image)
                                        <img class="h-12 w-12 rounded-full object-cover border-2 border-white shadow-sm"
                                            src="{{ asset('storage/' . $review->customer->image) ?? asset('images/template1/frontend/default.webp') }}"
                                            alt="{{ $review->customer->name }}">
                                    @else
                                        <img class="h-12 w-12 rounded-full object-cover border-2 border-white shadow-sm" height="" width=""
                                            src="https://ui-avatars.com/api/?name={{ urlencode($review->customer->name ?? 'User') }}&background=random"
                                            alt="User">
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-[#1a73e8] font-bold text-lg leading-tight">
                                        {{ $review->customer->name ?? 'Anonymous' }}
                                    </h3>
                                    <!-- Star Rating -->
                                    <div class="flex items-center mt-1">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="fa-solid fa-star text-sm {{ $i <= $review->rating ? 'text-orange-400' : 'text-gray-200' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Load More Button -->
                @if ($allReviews->count() > 6)
                    <div class="mt-12 text-center">
                        <button id="load-more-reviews"
                            class="inline-block bg-white text-gray-700 border border-gray-200 px-8 py-3 rounded-full font-semibold hover:bg-gray-50 transition shadow-sm">
                            আরও দেখুন
                        </button>
                    </div>
                @endif
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
    <script>
        function categoryFilter(megaId, subId, btn) {
            const grid = document.getElementById(`grid-${megaId}`);
            const loader = document.getElementById(`loader-${megaId}`);

            const wrapper = document.querySelector(`.sub-tabs-wrapper-${megaId}`);
            wrapper.querySelectorAll('button').forEach(b => {
                b.classList.remove('primary-bg', 'text-white');
                b.classList.add('bg-gray-100', 'text-gray-700');
            });
            btn.classList.remove('bg-gray-100', 'text-gray-700');
            btn.classList.add('primary-bg', 'text-white');

            grid.style.opacity = '0.3';
            loader.classList.remove('hidden');
            loader.classList.add('flex');

            fetch(`/filter-subcategory-products?mega_id=${megaId}&sub_id=${subId}`)
                .then(res => res.text())
                .then(html => {
                    grid.innerHTML = html;
                    grid.style.opacity = '1';
                    loader.classList.add('hidden');
                });
        }
    </script>
@endpush
