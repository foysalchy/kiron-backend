@extends('template1.layouts.front')

@section('content')
    <!-- BRAND TOP INFO SECTION -->
    <section class="py-6 font-['Outfit']">
        <div class="container mx-auto px-4">
            <!-- Main Card Container -->
            <div class="text-center">
                <!-- Page Title -->
                <h2 class="text-2xl md:text-4xl font-semibold text-[#1D2128] mb-3">
                    All Brands
                </h2>

                <p class="text-base md:text-lg text-gray-500 font-medium max-w-2xl mx-auto leading-relaxed">
                    Discover products from your favorite brands. We partner with top manufacturers to bring you quality
                    products at the best prices.
                </p>
            </div>
        </div>
    </section>
    <!-- ALL BRANDS GRID SECTION -->
    <section class="py-6 container mx-auto">
        <div class="">

            <!-- Section Header -->
            <h2 class="text-2xl font-black text-[#1D2128] mb-8">All Brands</h2>

            <!-- Brands Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Brand Card: Apple -->
                <a href="/brand/apple" class="group block h-full">
                    <div
                        class="p-6 bg-white border border-gray-200 rounded-lg shadow-xs group-hover:shadow-xl group-hover:border-orange-100 transition-all duration-300">
                        <div class="flex items-start gap-4">
                            <!-- Logo Placeholder -->
                            <div
                                class="w-16 h-16 bg-gray-50 rounded-xl flex items-center justify-center group-hover:bg-orange-50 shrink-0 transition-colors">
                                <i class="fab fa-apple text-3xl text-gray-400 group-hover:text-[#FF6A00]"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-2">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 group-hover:text-[#FF6A00] truncate transition-colors">
                                        Apple</h3>
                                    <span
                                        class="px-2.5 py-0.5 bg-[#FF6A00] text-white text-[10px] font-bold rounded-full uppercase tracking-wider">Featured</span>
                                </div>
                                <p class="text-md text-gray-500 mb-4 line-clamp-2 leading-relaxed">Premium technology
                                    products and high-end electronics.</p>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="px-2.5 py-1 bg-gray-100 text-gray-600 text-[11px] font-bold rounded-lg group-hover:bg-orange-100 group-hover:text-[#FF6A00] transition-colors uppercase">Electronics</span>
                                    <span class="text-sm text-gray-400 tracking-tight">45+ Products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Brand Card: Samsung -->
                <a href="/brand/samsung" class="group block h-full">
                    <div
                        class="p-6 bg-white border border-gray-200 rounded-lg shadow-xs group-hover:shadow-xl group-hover:border-orange-100 transition-all duration-300">
                        <div class="flex items-start gap-4">
                            <!-- Logo Placeholder -->
                            <div
                                class="w-16 h-16 bg-gray-50 rounded-xl flex items-center justify-center group-hover:bg-orange-50 shrink-0 transition-colors">
                                <i class="fas fa-mobile-alt text-2xl text-gray-400 group-hover:text-[#FF6A00]"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-2">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 group-hover:text-[#FF6A00] truncate transition-colors">
                                        Samsung</h3>
                                    <span
                                        class="px-2.5 py-0.5 bg-[#FF6A00] text-white text-[10px] font-bold rounded-full uppercase tracking-wider">Featured</span>
                                </div>
                                <p class="text-md text-gray-500 mb-4 line-clamp-2 leading-relaxed">Leading electronics
                                    manufacturer.</p>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="px-2.5 py-1 bg-gray-100 text-gray-600 text-[11px] font-bold rounded-lg group-hover:bg-orange-100 group-hover:text-[#FF6A00] transition-colors uppercase">Electronics</span>
                                    <span class="text-sm text-gray-400 tracking-tight">45+ Products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Brand Card: Nike -->
                <a href="/brand/nike" class="group block h-full">
                    <div
                        class="p-6 bg-white border border-gray-200 rounded-lg shadow-xs group-hover:shadow-xl group-hover:border-orange-100 transition-all duration-300">
                        <div class="flex items-start gap-4">
                            <!-- Logo Placeholder -->
                            <div
                                class="w-16 h-16 bg-gray-50 rounded-xl flex items-center justify-center group-hover:bg-orange-50 shrink-0 transition-colors">
                                <i class="fas fa-running text-2xl text-gray-400 group-hover:text-[#FF6A00]"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-2">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 group-hover:text-[#FF6A00] truncate transition-colors">
                                        Nike</h3>
                                    <span
                                        class="px-2.5 py-0.5 bg-[#FF6A00] text-white text-[10px] font-bold rounded-full uppercase tracking-wider">Featured</span>
                                </div>
                                <p class="text-md text-gray-500 mb-4 line-clamp-2 leading-relaxed">Leading electronics
                                    manufacturer.</p>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="px-2.5 py-1 bg-gray-100 text-gray-600 text-[11px] font-bold rounded-lg group-hover:bg-orange-100 group-hover:text-[#FF6A00] transition-colors uppercase">Electronics</span>
                                    <span class="text-sm text-gray-400 tracking-tight">45+ Products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Brand Card: Sony (Without Featured Badge) -->
                <a href="/brand/sony" class="group block h-full">
                    <div
                        class="p-6 bg-white border border-gray-200 rounded-lg shadow-xs group-hover:shadow-xl group-hover:border-orange-100 transition-all duration-300">
                        <div class="flex items-start gap-4">
                            <!-- Logo Placeholder -->
                            <div
                                class="w-16 h-16 bg-gray-50 rounded-xl flex items-center justify-center group-hover:bg-orange-50 shrink-0 transition-colors">
                                <i class="fas fa-gamepad text-2xl text-gray-400 group-hover:text-[#FF6A00]"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-2">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 group-hover:text-[#FF6A00] truncate transition-colors">
                                        Sony</h3>
                                    <span
                                        class="px-2.5 py-0.5 bg-[#FF6A00] text-white text-[10px] font-bold rounded-full uppercase tracking-wider">Featured</span>
                                </div>
                                <p class="text-md text-gray-500 mb-4 line-clamp-2 leading-relaxed">Leading electronics
                                    manufacturer.</p>

                                <div class="flex items-center justify-between">
                                    <span
                                        class="px-2.5 py-1 bg-gray-100 text-gray-600 text-[11px] font-bold rounded-lg group-hover:bg-orange-100 group-hover:text-[#FF6A00] transition-colors uppercase">Electronics</span>
                                    <span class="text-sm text-gray-400 tracking-tight">45+ Products</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

            </div>
        </div>
    </section>
@endsection
