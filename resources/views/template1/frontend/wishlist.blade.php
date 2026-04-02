@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto py-6">
        <!-- Top Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-4">
                <a href="{{url('/dashboard')}}"
                    class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all">
                    <i class="fas fa-arrow-left text-[10px]"></i> ড্যাশবোর্ডে ফিরুন
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-[#1D2128]">আমার উইশলিস্ট</h1>
                    <p class="text-md text-gray-500 font-medium">6 টি পণ্য</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    class="flex items-center gap-2 bg-[#22C55E] hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-shopping-cart h-4 w-4 mr-2">
                        <circle cx="8" cy="21" r="1"></circle>
                        <circle cx="19" cy="21" r="1"></circle>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                    </svg> সব কার্টে যোগ করুন
                </button>
                <button
                    class="flex items-center gap-2 border border-red-500 text-red-500 hover:bg-red-50 px-4 py-2 rounded-md text-sm font-medium transition-all">
                    <i class="far fa-trash-alt text-xs"></i> সব মুছুন
                </button>
            </div>
        </div>

        <!-- Wishlist Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

            <!-- Card 1: G63 Speaker -->
            <div
                class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-xs hover:shadow-md transition-shadow group">
                <div class="relative aspect-square bg-[#F3F4F6] flex items-center justify-center overflow-hidden">
                    <!-- Placeholder Icon Pattern -->
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <div class="w-32 h-32 border-2 border-gray-400 rounded-full flex items-center justify-center">
                            <div class="w-20 h-20 border-2 border-gray-400 rounded-full"></div>
                        </div>
                    </div>
                    <i class="fa-regular fa-image text-gray-300 text-4xl relative z-10"></i>

                    <!-- Badges -->
                    <span
                        class="absolute top-3 left-3 bg-[#EF4444] text-white text-xs font-medium px-3 py-1 rounded-full">-13%</span>
                    <button class="absolute top-3 right-3 text-[#EF4444] bg-white rounded-full h-8 w-8">
                        <i class="fas fa-heart text-md text-center"></i>
                    </button>
                </div>
                <div class="p-4">
                    <h3 class="text-md font-medium text-gray-800 mb-2 line-clamp-2 leading-tight">G63 Speaker Lamp -
                        Multi-Function Bluetooth Speaker</h3>
                    <div class="flex items-center gap-1 text-yellow-400 text-xs mb-3">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="far fa-star text-gray-300"></i>
                        <span class="text-gray-500 font-medium ml-1 text-xs">(42)</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-baseline gap-2">
                            <span class="text-[#FF6A00] font-semibold text-lg">৳690</span>
                            <span class="text-gray-400 text-xs line-through">৳790</span>
                        </div>
                        <span
                            class="bg-[#1D2128] text-white text-xs font-semibold px-2 py-1 rounded-full uppercase tracking-tighter">স্টকে
                            আছে</span>
                    </div>
                    <div class="flex gap-2">
                        <button
                            class="flex-1 flex items-center justify-center gap-2 bg-[#FF6A00] hover:bg-orange-600 text-white py-2 rounded text-sm font-medium transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shopping-cart h-4 w-4 mr-1">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12">
                                </path>
                            </svg> কার্টে যোগ করুন
                        </button>
                        <button
                            class="w-10 h-9 flex items-center justify-center border border-red-200 text-red-600 hover:bg-red-500 hover:text-white rounded transition-all">
                            <i class="far fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card 2: Stock Out Fixed -->
            <div
                class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow group">
                <div class="relative aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">

                    <div class="absolute inset-0 grayscale opacity-40 flex items-center justify-center bg-[#9CA3AF]">
                        <i class="fa-regular fa-image text-gray-200 text-4xl"></i>
                    </div>

                    <div class="absolute inset-0 z-10 flex items-center justify-center">
                        <span
                            class="bg-[#EF4444] text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider shadow-lg">
                            স্টক নেই
                        </span>
                    </div>

                    <button
                        class="absolute top-3 right-3 text-[#EF4444] bg-white rounded-full h-8 w-8 shadow-sm flex items-center justify-center z-20 hover:scale-110 transition-transform">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <div class="p-4 opacity-75">
                    <h3 class="text-md font-medium text-gray-800 mb-2 line-clamp-2 leading-tight">
                        Heavy Vehicle Camera Solution Set
                    </h3>
                    <div class="flex items-center gap-1 text-yellow-400 text-xs mb-3">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                        <span class="text-gray-500 font-medium ml-1 text-xs">(১৫)</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[#FF6A00] font-semibold text-lg">৳১২৫০০</span>
                        <span class="bg-[#EF4444] text-white text-xs font-semibold px-2 py-1 rounded-full uppercase">স্টক
                            নেই</span>
                    </div>
                    <div class="flex gap-2">
                        <button disabled
                            class="flex-1 flex items-center justify-center gap-2 bg-orange-300 text-white py-2 rounded text-xs font-medium cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shopping-cart h-4 w-4 mr-1">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12">
                                </path>
                            </svg> কার্টে যোগ করুন
                        </button>
                        <button
                            class="w-10 h-9 flex items-center justify-center border border-red-200 text-red-600 hover:bg-red-500 hover:text-white rounded transition-all">
                            <i class="far fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Card 1: G63 Speaker -->
            <div
                class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-xs hover:shadow-md transition-shadow group">
                <div class="relative aspect-square bg-[#F3F4F6] flex items-center justify-center overflow-hidden">
                    <!-- Placeholder Icon Pattern -->
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <div class="w-32 h-32 border-2 border-gray-400 rounded-full flex items-center justify-center">
                            <div class="w-20 h-20 border-2 border-gray-400 rounded-full"></div>
                        </div>
                    </div>
                    <i class="fa-regular fa-image text-gray-300 text-4xl relative z-10"></i>

                    <!-- Badges -->
                    <span
                        class="absolute top-3 left-3 bg-[#EF4444] text-white text-xs font-medium px-3 py-1 rounded-full">-13%</span>
                    <button class="absolute top-3 right-3 text-[#EF4444] bg-white rounded-full h-8 w-8">
                        <i class="fas fa-heart text-md text-center"></i>
                    </button>
                </div>
                <div class="p-4">
                    <h3 class="text-md font-medium text-gray-800 mb-2 line-clamp-2 leading-tight">G63 Speaker Lamp -
                        Multi-Function Bluetooth Speaker</h3>
                    <div class="flex items-center gap-1 text-yellow-400 text-xs mb-3">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="far fa-star text-gray-300"></i>
                        <span class="text-gray-500 font-medium ml-1 text-xs">(42)</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-baseline gap-2">
                            <span class="text-[#FF6A00] font-semibold text-lg">৳690</span>
                            <span class="text-gray-400 text-xs line-through">৳790</span>
                        </div>
                        <span
                            class="bg-[#1D2128] text-white text-xs font-semibold px-2 py-1 rounded-full uppercase tracking-tighter">স্টকে
                            আছে</span>
                    </div>
                    <div class="flex gap-2">
                        <button
                            class="flex-1 flex items-center justify-center gap-2 bg-[#FF6A00] hover:bg-orange-600 text-white py-2 rounded text-sm font-medium transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shopping-cart h-4 w-4 mr-1">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12">
                                </path>
                            </svg> কার্টে যোগ করুন
                        </button>
                        <button
                            class="w-10 h-9 flex items-center justify-center border border-red-200 text-red-600 hover:bg-red-500 hover:text-white rounded transition-all">
                            <i class="far fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card 2: Stock Out Fixed -->
            <div
                class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow group">
                <div class="relative aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">

                    <div class="absolute inset-0 grayscale opacity-40 flex items-center justify-center bg-[#9CA3AF]">
                        <i class="fa-regular fa-image text-gray-200 text-4xl"></i>
                    </div>

                    <div class="absolute inset-0 z-10 flex items-center justify-center">
                        <span
                            class="bg-[#EF4444] text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider shadow-lg">
                            স্টক নেই
                        </span>
                    </div>

                    <button
                        class="absolute top-3 right-3 text-[#EF4444] bg-white rounded-full h-8 w-8 shadow-sm flex items-center justify-center z-20 hover:scale-110 transition-transform">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <div class="p-4 opacity-75">
                    <h3 class="text-md font-medium text-gray-800 mb-2 line-clamp-2 leading-tight">
                        Heavy Vehicle Camera Solution Set
                    </h3>
                    <div class="flex items-center gap-1 text-yellow-400 text-xs mb-3">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                        <span class="text-gray-500 font-medium ml-1 text-xs">(১৫)</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[#FF6A00] font-semibold text-lg">৳১২৫০০</span>
                        <span class="bg-[#EF4444] text-white text-xs font-semibold px-2 py-1 rounded-full uppercase">স্টক
                            নেই</span>
                    </div>
                    <div class="flex gap-2">
                        <button disabled
                            class="flex-1 flex items-center justify-center gap-2 bg-orange-300 text-white py-2 rounded text-xs font-medium cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shopping-cart h-4 w-4 mr-1">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12">
                                </path>
                            </svg> কার্টে যোগ করুন
                        </button>
                        <button
                            class="w-10 h-9 flex items-center justify-center border border-red-200 text-red-600 hover:bg-red-500 hover:text-white rounded transition-all">
                            <i class="far fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Card 1: G63 Speaker -->
            <div
                class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-xs hover:shadow-md transition-shadow group">
                <div class="relative aspect-square bg-[#F3F4F6] flex items-center justify-center overflow-hidden">
                    <!-- Placeholder Icon Pattern -->
                    <div class="absolute inset-0 opacity-10 flex items-center justify-center">
                        <div class="w-32 h-32 border-2 border-gray-400 rounded-full flex items-center justify-center">
                            <div class="w-20 h-20 border-2 border-gray-400 rounded-full"></div>
                        </div>
                    </div>
                    <i class="fa-regular fa-image text-gray-300 text-4xl relative z-10"></i>

                    <!-- Badges -->
                    <span
                        class="absolute top-3 left-3 bg-[#EF4444] text-white text-xs font-medium px-3 py-1 rounded-full">-13%</span>
                    <button class="absolute top-3 right-3 text-[#EF4444] bg-white rounded-full h-8 w-8">
                        <i class="fas fa-heart text-md text-center"></i>
                    </button>
                </div>
                <div class="p-4">
                    <h3 class="text-md font-medium text-gray-800 mb-2 line-clamp-2 leading-tight">G63 Speaker Lamp -
                        Multi-Function Bluetooth Speaker</h3>
                    <div class="flex items-center gap-1 text-yellow-400 text-xs mb-3">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="far fa-star text-gray-300"></i>
                        <span class="text-gray-500 font-medium ml-1 text-xs">(42)</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-baseline gap-2">
                            <span class="text-[#FF6A00] font-semibold text-lg">৳690</span>
                            <span class="text-gray-400 text-xs line-through">৳790</span>
                        </div>
                        <span
                            class="bg-[#1D2128] text-white text-xs font-semibold px-2 py-1 rounded-full uppercase tracking-tighter">স্টকে
                            আছে</span>
                    </div>
                    <div class="flex gap-2">
                        <button
                            class="flex-1 flex items-center justify-center gap-2 bg-[#FF6A00] hover:bg-orange-600 text-white py-2 rounded text-sm font-medium transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shopping-cart h-4 w-4 mr-1">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12">
                                </path>
                            </svg> কার্টে যোগ করুন
                        </button>
                        <button
                            class="w-10 h-9 flex items-center justify-center border border-red-200 text-red-600 hover:bg-red-500 hover:text-white rounded transition-all">
                            <i class="far fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card 2: Stock Out Fixed -->
            <div
                class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow group">
                <div class="relative aspect-square bg-gray-100 flex items-center justify-center overflow-hidden">

                    <div class="absolute inset-0 grayscale opacity-40 flex items-center justify-center bg-[#9CA3AF]">
                        <i class="fa-regular fa-image text-gray-200 text-4xl"></i>
                    </div>

                    <div class="absolute inset-0 z-10 flex items-center justify-center">
                        <span
                            class="bg-[#EF4444] text-white text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider shadow-lg">
                            স্টক নেই
                        </span>
                    </div>

                    <button
                        class="absolute top-3 right-3 text-[#EF4444] bg-white rounded-full h-8 w-8 shadow-sm flex items-center justify-center z-20 hover:scale-110 transition-transform">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <div class="p-4 opacity-75">
                    <h3 class="text-md font-medium text-gray-800 mb-2 line-clamp-2 leading-tight">
                        Heavy Vehicle Camera Solution Set
                    </h3>
                    <div class="flex items-center gap-1 text-yellow-400 text-xs mb-3">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                        <span class="text-gray-500 font-medium ml-1 text-xs">(১৫)</span>
                    </div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[#FF6A00] font-semibold text-lg">৳১২৫০০</span>
                        <span class="bg-[#EF4444] text-white text-xs font-semibold px-2 py-1 rounded-full uppercase">স্টক
                            নেই</span>
                    </div>
                    <div class="flex gap-2">
                        <button disabled
                            class="flex-1 flex items-center justify-center gap-2 bg-orange-300 text-white py-2 rounded text-xs font-medium cursor-not-allowed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shopping-cart h-4 w-4 mr-1">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12">
                                </path>
                            </svg> কার্টে যোগ করুন
                        </button>
                        <button
                            class="w-10 h-9 flex items-center justify-center border border-red-200 text-red-600 hover:bg-red-500 hover:text-white rounded transition-all">
                            <i class="far fa-trash-alt text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- Bottom See More Button -->
        <div class="mt-12 text-center">
            <a href="./shop.html"
                class="inline-block px-8 py-2.5 border border-gray-200 text-gray-800 font-medium rounded-md hover:bg-gray-50 text-sm shadow-xs">
                আরো পণ্য দেখুন
            </a>
        </div>

    </section>
@endsection
