@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto py-6">
        <!-- Header Actions -->
        <div class="flex flex-col md:flex-row items-center justify-between mb-2 gap-4">
            <div class="flex items-center gap-4">
                <a href="{{url('dashboard')}}"
                    class="flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                    <i class="fas fa-arrow-left"></i> ড্যাশবোর্ড
                </a>
                <h1 class="text-2xl font-black text-[#1D2128]">অর্ডার বিস্তারিত</h1>
            </div>
            <div class="flex flex-col items-end">
                <span class="px-4 py-1 bg-green-500 text-white text-xs font-bold rounded-full mb-1">ডেলিভার সম্পন্ন</span>
                <p class="text-sm text-gray-500 font-medium">অর্ডার তারিখ: ১৮/১/২০২৪</p>
            </div>
        </div>
        <p class="text-md text-gray-500 mb-8">অর্ডার নম্বর: ORD-2024-001</p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Left Column (Products & Tracking) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Ordered Products Section -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden p-6">
                    <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-6">অর্ডারকৃত পণ্যসমূহ</h3>

                    <div class="space-y-4">
                        <!-- Product Item 1 -->
                        <div
                            class="flex flex-col sm:flex-row items-center gap-6 p-4 border border-gray-200 rounded-lg bg-white">
                            <div
                                class="w-24 h-24 bg-gray-50 rounded-xl flex items-center justify-center border border-gray-100 shrink-0">
                                <i class="fas fa-image text-3xl text-gray-200"></i>
                            </div>
                            <div class="flex-1 text-center sm:text-left">
                                <h4 class="text-lg font-base text-gray-900 mb-1">G63 Speaker Lamp - Multi-Function Bluetooth
                                    Speaker</h4>
                                <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-2">
                                    <span
                                        class="px-2 py-0.5 text-xs font-semibold border border-gray-200 rounded-full text-gray-800 rounded">নীল</span>
                                    <span
                                        class="px-2 py-0.5 text-xs font-semibold border border-gray-200 rounded-full text-gray-800 rounded">মাঝারি</span>
                                </div>
                                <p class="text-xs text-gray-500 font-base mb-2 uppercase">SKU: G63-SL-001-BL-MD</p>
                                <p class=" text-gray-900">
                                    <span class="text-sm ">৳690 × 2</span>
                                    <span class="text-md font-semibold text-[#FF6A00] ml-2">৳1380</span>
                                </p>
                            </div>
                            <button
                                class="px-4 py-2 border border-[#FF6A00] text-[#FF6A00] rounded-lg text-sm hover:bg-orange-50 transition-all flex items-center gap-2">
                                <i class="far fa-star"></i> রিভিউ দিন
                            </button>
                        </div>

                        <!-- Product Item 2 -->
                        <div
                            class="flex flex-col sm:flex-row items-center gap-6 p-4 border border-gray-200 rounded-lg bg-white">
                            <div
                                class="w-24 h-24 bg-gray-50 rounded-xl flex items-center justify-center border border-gray-100 shrink-0">
                                <i class="fas fa-image text-3xl text-gray-200"></i>
                            </div>
                            <div class="flex-1 text-center sm:text-left">
                                <h4 class="text-lg font-base text-gray-900 mb-1">Wireless Headphones</h4>
                                <div class="flex flex-wrap justify-center sm:justify-start gap-2 mb-2">
                                    <span
                                        class="px-2 py-0.5 text-xs font-semibold border border-gray-200 rounded-full text-gray-800 rounded">কালো</span>
                                    <span
                                        class="px-2 py-0.5 text-xs font-semibold border border-gray-200 rounded-full text-gray-800 rounded">স্ট্যান্ডার্ড</span>
                                </div>
                                <p class="text-xs text-gray-500 font-base mb-2 uppercase">SKU: WH-001-BK-ST</p>
                                <p class=" text-gray-900">
                                    <span class="text-sm ">৳690 × 2</span>
                                    <span class="text-md font-semibold text-[#FF6A00] ml-2">৳1380</span>
                                </p>
                            </div>
                            <button disabled
                                class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg text-xs font-bold flex items-center gap-2 cursor-not-allowed">
                                <i class="fas fa-star"></i> রিভিউ দেওয়া হয়েছে
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Order Tracking Section -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden p-6">
                    <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-8">অর্ডার ট্র্যাকিং</h3>

                    <!-- Vertical Timeline -->
                    <div class="relative pl-8 space-y-8  before:bg-green-500">
                        <!-- Step 1 -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Order Placed</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-15 10:30 AM</p>
                        </div>
                        <!-- Step 2 -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Order Confirmed</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-15 11:00 AM</p>
                        </div>
                        <!-- Step 3 -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Processing</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-15 02:00 PM</p>
                        </div>
                        <!-- Step 4 -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Shipped</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-16 09:00 AM</p>
                        </div>
                        <!-- Step 5 -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Out for Delivery</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-18 08:00 AM</p>
                        </div>
                        <!-- Step 6 (Final) -->
                        <div class="relative">
                            <div
                                class="absolute -left-8 top-2 w-4 h-4 rounded-full bg-green-500 flex items-center justify-center z-10">
                            </div>
                            <p class="font-medium text-md text-green-700 leading-none mb-1">Delivered</p>
                            <p class="text-sm text-gray-600 font-base">2024-01-18 03:30 PM</p>
                        </div>
                    </div>

                    <!-- Courier Box -->
                    <div
                        class="mt-10 p-4 bg-blue-50 border border-blue-100 rounded-xl flex flex-col md:flex-row items-center justify-between gap-4">
                        <div>
                            <h5 class="text-blue-800 text-md mb-1">কুরিয়ার ট্র্যাকিং</h5>
                            <p class="text-sm text-blue-700">Sundarban Courier - SA123456789BD</p>
                        </div>
                        <button
                            class="w-full md:w-auto px-6 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-700 transition-all flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-external-link h-4 w-4 mr-2">
                                <path d="M15 3h6v6"></path>
                                <path d="M10 14 21 3"></path>
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            </svg>
                            ট্র্যাক করুন
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column (Info Cards) -->
            <div class="space-y-6">

                <!-- Customer Information -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-900 mb-6">কাস্টমার তথ্য</h3>
                    <div class="space-y-4">
                        <div class="flex items-start ">
                            <div class="w-10 h-10 flex items-center justify-center text-gray-400 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-map-pin h-4 w-4 text-gray-500">
                                    <path
                                        d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0">
                                    </path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                            </div>
                            <div>
                                <p class="text-gray-900 text-md">মোহাম্মদ রহিম</p>
                                <p class="text-sm text-gray-500 leading-relaxed font-medium">১২৩ ধানমন্ডি, ঢাকা-১২০৫</p>
                            </div>
                        </div>
                        <div class="flex items-center ">
                            <div class="w-10 h-10 flex items-center justify-center text-gray-400 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-phone h-4 w-4 text-gray-500">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-gray-900 text-md">+8801712345678</p>
                        </div>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-900 mb-6">পেমেন্ট সামারি</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between text-md text-gray-700 font-medium">
                            <span>সাবটোটাল:</span>
                            <span class="text-gray-900">৳2580</span>
                        </div>
                        <div class="flex justify-between text-md text-gray-700 font-medium">
                            <span>ডেলিভারি চার্জ:</span>
                            <span class="text-gray-900">৳60</span>
                        </div>
                        <div class="pt-3 border-t border-gray-200 flex justify-between items-center text-lg font-semibold">
                            <span class="text-gray-900">মোট:</span>
                            <span class="text-gray-900">৳2640</span>
                        </div>
                        <p class="text-sm text-gray-700 mt-2">পেমেন্ট পদ্ধতি: Cash on Delivery</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6">
                    <h3 class="text-lg md:text-2xl font-bold text-gray-900 mb-6">অ্যাকশন</h3>
                    <div class="space-y-3">
                        <a href="./invoice.html"
                            class="w-full py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-800 hover:border-[#FF6A00] hover:text-[#FF6A00] transition-all flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-download h-4 w-4 mr-2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" x2="12" y1="15" y2="3"></line>
                            </svg>
                            ইনভয়েস ডাউনলোড
                        </a>
                        <a href="./support.html"
                            class="w-full py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-800 hover:border-[#FF6A00] hover:text-[#FF6A00] transition-all flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-message-square h-4 w-4 mr-2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            সাপোর্ট যোগাযোগ
                        </a>
                        <button
                            class="w-full py-2.5 bg-white border border-gray-200 rounded-md text-sm text-gray-800 hover:border-[#FF6A00] hover:text-[#FF6A00] transition-all flex items-center justify-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-package h-4 w-4 mr-2">
                                <path
                                    d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z">
                                </path>
                                <path d="M12 22V12"></path>
                                <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                <path d="m7.5 4.27 9 5.15"></path>
                            </svg>
                            রিটার্ন রিকোয়েস্ট
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
