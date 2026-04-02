@extends('template1.layouts.front')

@section('content')
    <section class="container py-6 mx-auto px-4 lg:px-0">
        <!-- Dashboard Header -->
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-black text-[#1D2128]">আমার ড্যাশবোর্ড</h1>
            <button
                class="px-5 py-2 bg-white border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-all">
                লগআউট
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Left Sidebar: Persistent Profile & Nav -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg border border-gray-200 shadow-xs p-6 text-center">
                    <!-- Avatar -->
                    <div class="flex justify-center mb-4">
                        <div
                            class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center border-4 border-white shadow-sm overflow-hidden">
                            <i class="fas fa-user text-3xl text-gray-300"></i>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">John Doe</h3>
                    <p class="text-sm text-gray-500 font-medium">user@example.com</p>

                    <!-- Sidebar Menu -->
                    <nav class="mt-8 space-y-2" id="dashboard-nav">
                        <button onclick="showSection('overview', this)"
                            class="nav-link w-full flex items-center gap-3 px-4 py-3 bg-[#1D2128] text-white rounded-xl text-sm font-semibold transition-all">
                            <i class="far fa-user w-5 text-center"></i> ওভারভিউ
                        </button>
                        <button onclick="showSection('orders', this)"
                            class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-orange-50 hover:text-[#FF6A00] rounded-xl text-sm font-semibold transition-all">
                            <i class="fas fa-shopping-bag w-5 text-center"></i> আমার
                            অর্ডার
                        </button>
                        <button onclick="showSection('wishlist', this)"
                            class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-orange-50 hover:text-[#FF6A00] rounded-xl text-sm font-semibold transition-all">
                            <i class="far fa-heart w-5 text-center"></i> উইশলিস্ট
                        </button>
                        <button onclick="showSection('edit', this)"
                            class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-orange-50 hover:text-[#FF6A00] rounded-xl text-sm font-semibold transition-all">
                            <i class="far fa-edit w-5 text-center"></i> প্রোফাইল এডিট
                        </button>
                        <button onclick="showSection('password', this)"
                            class="nav-link w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-orange-50 hover:text-[#FF6A00] rounded-xl text-sm font-semibold transition-all">
                            <i class="fas fa-lock w-5 text-center"></i> পাসওয়ার্ড পরিবর্তন
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Right Side Content Area -->
            <div class="lg:col-span-3">
                <!-- 1. SECTION: OVERVIEW (Default) -->
                <div id="overview-section" class="dashboard-content space-y-6">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div
                            class="bg-white p-6 rounded-lg border border-gray-200 shadow-xs flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">
                                    মোট অর্ডার
                                </p>
                                <h4 class="text-2xl font-semibold text-gray-900">3</h4>
                            </div>
                            <div class="w-12 h-12 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-shopping-bag h-8 w-8 text-orange-500">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                                    <path d="M3 6h18"></path>
                                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                                </svg>
                            </div>
                        </div>
                        <div
                            class="bg-white p-6 rounded-lg border border-gray-200 shadow-xs flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">
                                    মোট খরচ
                                </p>
                                <h4 class="text-2xl font-semibold text-gray-900">৳3720</h4>
                            </div>
                            <div class="w-12 h-12 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-package h-8 w-8 text-green-500">
                                    <path
                                        d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z">
                                    </path>
                                    <path d="M12 22V12"></path>
                                    <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                    <path d="m7.5 4.27 9 5.15"></path>
                                </svg>
                            </div>
                        </div>
                        <div
                            class="bg-white p-6 rounded-2xl border border-gray-200 shadow-xs flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">
                                    উইশলিস্ট
                                </p>
                                <h4 class="text-2xl font-semibold text-gray-900">3</h4>
                            </div>
                            <div class="w-12 h-12 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-heart h-8 w-8 text-red-500">
                                    <path
                                        d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders Table -->
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-xl md:text-2xl font-bold text-gray-900">
                                সাম্প্রতিক অর্ডার
                            </h2>
                        </div>
                        <div class="p-6 space-y-4">
                            <div
                                class="flex flex-col md:flex-row items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50/50 transition-all gap-4">
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">ORD-001</p>
                                    <p class="text-sm text-gray-500 font-medium">
                                        ২০২৪-০১-১৫
                                    </p>
                                </div>
                                <span
                                    class="px-3 py-1 bg-green-100 text-green-700 text-sm font-semibold rounded-full flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-circle-check-big h-4 w-4 text-green-500">
                                        <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                        <path d="m9 11 3 3L22 4"></path>
                                    </svg>
                                    ডেলিভার হয়েছে</span>
                                <div class="flex items-center gap-6">
                                    <p class="font-semibold text-gray-900">৳1250</p>
                                    <a href="{{url('order-details')}}"
                                        class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:text-[#FF6A00] flex items-center gap-2 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-eye h-4 w-4 mr-1">
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                            </path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        দেখুন
                                    </a>
                                </div>
                            </div>
                            <div
                                class="flex flex-col md:flex-row items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50/50 transition-all gap-4">
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">ORD-002</p>
                                    <p class="text-sm text-gray-500 font-medium">
                                        ২০২৪-০১-২০
                                    </p>
                                </div>
                                <span
                                    class="px-3 py-1 bg-orange-100 text-orange-700 text-sm font-semibold rounded-full flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-package h-4 w-4 text-orange-500">
                                        <path
                                            d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z">
                                        </path>
                                        <path d="M12 22V12"></path>
                                        <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                        <path d="m7.5 4.27 9 5.15"></path>
                                    </svg>
                                    প্রসেসিং</span>
                                <div class="flex items-center gap-6">
                                    <p class="font-semibold text-gray-900">৳1250</p>
                                    <a href="{{url('order-details')}}"
                                        class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:text-[#FF6A00] flex items-center gap-2 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-eye h-4 w-4 mr-1">
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                            </path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        দেখুন
                                    </a>
                                </div>
                            </div>
                            <div
                                class="flex flex-col md:flex-row items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50/50 transition-all gap-4">
                                <div class="text-left">
                                    <p class="font-medium text-gray-900">ORD-001</p>
                                    <p class="text-sm text-gray-500 font-medium">
                                        ২০২৪-০১-১৫
                                    </p>
                                </div>
                                <span
                                    class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-semibold rounded-full flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-truck h-4 w-4 text-blue-500">
                                        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                                        <path d="M15 18H9"></path>
                                        <path
                                            d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                                        </path>
                                        <circle cx="17" cy="18" r="2"></circle>
                                        <circle cx="7" cy="18" r="2"></circle>
                                    </svg>
                                    শিপ করা হয়েছে</span>
                                <div class="flex items-center gap-6">
                                    <p class="font-semibold text-gray-900">৳1250</p>
                                    <a href="{{url('order-details')}}"
                                        class="px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:text-[#FF6A00] flex items-center gap-2 transition-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-eye h-4 w-4 mr-1">
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                            </path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        দেখুন
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. SECTION: ALL MY ORDERS (Initially Hidden) -->
                <div id="orders-section" class="dashboard-content hidden space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-xl md:text-2xl font-bold text-gray-900">
                                আমার সব অর্ডার
                            </h2>
                        </div>
                        <div class="p-6 space-y-8">
                            <!-- Detailed Order Card 1 -->
                            <div class="border border-gray-200 rounded-lg p-6 space-y-6">
                                <!-- Top Row: Order Info (Left) and Status/Price (Right) -->
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <!-- Left Side: Order ID & Date -->
                                    <div>
                                        <h4 class="font-semibold text-md text-gray-900">
                                            ORD-001
                                        </h4>
                                        <p class="text-sm text-gray-700 font-medium">
                                            অর্ডার তারিখ: ২০২৪-০১-১৫
                                        </p>
                                    </div>

                                    <!-- Right Side: Status Badge & Price (Pushed to Right) -->
                                    <div class="flex flex-col md:items-end gap-2 w-full md:w-auto">
                                        <span
                                            class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full flex items-center gap-1.5 w-fit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-circle-check-big h-4 w-4 text-green-500">
                                                <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                                <path d="m9 11 3 3L22 4"></path>
                                            </svg>
                                            ডেলিভার হয়েছে
                                        </span>
                                        <p class="text-lg font-bold text-gray-900 leading-none">
                                            ৳1250
                                        </p>
                                    </div>
                                </div>

                                <!-- Items Grid (Same as before) -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Item 1 -->
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shrink-0 border border-gray-100">
                                            <i class="fas fa-image text-gray-200"></i>
                                        </div>
                                        <div class="text-sm">
                                            <p class="text-gray-800 leading-tight">
                                                G63 Speaker Lamp
                                            </p>
                                            <p class="text-gray-500 font-medium">৳690</p>
                                        </div>
                                    </div>
                                    <!-- Item 2 -->
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shrink-0 border border-gray-100">
                                            <i class="fas fa-image text-gray-200"></i>
                                        </div>
                                        <div class="text-sm">
                                            <p class="text-gray-800 leading-tight">
                                                Car Seat Cover
                                            </p>
                                            <p class="text-gray-500 font-medium">৳450</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions (Same as before) -->
                                <div class="flex gap-3">
                                    <a href="./order-details.html"
                                        class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[#FF6A00] flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-eye h-4 w-4 mr-1">
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                            </path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        বিস্তারিত
                                    </a>
                                    <a href="./invoice.html"
                                        class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[#FF6A00] flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-download h-4 w-4 mr-1">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" x2="12" y1="15" y2="3"></line>
                                        </svg>
                                        ইনভয়েস
                                    </a>
                                </div>
                            </div>
                            <!-- Detailed Order Card 1 -->
                            <div class="border border-gray-200 rounded-lg p-6 space-y-6">
                                <!-- Top Row: Order Info (Left) and Status/Price (Right) -->
                                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                    <!-- Left Side: Order ID & Date -->
                                    <div>
                                        <h4 class="font-semibold text-md text-gray-900">
                                            ORD-001
                                        </h4>
                                        <p class="text-sm text-gray-700 font-medium">
                                            অর্ডার তারিখ: ২০২৪-০১-১৫
                                        </p>
                                    </div>

                                    <!-- Right Side: Status Badge & Price (Pushed to Right) -->
                                    <div class="flex flex-col md:items-end gap-2 w-full md:w-auto">
                                        <span
                                            class="px-3 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full flex items-center gap-1.5 w-fit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-package h-4 w-4 text-orange-500">
                                                <path
                                                    d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z">
                                                </path>
                                                <path d="M12 22V12"></path>
                                                <path d="m3.3 7 7.703 4.734a2 2 0 0 0 1.994 0L20.7 7"></path>
                                                <path d="m7.5 4.27 9 5.15"></path>
                                            </svg>
                                            প্রসেসিং
                                        </span>
                                        <p class="text-lg font-bold text-gray-900 leading-none">
                                            ৳1250
                                        </p>
                                    </div>
                                </div>

                                <!-- Items Grid (Same as before) -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Item 1 -->
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shrink-0 border border-gray-100">
                                            <i class="fas fa-image text-gray-200"></i>
                                        </div>
                                        <div class="text-sm">
                                            <p class="text-gray-800 leading-tight">
                                                G63 Speaker Lamp
                                            </p>
                                            <p class="text-gray-500 font-medium">৳690</p>
                                        </div>
                                    </div>
                                    <!-- Item 2 -->
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shrink-0 border border-gray-100">
                                            <i class="fas fa-image text-gray-200"></i>
                                        </div>
                                        <div class="text-sm">
                                            <p class="text-gray-800 leading-tight">
                                                Car Seat Cover
                                            </p>
                                            <p class="text-gray-500 font-medium">৳450</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions (Same as before) -->
                                <div class="flex gap-3">
                                    <a href="./order-details.html"
                                        class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[#FF6A00] flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-eye h-4 w-4 mr-1">
                                            <path
                                                d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                                            </path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        বিস্তারিত
                                    </a>
                                    <a href="./invoice.html"
                                        class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-600 hover:text-[#FF6A00] flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-download h-4 w-4 mr-1">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" x2="12" y1="15" y2="3"></line>
                                        </svg>
                                        ইনভয়েস
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. SECTION: WISHLIST (Initially Hidden) -->
                <div id="wishlist-section" class="dashboard-content hidden space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <!-- Header -->
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-900">আমার উইশলিস্ট</h2>
                        </div>

                        <div class="p-6">
                            <!-- Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- Wishlist Card 1 (In Stock) -->
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <!-- Image Placeholder -->
                                    <div
                                        class="w-full h-40 bg-gray-100 rounded-xl mb-4 flex items-center justify-center relative">
                                        <i class="far fa-image text-4xl text-gray-200"></i>
                                    </div>

                                    <h3 class="text-md text-gray-800 mb-3 line-clamp-2 h-10 leading-tight">
                                        Crystal Car Hanging Logo - Premium
                                    </h3>

                                    <div class="flex items-end justify-between mb-4">
                                        <div>
                                            <p class="text-lg font-semibold text-[#FF6A00]">৳1250</p>
                                            <p class="text-xs text-gray-400 line-through font-medium">
                                                ৳1500
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 bg-[#1D2128] text-white text-sm rounded-full">
                                            স্টকে আছে
                                        </span>
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            class="flex-1 py-2.5 bg-[#1D2128] text-white rounded-lg text-sm hover:bg-black transition-all">
                                            কার্টে যোগ করুন
                                        </button>
                                        <button
                                            class="w-10 h-10 flex items-center justify-center border border-gray-200 rounded-lg text-gray-600 hover:text-red-500 hover:border-red-500 transition-all">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Wishlist Card 2 (Out of Stock) -->
                                <div
                                    class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow opacity-90">
                                    <div class="w-full h-40 bg-gray-100 rounded-xl mb-4 flex items-center justify-center">
                                        <i class="far fa-image text-4xl text-gray-200"></i>
                                    </div>

                                    <h3 class="text-md text-gray-800 mb-3 line-clamp-2 h-10 leading-tight">
                                        Heavy Vehicle Camera Solution Set
                                    </h3>

                                    <div class="flex items-end justify-between mb-4">
                                        <div>
                                            <p class="text-lg font-semibold text-[#FF6A00]">
                                                ৳15000
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 bg-red-500 text-white text-sm rounded-full">
                                            স্টক নেই
                                        </span>
                                    </div>

                                    <div class="flex gap-2">
                                        <button disabled
                                            class="flex-1 py-2.5 bg-gray-400 text-white rounded-lg text-sm cursor-not-allowed">
                                            কার্টে যোগ করুন
                                        </button>
                                        <button
                                            class="w-10 h-10 flex items-center justify-center border border-gray-200 rounded-lg text-gray-600 hover:text-red-500 hover:border-red-500 transition-all">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Wishlist Card 3 -->
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <!-- Image Placeholder -->
                                    <div
                                        class="w-full h-40 bg-gray-100 rounded-xl mb-4 flex items-center justify-center relative">
                                        <i class="far fa-image text-4xl text-gray-200"></i>
                                    </div>

                                    <h3 class="text-md text-gray-800 mb-3 line-clamp-2 h-10 leading-tight">
                                        Crystal Car Hanging Logo - Premium
                                    </h3>

                                    <div class="flex items-end justify-between mb-4">
                                        <div>
                                            <p class="text-lg font-semibold text-[#FF6A00]">৳1550</p>
                                            <p class="text-xs text-gray-400 line-through font-medium">
                                                ৳1500
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 bg-[#1D2128] text-white text-sm rounded-full">
                                            স্টকে আছে
                                        </span>
                                    </div>

                                    <div class="flex gap-2">
                                        <button
                                            class="flex-1 py-2.5 bg-[#1D2128] text-white rounded-lg text-sm hover:bg-black transition-all">
                                            কার্টে যোগ করুন
                                        </button>
                                        <button
                                            class="w-10 h-10 flex items-center justify-center border border-gray-200 rounded-lg text-gray-600 hover:text-red-500 hover:border-red-500 transition-all">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. SECTION: PROFILE EDIT (Initially Hidden) -->
                <div id="edit-section" class="dashboard-content hidden space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-gray-900">প্রোফাইল তথ্য</h2>
                        </div>

                        <div class="p-6 pt-0">
                            <form action="#" class="space-y-6">
                                <!-- Grid Layout -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Name Field -->
                                    <div class="space-y-2">
                                        <label class="text-sm text-gray-700">নাম</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                                <i class="far fa-user text-sm"></i>
                                            </span>
                                            <input type="text" value="John Doe"
                                                class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-50 bg-gray-50/50 text-gray-800 text-sm outline-none focus:border-[#FF6A00] transition-all">
                                        </div>
                                    </div>

                                    <!-- Email Field -->
                                    <div class="space-y-2">
                                        <label class="text-sm text-gray-700">ইমেইল</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                                <i class="far fa-envelope text-sm"></i>
                                            </span>
                                            <input type="email" value="user@example.com"
                                                class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-50 bg-gray-50/50 text-gray-800 text-sm outline-none focus:border-[#FF6A00] transition-all">
                                        </div>
                                    </div>

                                    <!-- Phone Field -->
                                    <div class="space-y-2">
                                        <label class="text-sm text-gray-700">ফোন</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                                <i class="fas fa-phone-alt text-sm"></i>
                                            </span>
                                            <input type="tel" placeholder="যোগ করা হয়নি"
                                                class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-50 bg-gray-50/50 text-gray-800 text-sm outline-none focus:border-[#FF6A00] transition-all">
                                        </div>
                                    </div>

                                    <!-- Address Field -->
                                    <div class="space-y-2">
                                        <label class="text-sm text-gray-700">ঠিকানা</label>
                                        <div class="relative">
                                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                                <i class="fas fa-map-marker-alt text-sm"></i>
                                            </span>
                                            <input type="text" placeholder="যোগ করা হয়নি"
                                                class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-50 bg-gray-50/50 text-gray-800 text-sm outline-none focus:border-[#FF6A00] transition-all">
                                        </div>
                                    </div>
                                </div>

                                <!-- Update Button -->
                                <div class="pt-4">
                                    <button type="submit"
                                        class="flex items-center gap-2 bg-[#FF6A00] hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                                        <i class="far fa-edit"></i>
                                        প্রোফাইল আপডেট করুন
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- 5. SECTION: PASSWORD CHANGE (Initially Hidden) -->
                <div id="password-section" class="dashboard-content hidden space-y-6">
                    <div class="bg-white rounded-lg border border-gray-200 shadow-xs overflow-hidden">
                        <!-- Card Header -->
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-gray-900">পাসওয়ার্ড পরিবর্তন করুন</h2>
                        </div>

                        <div class="p-6 pt-0">
                            <form action="#" class="space-y-5 max-w-2xl">

                                <!-- Current Password -->
                                <div class="space-y-2">
                                    <label class="text-sm text-gray-800">বর্তমান পাসওয়ার্ড</label>
                                    <input type="password" placeholder="বর্তমান পাসওয়ার্ড লিখুন"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 text-gray-800 text-sm outline-none focus:border-[#FF6A00] transition-all placeholder:text-gray-400">
                                </div>

                                <!-- New Password -->
                                <div class="space-y-2">
                                    <label class="text-sm text-gray-800">নতুন পাসওয়ার্ড</label>
                                    <input type="password" placeholder="নতুন পাসওয়ার্ড লিখুন"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 text-gray-800 text-sm outline-none focus:border-[#FF6A00] transition-all placeholder:text-gray-400">
                                </div>

                                <!-- Confirm New Password -->
                                <div class="space-y-2">
                                    <label class="text-sm text-gray-800">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                                    <input type="password" placeholder="নতুন পাসওয়ার্ড আবার লিখুন"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 text-gray-800 text-sm outline-none focus:border-[#FF6A00] transition-all placeholder:text-gray-400">
                                </div>

                                <!-- Submit Button -->
                                <div class="pt-2">
                                    <button type="submit"
                                        class="bg-[#FF6A00] hover:bg-orange-600 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-all shadow-sm">
                                        পাসওয়ার্ড আপডেট করুন
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script>
        function showSection(sectionName, element) {
            const sections = document.querySelectorAll(".dashboard-content");
            sections.forEach((s) => s.classList.add("hidden"));

            const target = document.getElementById(sectionName + "-section");
            if (target) target.classList.remove("hidden");

            const navLinks = document.querySelectorAll(".nav-link");
            navLinks.forEach((link) => {
                link.classList.remove("bg-[#1D2128]", "text-white");
                link.classList.add("text-gray-600", "hover:bg-orange-50");
            });

            element.classList.add("bg-[#1D2128]", "text-white");
            element.classList.remove("text-gray-600", "hover:bg-orange-50");
        }
    </script>
@endpush
