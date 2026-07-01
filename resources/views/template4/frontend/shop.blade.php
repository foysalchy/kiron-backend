@extends('template1.layouts.front')

@section('content')
    <!-- Mobile Filter Overlay (Right Drawer) -->
    <div id="right-filter-overlay" class="fixed inset-0 bg-black/60 z-[60] hidden lg:hidden transition-opacity"
        aria-hidden="true"></div>

    <!-- ════════════════════════════════════════
             RIGHT SIDEBAR FILTER DRAWER (Mobile Only)
            ════════════════════════════════════════ -->
    <aside id="right-filter-drawer"
        class="fixed inset-y-0 right-0 z-[70] w-[280px] sm:w-[320px] h-full bg-white shadow-2xl transform translate-x-full transition-transform duration-300 overflow-y-auto lg:hidden px-4 py-5 flex flex-col">
        <!-- Drawer Header -->
        <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-5">
            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide">
                Filter Products
            </h2>
            <button id="close-right-filter-btn"
                class="text-3xl leading-none text-gray-400 hover:text-[#632085] focus:outline-none transition">
                &times;
            </button>
        </div>

        <!-- Mobile Filter Options (Desktop filters moved here for mobile) -->
        <div class="space-y-5 flex-1">
            <!-- Age Group -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Age Group</label>
                <select
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#632085] focus:border-[#632085]">
                    <option value="">All Ages</option>
                    <option value="0m">0 Months+</option>
                    <option value="0-6m">0-6 Month</option>
                    <option value="7-12m">7-12 Month</option>
                    <option value="1-1.5y">1-1.5 Years</option>
                    <option value="1.6-2y">1.6-2 Years</option>
                    <option value="2-2.5y">2-2.5 Years</option>
                    <option value="2.5-3y">2.5-3 Years</option>
                </select>
            </div>

            <!-- Size -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Size</label>
                <select
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#632085] focus:border-[#632085]">
                    <option value="">All Sizes</option>
                    <option value="s">Small</option>
                    <option value="m">Medium</option>
                    <option value="l">Large</option>
                </select>
            </div>

            <!-- Color -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Color</label>
                <select
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#632085] focus:border-[#632085]">
                    <option value="">All Colors</option>
                    <option value="red">Red</option>
                    <option value="pink">Pink</option>
                    <option value="blue">Blue</option>
                </select>
            </div>

            <!-- Brand -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Brand</label>
                <select
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#632085] focus:border-[#632085]">
                    <option value="">All Brands</option>
                    <option value="ponds">Ponds</option>
                    <option value="huggies">Huggies</option>
                </select>
            </div>

            <!-- Price Range -->
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1.5">Price Range</label>
                <select
                    class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-700 bg-white focus:outline-none focus:ring-1 focus:ring-[#632085] focus:border-[#632085]">
                    <option value="">Any Price</option>
                    <option value="low-high">Low to High</option>
                    <option value="high-low">High to Low</option>
                </select>
            </div>
        </div>

        <!-- Apply Button -->
        <div class="mt-6 border-t border-gray-100 pt-4">
            <button id="apply-filters-btn"
                class="w-full bg-[#632085] hover:bg-[#52166d] text-white font-bold py-3 rounded-lg text-base shadow-sm transition">
                Apply Filters
            </button>
        </div>
    </aside>

    <div class="container mx-auto px-3 sm:px-4 py-4 md:py-8" style="font-family: &quot;Manrope&quot;, sans-serif">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
            <!-- ════════════════════════════════════════
                 LEFT SIDEBAR: CATEGORIES (Desktop Only)
                ════════════════════════════════════════ -->
            <aside
                class="hidden lg:block lg:col-span-3 bg-white px-4 py-4 pr-4 border border-gray-100 rounded-lg shadow-sm">
                <h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4">
                    All Products
                </h2>

                <div class="space-y-1.5">
                    <!-- Footwear Category -->
                    <div class="category-item">
                        <button
                            class="category-header w-full flex justify-between items-center py-2 text-lg font-semibold text-gray-700 hover:text-[#632085] transition focus:outline-none"
                            data-target="footwear-sub">
                            <span>Footwear</span>
                            <span class="icon text-lg font-normal">+</span>
                        </button>
                        <div id="footwear-sub" class="category-content hidden pl-3 space-y-1 mt-1 pb-2">
                            <a href="#" class="block text-base text-gray-500 hover:text-[#632085] py-1">Baby Shoes</a>
                            <a href="#" class="block text-base text-gray-500 hover:text-[#632085] py-1">Sandals</a>
                        </div>
                    </div>

                    <!-- Clothes Category (Default Expanded) -->
                    <div class="category-item">
                        <button
                            class="category-header w-full flex justify-between items-center py-2 text-lg font-bold text-[#632085] transition focus:outline-none"
                            data-target="clothes-sub">
                            <span>Clothes</span>
                            <span class="icon text-lg font-normal">-</span>
                        </button>
                        <div id="clothes-sub" class="category-content pl-3 space-y-2 mt-1 pb-2">
                            <div class="sub-category-item">
                                <button
                                    class="subcategory-header w-full flex justify-between items-center py-1.5 text-base font-bold text-[#632085] transition focus:outline-none"
                                    data-target="newborn-sub">
                                    <span>Newborn Essentials</span>
                                    <span class="sub-icon text-xl font-normal">-</span>
                                </button>
                                <div id="newborn-sub" class="subcategory-content pl-3 space-y-2 mt-2">
                                    <a href="#"
                                        class="block text-sm text-gray-600 hover:text-[#632085] transition">Onesies Bodysuit
                                        &amp; Vest</a>
                                    <a href="#"
                                        class="block text-sm text-gray-600 hover:text-[#632085] transition">Rompers &amp;
                                        Sleepsuits</a>
                                    <a href="#"
                                        class="block text-sm text-gray-600 hover:text-[#632085] transition">Newborn Set
                                        &amp; Suit</a>
                                    <a href="#"
                                        class="block text-sm text-gray-600 hover:text-[#632085] transition">Cap Mitten &amp;
                                        Booties</a>
                                </div>
                            </div>
                            <div class="sub-category-item">
                                <button
                                    class="subcategory-header w-full flex justify-between items-center py-1.5 text-base font-semibold text-gray-700 hover:text-[#632085] transition focus:outline-none"
                                    data-target="boys-sub">
                                    <span>Boys Fashion</span>
                                    <span class="sub-icon text-xl font-normal">+</span>
                                </button>
                                <div id="boys-sub" class="subcategory-content hidden pl-3 space-y-2 mt-2">
                                    <a href="#" class="block text-sm text-gray-600 hover:text-[#632085]">T-Shirts</a>
                                    <a href="#" class="block text-sm text-gray-600 hover:text-[#632085]">Jeans</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nursing -->
                    <div class="category-item">
                        <button
                            class="category-header w-full flex justify-between items-center py-2 text-lg font-semibold text-gray-700 hover:text-[#632085] transition focus:outline-none"
                            data-target="nursing-sub">
                            <span>Nursing</span>
                            <span class="icon text-lg font-normal">+</span>
                        </button>
                        <div id="nursing-sub" class="category-content hidden pl-3 space-y-2 mt-1 pb-2">
                            <a href="#" class="block text-base text-gray-600 hover:text-[#632085]">Nursing Pillows</a>
                        </div>
                    </div>

                    <!-- Diapers -->
                    <div class="category-item">
                        <button
                            class="category-header w-full flex justify-between items-center py-2 text-lg font-semibold text-gray-700 hover:text-[#632085] transition focus:outline-none"
                            data-target="diapers-sub">
                            <span>Diapers</span>
                            <span class="icon text-lg font-normal">+</span>
                        </button>
                        <div id="diapers-sub" class="category-content hidden pl-3 space-y-2 mt-1 pb-2">
                            <a href="#" class="block text-base text-gray-600 hover:text-[#632085]">Disposable
                                Diapers</a>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- ════════════════════════════════════════
                 RIGHT SIDE: TOP FILTER BAR & CARD GRID CONTAINER (col-span-9)
                ════════════════════════════════════════ -->
            <div class="lg:col-span-9 w-full overflow-hidden">
                <!-- Top Filter Bar & Sorting (Side-by-Side on Mobile) -->
                <div class="bg-[#F5F5F5] rounded-lg px-2 sm:px-4 py-2 sm:py-4 mb-4 md:mb-6">
                    <div class="flex flex-row items-center justify-between gap-2 sm:gap-4">
                        <!-- Mobile Filter Button (Visible only on mobile/tablet) -->
                        <!-- flex-1 ensures it shares 50% width on mobile -->
                        <button id="open-right-filter-btn"
                            class="flex-1 lg:hidden border border-gray-300 rounded-lg px-2 sm:px-4 py-2 sm:py-2.5 bg-[#632085] text-white text-[12px] sm:text-sm font-bold uppercase tracking-wide flex items-center justify-center gap-1.5 sm:gap-2 shadow-sm focus:outline-none active:scale-95 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                                </path>
                            </svg>
                            <span>Filter By</span>
                        </button>

                        <!-- Left Side: Desktop Inline Filters (Visible only on lg) -->
                        <div class="hidden lg:flex flex-wrap items-center gap-3">
                            <!-- 1. AGE GROUP Filter -->
                            <div class="relative filter-dropdown-container">
                                <button
                                    class="filter-dropdown-btn border border-gray-300 rounded px-4 py-2 bg-white text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 hover:bg-gray-50 transition">
                                    <span>Age</span>
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div
                                    class="filter-menu hidden absolute left-0 mt-1.5 w-36 bg-white border border-gray-200 rounded shadow-lg z-30 overflow-hidden divide-y divide-gray-50">
                                    <a href="#"
                                        class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition">0
                                        Months+</a>
                                    <a href="#"
                                        class="block px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition">0-6
                                        Month</a>
                                </div>
                            </div>
                            <!-- 2. Size Filter -->
                            <div class="relative filter-dropdown-container">
                                <button
                                    class="filter-dropdown-btn border border-gray-300 rounded px-4 py-2 bg-white text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 hover:bg-gray-50 transition">
                                    <span>Size</span>
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                            <!-- 3. COLOR Filter -->
                            <div class="relative filter-dropdown-container">
                                <button
                                    class="filter-dropdown-btn border border-gray-300 rounded px-4 py-2 bg-white text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 hover:bg-gray-50 transition">
                                    <span>Color</span>
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                            <!-- 4. BRAND Filter -->
                            <div class="relative filter-dropdown-container">
                                <button
                                    class="filter-dropdown-btn border border-gray-300 rounded px-4 py-2 bg-white text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-2 hover:bg-gray-50 transition">
                                    <span>Brand</span>
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Right Side: Native Sort By Dropdown (Always Visible, shares 50% on mobile) -->
                        <div class="flex-1 lg:flex-none flex items-center justify-end">
                            <label for="input-sort"
                                class="hidden xl:block text-sm font-bold text-gray-700 uppercase mr-3 shrink-0">Sort By
                                :</label>
                            <div class="relative w-full lg:w-56">
                                <select id="input-sort" onchange="location = this.value"
                                    class="w-full appearance-none border border-gray-300 rounded-lg pl-2 sm:pl-3 pr-7 py-2 sm:py-2.5 bg-white text-[12px] sm:text-sm font-semibold text-gray-700 focus:outline-none focus:ring-1 focus:ring-[#632085] focus:border-[#632085] transition shadow-sm cursor-pointer hover:bg-gray-50 truncate">
                                    <option value="" selected="selected">
                                        Default Sorting
                                    </option>
                                    <option value="a-z">Name (A - Z)</option>
                                    <option value="z-a">Name (Z - A)</option>
                                    <option value="low-high">Price (Low &gt; High)</option>
                                    <option value="high-low">Price (High &gt; Low)</option>
                                    <option value="highest">Rating (Highest)</option>
                                    <option value="lowest">Rating (Lowest)</option>
                                </select>
                                <div
                                    class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 sm:px-3 text-gray-500">
                                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
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
                <div class="bg-white py-2 md:py-4 px-0">
                    <div
                        class="grid grid-cols-2 md:grid-cols-3 gap-x-2 sm:gap-x-4 lg:gap-x-6 gap-y-6 sm:gap-y-8 lg:gap-y-10">
                        <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                            <div class="relative">
                                <div
                                    class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                                    <div class="w-full h-full p-8 flex items-center justify-center">
                                        <img src="assets/images/girl.png" alt="Product Image"
                                            class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                                    </div>
                                </div>

                                <!-- উইশলিস্ট বাটন -->
                                <div class="absolute -top-1 -right-1">
                                    <div class="bg-white p-1 rounded-full">
                                        <button
                                            class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                            aria-label="Add to Wishlist">
                                            <i class="fa-regular fa-heart text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1 p-4">
                                <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                                    Luxury Essentials for Growing Families
                                </h3>

                                <!-- Price Section inspired by the image -->
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                                    <!-- Prices -->
                                    <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">৳1,040</span>
                                    <span class="text-[#999999] text-sm md:text-base line-through">৳1,340</span>

                                    <div class="basis-full h-0 sm:hidden"></div>

                                    <!-- Discount Percentage Badge -->
                                    <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                        -22%
                                    </span>
                                </div>
                                <button
                                    class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                                    Add To Cart
                                </button>
                            </div>
                        </div>
                        <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                            <div class="relative">
                                <div
                                    class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                                    <div class="w-full h-full p-8 flex items-center justify-center">
                                        <img src="assets/images/girl.png" alt="Product Image"
                                            class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                                    </div>
                                </div>

                                <!-- উইশলিস্ট বাটন -->
                                <div class="absolute -top-1 -right-1">
                                    <div class="bg-white p-1 rounded-full">
                                        <button
                                            class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                            aria-label="Add to Wishlist">
                                            <i class="fa-regular fa-heart text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1 p-4">
                                <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                                    Luxury Essentials for Growing Families
                                </h3>

                                <!-- Price Section inspired by the image -->
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                                    <!-- Prices -->
                                    <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">৳1,040</span>
                                    <span class="text-[#999999] text-sm md:text-base line-through">৳1,340</span>

                                    <div class="basis-full h-0 sm:hidden"></div>

                                    <!-- Discount Percentage Badge -->
                                    <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                        -22%
                                    </span>
                                </div>
                                <button
                                    class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                                    Add To Cart
                                </button>
                            </div>
                        </div>
                        <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                            <div class="relative">
                                <div
                                    class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                                    <div class="w-full h-full p-8 flex items-center justify-center">
                                        <img src="assets/images/girl.png" alt="Product Image"
                                            class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                                    </div>
                                </div>

                                <!-- উইশলিস্ট বাটন -->
                                <div class="absolute -top-1 -right-1">
                                    <div class="bg-white p-1 rounded-full">
                                        <button
                                            class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                            aria-label="Add to Wishlist">
                                            <i class="fa-regular fa-heart text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1 p-4">
                                <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                                    Luxury Essentials for Growing Families
                                </h3>

                                <!-- Price Section inspired by the image -->
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                                    <!-- Prices -->
                                    <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">৳1,040</span>
                                    <span class="text-[#999999] text-sm md:text-base line-through">৳1,340</span>

                                    <div class="basis-full h-0 sm:hidden"></div>

                                    <!-- Discount Percentage Badge -->
                                    <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                        -22%
                                    </span>
                                </div>
                                <button
                                    class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                                    Add To Cart
                                </button>
                            </div>
                        </div>
                        <div class="max-w-[348px] group cursor-pointer bg-white border-1 border-[#ddd] rounded-2xl">
                            <div class="relative">
                                <div
                                    class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative aspect-[1/1.1] border-b-1 border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)]">
                                    <div class="w-full h-full p-8 flex items-center justify-center">
                                        <img src="assets/images/girl.png" alt="Product Image"
                                            class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-105" />
                                    </div>
                                </div>

                                <!-- উইশলিস্ট বাটন -->
                                <div class="absolute -top-1 -right-1">
                                    <div class="bg-white p-1 rounded-full">
                                        <button
                                            class="w-11 h-11 bg-[#66267b] text-white rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all hover:bg-[#521d63]"
                                            aria-label="Add to Wishlist">
                                            <i class="fa-regular fa-heart text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1 p-4">
                                <h3 class="text-[#0f172a] text-base md:text-xl font-medium leading-[1.3] tracking-tight">
                                    Luxury Essentials for Growing Families
                                </h3>

                                <!-- Price Section inspired by the image -->
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-1 font-manrope">
                                    <!-- Prices -->
                                    <span class="text-[#005c7a] text-xl md:text-2xl font-semibold">৳1,040</span>
                                    <span class="text-[#999999] text-sm md:text-base line-through">৳1,340</span>

                                    <div class="basis-full h-0 sm:hidden"></div>

                                    <!-- Discount Percentage Badge -->
                                    <span class="bg-[#facc15] text-[#0f172a] text-xs font-bold px-2 py-0.5 rounded-full">
                                        -22%
                                    </span>
                                </div>
                                <button
                                    class="bg-[#66267b] text-white text-center text-sm md:text-base rounded-4xl border-0 mt-2 py-3 hover:bg-[#851ea7] cursor-pointer px-4 w-full">
                                    Add To Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center sm:justify-end mt-8 md:mt-12 mb-4 md:mb-6"
            style="font-family: &quot;Manrope&quot;, sans-serif">
            <nav class="flex items-center gap-1.5 md:gap-2" aria-label="Pagination">
                <!-- Previous Button -->
                <button id="prev-page"
                    class="px-2.5 sm:px-3 md:px-4 h-8 md:h-10 flex items-center justify-center border border-gray-300 rounded text-xs sm:text-sm md:text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none transition">
                    Prev
                </button>
                <!-- Page Numbers -->
                <div id="page-numbers" class="flex items-center gap-1.5 md:gap-2">
                    <button
                        class="page-num-btn w-8 h-8 md:w-10 md:h-10 flex items-center justify-center bg-[#632085] hover:bg-[#52166d] rounded text-white font-bold text-xs sm:text-sm md:text-base focus:outline-none transition"
                        data-page="1">
                        1
                    </button>
                    <button
                        class="page-num-btn w-8 h-8 md:w-10 md:h-10 flex items-center justify-center border border-gray-300 rounded text-gray-700 hover:bg-gray-50 font-bold text-xs sm:text-sm md:text-base focus:outline-none transition"
                        data-page="2">
                        2
                    </button>
                    <button
                        class="page-num-btn w-8 h-8 md:w-10 md:h-10 flex items-center justify-center border border-gray-300 rounded text-gray-700 hover:bg-gray-50 font-bold text-xs sm:text-sm md:text-base focus:outline-none transition"
                        data-page="3">
                        3
                    </button>
                    <button
                        class="hidden sm:flex page-num-btn w-10 h-10 items-center justify-center border border-gray-300 rounded text-gray-700 hover:bg-gray-50 font-bold text-base focus:outline-none transition"
                        data-page="4">
                        4
                    </button>
                </div>
                <!-- Next Button -->
                <button id="next-page"
                    class="px-2.5 sm:px-3 md:px-4 h-8 md:h-10 flex items-center justify-center border border-gray-300 rounded text-xs sm:text-sm md:text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none transition">
                    Next
                </button>
            </nav>
        </div>
    </div>
@endsection
@push('scripts')
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
                        this.classList.add("text-[#632085]", "font-bold");
                    } else {
                        content.classList.add("hidden");
                        icon.textContent = "+";
                        this.classList.remove("text-[#632085]", "font-bold");
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
                        this.classList.add("text-[#632085]", "font-bold");
                    } else {
                        content.classList.add("hidden");
                        icon.textContent = "+";
                        this.classList.remove("text-[#632085]", "font-bold");
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
