@extends('template1.layouts.front')

@section('content')
    <section class="py-6 container mx-auto">

        <!-- Main Card Container -->
        <div class="archiveTopInfo">

            <!-- Breadcrumb (Desktop only - hideOnPhone) -->
            <nav class="hidden md:flex items-center gap-2 mb-6 text-md font-medium text-gray-500">
                <a href="/" class="hover:text-[#f15a24] transition-colors">Home</a>

                <!-- Chevron Icon -->
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" class="w-4 h-4 text-gray-500"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                </svg>

                <a href="/summer-essentials" class=" hover:text-[#f15a24] transition-colors">
                    Summer Essential
                </a>
            </nav>

            <!-- Page Title -->
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">
                Summer Essential
            </h2>

            <!-- Short Description / Subtitle -->
            <p class="text-md text-gray-500 font-medium">
                Summer Essential
            </p>

        </div>

    </section>
    <!-- SHOP PAGE SECTION -->
    <section class="py-6 container mx-auto">

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- ══════════════════════════════════════
                        SIDEBAR
                    ══════════════════════════════════════ -->
            <aside class="w-full lg:w-[220px] shrink-0">
                <div
                    class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden lg:sticky lg:top-[70px] lg:max-h-[calc(100vh-90px)] lg:overflow-y-auto no-scrollbar">

                    <!-- ① Filter By Price — ALWAYS VISIBLE -->
                    <div class="p-4">
                        <h3 class="text-sm font-bold text-gray-800 mb-4">Filter By Price</h3>

                        <div class="relative h-1.5 bg-gray-100 rounded-full mb-6 mx-1">
                            <div class="absolute h-full bg-[#f15a24] rounded-full" style="left:0%;right:0%;"></div>
                            <div
                                class="absolute w-4 h-4 bg-white rounded-full border-2 border-[#f15a24] -top-1.5 left-0 cursor-pointer shadow-sm">
                            </div>
                            <div
                                class="absolute w-4 h-4 bg-white rounded-full border-2 border-[#f15a24] -top-1.5 right-0 cursor-pointer shadow-sm">
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mb-4">
                            <input type="number" value="0"
                                class="w-full border border-gray-200 rounded px-2 py-1.5 text-[12px] outline-none focus:border-[#f15a24]
                                    [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-auto [&::-webkit-inner-spin-button]:appearance-auto [&::-webkit-inner-spin-button]:opacity-100">
                            <input type="number" value="3000"
                                class="w-full border border-gray-200 rounded px-2 py-1.5 text-[12px] outline-none focus:border-[#f15a24]
                                    [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-auto [&::-webkit-inner-spin-button]:appearance-auto [&::-webkit-inner-spin-button]:opacity-100">
                        </div>

                        <div class="flex items-center justify-between">
                            <button
                                class="bg-[#f15a24] text-white px-4 py-1.5 rounded text-[12px] font-bold hover:bg-orange-600 transition-colors uppercase">
                                Filter
                            </button>
                            <span class="text-[11px] text-gray-500 font-medium">Price: ৳0 — ৳3000</span>
                        </div>
                    </div>

                    <!-- ② Filter By Brand header — TOGGLE BUTTON HERE -->
                    <button onclick="toggleAllFilters()"
                        class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                        <span class="text-sm font-bold text-gray-800">Filter By Brand</span>
                        <svg id="all-filters-arrow" class="w-4 h-4 text-gray-400 transition-transform duration-300"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- ③ ALL FILTERS PANEL — hidden by default -->
                    <div id="all-filters-panel" class="hidden">

                        <!-- Brand List -->
                        <div class="px-4 pt-2 pb-3 ">
                            <div class="flex flex-col gap-0 text-gray-800 font-medium">
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Toyota</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Honda</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Sultu</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Gucci</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Lounuv</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">hoco</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">HGKJ</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">LIU HJG</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Bushineco</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Super V</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Viper</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Xunniu</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Nexpow</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">Godrej</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                                <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                    <span class="text-sm group-hover:text-[#f15a24]">TRD</span>
                                    <div
                                        class="w-4 h-4 border border-gray-300 rounded-full group-hover:border-[#f15a24] shrink-0">
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Filter By Colors -->
                        <div class="px-4 pt-3 pb-3">
                            <h3 class="text-sm font-bold text-gray-800 mb-3">Filter By Colors</h3>
                            <div class="flex flex-col gap-2 text-gray-800 font-medium">
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Beige</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Black</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Green</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Red</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Silver</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Navy
                                        Blue</span></label>
                            </div>
                        </div>

                        <!-- Filter By Size -->
                        <div class="px-4 pt-3 pb-3">
                            <h3 class="text-sm font-bold text-gray-800 mb-3">Filter By Size</h3>
                            <div class="flex flex-col gap-2 text-gray-800 font-medium">
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">36 Inch</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">4 Pcs
                                        Set</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">47 Inch</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">5 pcs
                                        Set</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">500ML</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Flat</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Front Door
                                        Glass</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Rear Door
                                        Glass</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Rear
                                        Windshield</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Round</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">30 Inch</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Steel Body
                                        Duster</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Extra
                                        Large</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Large
                                        SUV</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Small
                                        Sedan</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Medium
                                        SUV</span></label>
                            </div>
                        </div>

                        <!-- Filter By Style -->
                        <div class="px-4 pt-3 pb-4">
                            <h3 class="text-sm font-bold text-gray-800 mb-3">Filter By Style</h3>
                            <div class="flex flex-col gap-2 text-gray-800 font-medium">
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Round
                                        Design</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">USB
                                        Cable</span></label>
                                <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox"
                                        class="w-3.5 h-3.5 accent-[#f15a24]"><span class="text-sm">Rotatable
                                        Handle</span></label>
                            </div>
                        </div>

                    </div><!-- /#all-filters-panel -->

                </div>
            </aside>

            <!-- ══════════════════════════════════════
                        MAIN CONTENT
                    ══════════════════════════════════════ -->
            <main class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

                <!-- Shop Header -->
                <div class="px-5 py-3.5 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Summer Essential</h2>
                    <div class="relative">
                        <select
                            class="appearance-none bg-white border border-gray-200 text-gray-600 text-[13px] rounded-md pr-8 pl-3 py-1.5 outline-none focus:ring-1 focus:ring-[#f15a24] cursor-pointer">
                            <option selected>Default</option>
                            <option>Price: Low to High</option>
                            <option>Price: High to Low</option>
                            <option>Newest First</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-gray-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Product Grid — 5 columns -->
                <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-2">

                    <!-- Single Product Card -->
                    <div
                        class="group relative flex flex-col p-3 bg-white border border-gray-100 rounded-xl hover:shadow-xl transition-all duration-300">

                        <!-- 1. Image Section with Hover Overlay -->
                        <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3">
                            <!-- Product Image -->
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/f612deb4-7d5c-401e-8b82-d9d7458b5c49.jpg"
                                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                alt="Product">

                            <!-- Discount Badge (Optional) -->
                            <div
                                class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                -18%
                            </div>

                            <!-- HOVER ICONS (Eye & Cart) -->
                            <div
                                class="absolute inset-0 bg-black/10 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[1px]">
                                <!-- Eye Icon (Details Page) -->
                                <a href="{{url('product-details')}}"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <!-- Cart Icon (Add to Cart Action) -->
                                <button onclick="addToCart(980)"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="Add to Cart">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Product Info Section -->
                        <div class="flex flex-col flex-1">
                            <h3
                                class="text-[13px] font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[36px] group-hover:text-[#FF6A00] transition-colors">
                                Soft Car Emoji Pillow 2pcs - Fun Pack
                            </h3>

                            <!-- Rating Stars -->
                            <div class="flex items-center gap-1 mb-2">
                                <div class="flex text-yellow-400 text-[10px]">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-[10px] text-gray-400 font-bold">(25)</span>
                            </div>

                            <!-- Price and Add to Cart Button Row -->
                            <div class="mt-auto flex items-center justify-between gap-2">
                                <div class="flex flex-col">
                                    <span class="text-[15px] font-black text-[#FF6A00]">৳৯৮০</span>
                                    <span class="text-[11px] text-gray-400 line-through font-bold">৳১২০০</span>
                                </div>

                                <!-- Bottom Add to Cart Button -->
                                <button onclick="addToCart(980)"
                                    class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-[#FF6A00] transition-colors">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Card 2 -->
                    <div
                        class="group relative flex flex-col p-3 bg-white border border-gray-100 rounded-xl hover:shadow-xl transition-all duration-300">

                        <!-- 1. Image Section with Hover Overlay -->
                        <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3">
                            <!-- Product Image -->
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/519084f4-5bf8-4d77-a607-f75d64beeb1a.jpg"
                                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                alt="Product">

                            <!-- Discount Badge (Optional) -->
                            <div
                                class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                -18%
                            </div>

                            <!-- HOVER ICONS (Eye & Cart) -->
                            <div
                                class="absolute inset-0 bg-black/10 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[1px]">
                                <!-- Eye Icon (Details Page) -->
                                <a href="{{url('product-details')}}"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <!-- Cart Icon (Add to Cart Action) -->
                                <button onclick="addToCart(980)"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="Add to Cart">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Product Info Section -->
                        <div class="flex flex-col flex-1">
                            <h3
                                class="text-[13px] font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[36px] group-hover:text-[#FF6A00] transition-colors">
                                Soft Car Emoji Pillow 2pcs - Fun Pack
                            </h3>

                            <!-- Rating Stars -->
                            <div class="flex items-center gap-1 mb-2">
                                <div class="flex text-yellow-400 text-[10px]">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-[10px] text-gray-400 font-bold">(25)</span>
                            </div>

                            <!-- Price and Add to Cart Button Row -->
                            <div class="mt-auto flex items-center justify-between gap-2">
                                <div class="flex flex-col">
                                    <span class="text-[15px] font-black text-[#FF6A00]">৳৯৮০</span>
                                    <span class="text-[11px] text-gray-400 line-through font-bold">৳১২০০</span>
                                </div>

                                <!-- Bottom Add to Cart Button -->
                                <button onclick="addToCart(980)"
                                    class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-[#FF6A00] transition-colors">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div
                        class="group relative flex flex-col p-3 bg-white border border-gray-100 rounded-xl hover:shadow-xl transition-all duration-300">

                        <!-- 1. Image Section with Hover Overlay -->
                        <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3">
                            <!-- Product Image -->
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/b55d14e8-ae33-47e3-8b23-7296ac1ba55c.jpg"
                                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                alt="Product">

                            <!-- Discount Badge (Optional) -->
                            <div
                                class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                -18%
                            </div>

                            <!-- HOVER ICONS (Eye & Cart) -->
                            <div
                                class="absolute inset-0 bg-black/10 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[1px]">
                                <!-- Eye Icon (Details Page) -->
                                <a href="{{url('product-details')}}"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <!-- Cart Icon (Add to Cart Action) -->
                                <button onclick="addToCart(980)"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="Add to Cart">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Product Info Section -->
                        <div class="flex flex-col flex-1">
                            <h3
                                class="text-[13px] font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[36px] group-hover:text-[#FF6A00] transition-colors">
                                Soft Car Emoji Pillow 2pcs - Fun Pack
                            </h3>

                            <!-- Rating Stars -->
                            <div class="flex items-center gap-1 mb-2">
                                <div class="flex text-yellow-400 text-[10px]">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-[10px] text-gray-400 font-bold">(25)</span>
                            </div>

                            <!-- Price and Add to Cart Button Row -->
                            <div class="mt-auto flex items-center justify-between gap-2">
                                <div class="flex flex-col">
                                    <span class="text-[15px] font-black text-[#FF6A00]">৳৯৮০</span>
                                    <span class="text-[11px] text-gray-400 line-through font-bold">৳১২০০</span>
                                </div>

                                <!-- Bottom Add to Cart Button -->
                                <button onclick="addToCart(980)"
                                    class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-[#FF6A00] transition-colors">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div
                        class="group relative flex flex-col p-3 bg-white border border-gray-100 rounded-xl hover:shadow-xl transition-all duration-300">

                        <!-- 1. Image Section with Hover Overlay -->
                        <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3">
                            <!-- Product Image -->
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/519084f4-5bf8-4d77-a607-f75d64beeb1a.jpg"
                                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                alt="Product">

                            <!-- Discount Badge (Optional) -->
                            <div
                                class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                -18%
                            </div>

                            <!-- HOVER ICONS (Eye & Cart) -->
                            <div
                                class="absolute inset-0 bg-black/10 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[1px]">
                                <!-- Eye Icon (Details Page) -->
                                <a href="{{url('product-details')}}"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <!-- Cart Icon (Add to Cart Action) -->
                                <button onclick="addToCart(980)"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="Add to Cart">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Product Info Section -->
                        <div class="flex flex-col flex-1">
                            <h3
                                class="text-[13px] font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[36px] group-hover:text-[#FF6A00] transition-colors">
                                Soft Car Emoji Pillow 2pcs - Fun Pack
                            </h3>

                            <!-- Rating Stars -->
                            <div class="flex items-center gap-1 mb-2">
                                <div class="flex text-yellow-400 text-[10px]">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-[10px] text-gray-400 font-bold">(25)</span>
                            </div>

                            <!-- Price and Add to Cart Button Row -->
                            <div class="mt-auto flex items-center justify-between gap-2">
                                <div class="flex flex-col">
                                    <span class="text-[15px] font-black text-[#FF6A00]">৳৯৮০</span>
                                    <span class="text-[11px] text-gray-400 line-through font-bold">৳১২০০</span>
                                </div>

                                <!-- Bottom Add to Cart Button -->
                                <button onclick="addToCart(980)"
                                    class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-[#FF6A00] transition-colors">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5 -->
                    <div
                        class="group relative flex flex-col p-3 bg-white border border-gray-100 rounded-xl hover:shadow-xl transition-all duration-300">

                        <!-- 1. Image Section with Hover Overlay -->
                        <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3">
                            <!-- Product Image -->
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/519084f4-5bf8-4d77-a607-f75d64beeb1a.jpg"
                                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                alt="Product">

                            <!-- Discount Badge (Optional) -->
                            <div
                                class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                -18%
                            </div>

                            <!-- HOVER ICONS (Eye & Cart) -->
                            <div
                                class="absolute inset-0 bg-black/10 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[1px]">
                                <!-- Eye Icon (Details Page) -->
                                <a href="{{url('product-details')}}"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <!-- Cart Icon (Add to Cart Action) -->
                                <button onclick="addToCart(980)"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="Add to Cart">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Product Info Section -->
                        <div class="flex flex-col flex-1">
                            <h3
                                class="text-[13px] font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[36px] group-hover:text-[#FF6A00] transition-colors">
                                Soft Car Emoji Pillow 2pcs - Fun Pack
                            </h3>

                            <!-- Rating Stars -->
                            <div class="flex items-center gap-1 mb-2">
                                <div class="flex text-yellow-400 text-[10px]">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-[10px] text-gray-400 font-bold">(25)</span>
                            </div>

                            <!-- Price and Add to Cart Button Row -->
                            <div class="mt-auto flex items-center justify-between gap-2">
                                <div class="flex flex-col">
                                    <span class="text-[15px] font-black text-[#FF6A00]">৳৯৮০</span>
                                    <span class="text-[11px] text-gray-400 line-through font-bold">৳১২০০</span>
                                </div>

                                <!-- Bottom Add to Cart Button -->
                                <button onclick="addToCart(980)"
                                    class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-[#FF6A00] transition-colors">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 6 -->
                    <div
                        class="group relative flex flex-col p-3 bg-white border border-gray-100 rounded-xl hover:shadow-xl transition-all duration-300">

                        <!-- 1. Image Section with Hover Overlay -->
                        <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3">
                            <!-- Product Image -->
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/519084f4-5bf8-4d77-a607-f75d64beeb1a.jpg"
                                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                alt="Product">

                            <!-- Discount Badge (Optional) -->
                            <div
                                class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                -18%
                            </div>

                            <!-- HOVER ICONS (Eye & Cart) -->
                            <div
                                class="absolute inset-0 bg-black/10 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[1px]">
                                <!-- Eye Icon (Details Page) -->
                                <a href="{{url('product-details')}}"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <!-- Cart Icon (Add to Cart Action) -->
                                <button onclick="addToCart(980)"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="Add to Cart">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Product Info Section -->
                        <div class="flex flex-col flex-1">
                            <h3
                                class="text-[13px] font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[36px] group-hover:text-[#FF6A00] transition-colors">
                                Soft Car Emoji Pillow 2pcs - Fun Pack
                            </h3>

                            <!-- Rating Stars -->
                            <div class="flex items-center gap-1 mb-2">
                                <div class="flex text-yellow-400 text-[10px]">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-[10px] text-gray-400 font-bold">(25)</span>
                            </div>

                            <!-- Price and Add to Cart Button Row -->
                            <div class="mt-auto flex items-center justify-between gap-2">
                                <div class="flex flex-col">
                                    <span class="text-[15px] font-black text-[#FF6A00]">৳৯৮০</span>
                                    <span class="text-[11px] text-gray-400 line-through font-bold">৳১২০০</span>
                                </div>

                                <!-- Bottom Add to Cart Button -->
                                <button onclick="addToCart(980)"
                                    class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-[#FF6A00] transition-colors">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 7 -->
                    <div
                        class="group relative flex flex-col p-3 bg-white border border-gray-100 rounded-xl hover:shadow-xl transition-all duration-300">

                        <!-- 1. Image Section with Hover Overlay -->
                        <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3">
                            <!-- Product Image -->
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/519084f4-5bf8-4d77-a607-f75d64beeb1a.jpg"
                                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                alt="Product">

                            <!-- Discount Badge (Optional) -->
                            <div
                                class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                -18%
                            </div>

                            <!-- HOVER ICONS (Eye & Cart) -->
                            <div
                                class="absolute inset-0 bg-black/10 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[1px]">
                                <!-- Eye Icon (Details Page) -->
                                <a href="{{url('product-details')}}"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <!-- Cart Icon (Add to Cart Action) -->
                                <button onclick="addToCart(980)"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="Add to Cart">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Product Info Section -->
                        <div class="flex flex-col flex-1">
                            <h3
                                class="text-[13px] font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[36px] group-hover:text-[#FF6A00] transition-colors">
                                Soft Car Emoji Pillow 2pcs - Fun Pack
                            </h3>

                            <!-- Rating Stars -->
                            <div class="flex items-center gap-1 mb-2">
                                <div class="flex text-yellow-400 text-[10px]">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-[10px] text-gray-400 font-bold">(25)</span>
                            </div>

                            <!-- Price and Add to Cart Button Row -->
                            <div class="mt-auto flex items-center justify-between gap-2">
                                <div class="flex flex-col">
                                    <span class="text-[15px] font-black text-[#FF6A00]">৳৯৮০</span>
                                    <span class="text-[11px] text-gray-400 line-through font-bold">৳১২০০</span>
                                </div>

                                <!-- Bottom Add to Cart Button -->
                                <button onclick="addToCart(980)"
                                    class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-[#FF6A00] transition-colors">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 8 -->
                    <div
                        class="group relative flex flex-col p-3 bg-white border border-gray-100 rounded-xl hover:shadow-xl transition-all duration-300">

                        <!-- 1. Image Section with Hover Overlay -->
                        <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3">
                            <!-- Product Image -->
                            <img src="https://orenmart.sgp1.digitaloceanspaces.com/product/519084f4-5bf8-4d77-a607-f75d64beeb1a.jpg"
                                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                alt="Product">

                            <!-- Discount Badge (Optional) -->
                            <div
                                class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm">
                                -18%
                            </div>

                            <!-- HOVER ICONS (Eye & Cart) -->
                            <div
                                class="absolute inset-0 bg-black/10 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[1px]">
                                <!-- Eye Icon (Details Page) -->
                                <a href="{{url('product-details')}}"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="View Details">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <!-- Cart Icon (Add to Cart Action) -->
                                <button onclick="addToCart(980)"
                                    class="w-10 h-10 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md"
                                    title="Add to Cart">
                                    <i class="fas fa-shopping-cart text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Product Info Section -->
                        <div class="flex flex-col flex-1">
                            <h3
                                class="text-[13px] font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[36px] group-hover:text-[#FF6A00] transition-colors">
                                Soft Car Emoji Pillow 2pcs - Fun Pack
                            </h3>

                            <!-- Rating Stars -->
                            <div class="flex items-center gap-1 mb-2">
                                <div class="flex text-yellow-400 text-[10px]">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                        class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                <span class="text-[10px] text-gray-400 font-bold">(25)</span>
                            </div>

                            <!-- Price and Add to Cart Button Row -->
                            <div class="mt-auto flex items-center justify-between gap-2">
                                <div class="flex flex-col">
                                    <span class="text-[15px] font-black text-[#FF6A00]">৳৯৮০</span>
                                    <span class="text-[11px] text-gray-400 line-through font-bold">৳১২০০</span>
                                </div>

                                <!-- Bottom Add to Cart Button -->
                                <button onclick="addToCart(980)"
                                    class="bg-gray-900 text-white px-3 py-1.5 rounded-lg text-[11px] font-bold hover:bg-[#FF6A00] transition-colors">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>


                </div>
            </main>

        </div>

    </section>
@endsection
@push('scripts')
    <script>
        function addToCart(price) {
            alert("পণ্যটি কার্টে যোগ করা হয়েছে! দাম: ৳" + price);

            window.location.href = "./cart.html";
        }

        function toggleAllFilters() {
            const panel = document.getElementById('all-filters-panel');
            const arrow = document.getElementById('all-filters-arrow');
            const isHidden = panel.classList.contains('hidden');

            if (isHidden) {
                panel.classList.remove('hidden');
                arrow.style.transform = 'rotate(180deg)';
            } else {
                panel.classList.add('hidden');
                arrow.style.transform = 'rotate(0deg)';
            }
        }
    </script>
@endpush
