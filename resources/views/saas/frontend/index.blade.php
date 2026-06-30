@extends('saas.layouts.layout')

@section('content')

@php
    $solutions = [

        'ecommerce' => [
            'title' => 'E-Commerce',
            'desc' => 'Power your online store with automated order processing, inventory sync, and seamless customer experience.',
            'mockup' => 'ecommerce_dashboard_preview',
            'img' => 'https://via.placeholder.com/900x600?text=Ecommerce+Dashboard',
            'features' => [
                ['icon' => 'fa-globe', 'text' => 'Website Integration'],
                ['icon' => 'fa-cart-shopping', 'text' => 'Order Management'],
                ['icon' => 'fa-box', 'text' => 'Product Catalog'],
                ['icon' => 'fa-truck', 'text' => 'Shipping Integration'],
                ['icon' => 'fa-credit-card', 'text' => 'Online Payments'],
            ]
        ],

        'pos' => [
            'title' => 'POS System',
            'desc' => 'Fast and reliable POS system for retail and wholesale billing with barcode support.',
            'mockup' => 'pos_system_preview',
            'img' => 'https://via.placeholder.com/900x600?text=POS+System',
            'features' => [
                ['icon' => 'fa-barcode', 'text' => 'Barcode Scanning'],
                ['icon' => 'fa-receipt', 'text' => 'Instant Invoice'],
                ['icon' => 'fa-cash-register', 'text' => 'Fast Billing'],
                ['icon' => 'fa-credit-card', 'text' => 'Multiple Payments'],
                ['icon' => 'fa-rotate-left', 'text' => 'Sales Return'],
            ]
        ],

        'erp' => [
            'title' => 'ERP Core',
            'desc' => 'Manage your entire business operations including sales, purchase, inventory and warehouse.',
            'mockup' => 'erp_core_system',
            'img' => 'https://via.placeholder.com/900x600?text=ERP+System',
            'features' => [
                ['icon' => 'fa-layer-group', 'text' => 'Centralized System'],
                ['icon' => 'fa-sitemap', 'text' => 'Multi Module Control'],
                ['icon' => 'fa-boxes-stacked', 'text' => 'Inventory Management'],
                ['icon' => 'fa-cart-plus', 'text' => 'Sales & Purchase'],
                ['icon' => 'fa-warehouse', 'text' => 'Warehouse Control'],
            ]
        ],

        'crm' => [
            'title' => 'CRM',
            'desc' => 'Manage leads, customers, and improve sales conversion with smart tracking.',
            'mockup' => 'crm_dashboard',
            'img' => 'https://via.placeholder.com/900x600?text=CRM+System',
            'features' => [
                ['icon' => 'fa-user', 'text' => 'Lead Management'],
                ['icon' => 'fa-users', 'text' => 'Customer Profiles'],
                ['icon' => 'fa-bullseye', 'text' => 'Sales Tracking'],
                ['icon' => 'fa-bell', 'text' => 'Follow-up Reminders'],
                ['icon' => 'fa-chart-line', 'text' => 'Conversion Analytics'],
            ]
        ],

        'accounting' => [
            'title' => 'Accounting',
            'desc' => 'Complete financial management with profit, loss, cash flow and reporting tools.',
            'mockup' => 'accounting_system',
            'img' => 'https://via.placeholder.com/900x600?text=Accounting',
            'features' => [
                ['icon' => 'fa-coins', 'text' => 'Income Tracking'],
                ['icon' => 'fa-money-bill', 'text' => 'Expense Management'],
                ['icon' => 'fa-file-invoice', 'text' => 'Profit & Loss'],
                ['icon' => 'fa-wallet', 'text' => 'Cash Flow'],
                ['icon' => 'fa-chart-pie', 'text' => 'Financial Reports'],
            ]
        ],

        'hrm' => [
            'title' => 'HRM',
            'desc' => 'Employee management, attendance, payroll and leave tracking system.',
            'mockup' => 'hrm_system',
            'img' => 'https://via.placeholder.com/900x600?text=HRM',
            'features' => [
                ['icon' => 'fa-users', 'text' => 'Employee Records'],
                ['icon' => 'fa-clock', 'text' => 'Attendance System'],
                ['icon' => 'fa-calendar-check', 'text' => 'Leave Management'],
                ['icon' => 'fa-money-check', 'text' => 'Payroll System'],
                ['icon' => 'fa-id-card', 'text' => 'Staff Profiles'],
            ]
        ],

        'inventory' => [
            'title' => 'Inventory',
            'desc' => 'Real-time stock management with warehouse control and alerts.',
            'mockup' => 'inventory_system',
            'img' => 'https://via.placeholder.com/900x600?text=Inventory',
            'features' => [
                ['icon' => 'fa-boxes', 'text' => 'Stock Tracking'],
                ['icon' => 'fa-truck', 'text' => 'Warehouse Transfer'],
                ['icon' => 'fa-bell', 'text' => 'Low Stock Alerts'],
                ['icon' => 'fa-qrcode', 'text' => 'Barcode System'],
                ['icon' => 'fa-warehouse', 'text' => 'Multi Warehouse'],
            ]
        ],

        'analytics' => [
            'title' => 'Analytics',
            'desc' => 'Get real-time business insights with charts, reports and performance tracking.',
            'mockup' => 'analytics_dashboard',
            'img' => 'https://via.placeholder.com/900x600?text=Analytics',
            'features' => [
                ['icon' => 'fa-chart-line', 'text' => 'Sales Analytics'],
                ['icon' => 'fa-chart-pie', 'text' => 'Performance Reports'],
                ['icon' => 'fa-bolt', 'text' => 'Real-time Data'],
                ['icon' => 'fa-eye', 'text' => 'Product Insights'],
                ['icon' => 'fa-bullseye', 'text' => 'Business KPIs'],
            ]
        ],

    ];
@endphp
    <style>
        .hero-bg {
            background: radial-gradient(circle at 70% 30%, #1e1b4b 0%, #0a061e 60%);
        }

        .hero-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }

        /* --- ডট ডিজাইনের কাস্টম CSS --- */
        .hero-pagination .swiper-pagination-bullet {
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.3) !important;
            opacity: 1 !important;
            border-radius: 50%;
            transition: all 0.4s ease;
            cursor: pointer;
        }

        /* একটিভ ডটটি বড় (লম্বা) হবে */
        .hero-pagination .swiper-pagination-bullet-active {
            width: 40px !important;
            background: #ffffff !important;
            border-radius: 20px;
        }
    </style>
    <!-- HERO SECTION -->
    @if ($sliders->isNotEmpty())
        <section class="swiper heroSwiper relative overflow-hidden">
            <div class="swiper-wrapper">
                @foreach ($sliders as $slider)
                    <div
                        class="swiper-slide hero-bg min-h-screen flex items-center pt-28 pb-32 md:pt-20 relative overflow-hidden">
                        <div class="container mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

                            <!-- Left Side: Content -->
                            <div class="text-center lg:text-left order-2 lg:order-1">
                                <h1 class="text-white text-2xl md:text-3xl lg:text-4xl font-bold leading-tight mb-6">
                                    {{ $slider->title ?? 'আপনার ব্যবসার জন্য দরকারি সব কিছু এখন এক জায়গায়' }}
                                </h1>
                                <p
                                    class="text-gray-400 text-base md:text-lg lg:text-xl leading-relaxed mb-10 max-w-2xl mx-auto lg:mx-0">
                                    {{ $slider->description ?? 'আপনার ব্যবসার জন্য দরকারি সব কিছু এখন এক জায়গায়' }}
                                </p>

                                <!-- Action Buttons -->
                                <div
                                    class="flex flex-col sm:flex-row flex-wrap gap-4 items-center justify-center lg:justify-start mb-12">
                                    <a href="#"
                                        class="w-full sm:w-auto bg-[#34a487] hover:bg-[#4a38b8] text-white px-8 py-4 rounded-xl font-bold text-lg transition shadow-lg shadow-indigo-500/20 text-center">
                                        ফ্রি ট্রায়াল শুরু করুন
                                    </a>
                                    <a href="#"
                                        class="w-full sm:w-auto bg-white/10 hover:bg-white/20 text-white border border-white/20 px-8 py-4 rounded-xl font-bold text-lg flex items-center justify-center gap-2 transition text-center">
                                        ডেমো দেখুন <i class="fa-solid fa-play text-xs"></i>
                                    </a>
                                </div>

                                <!-- এখান থেকে hero-pagination ডিভটি সরিয়ে নিচে নেওয়া হয়েছে -->
                            </div>

                            <!-- Right Side: Graphics -->
                            <div class="relative flex justify-center items-center order-1 lg:order-2 py-20">
                                <div
                                    class="absolute w-[280px] h-[280px] sm:w-[350px] sm:h-[350px] md:w-[400px] md:h-[400px] border border-white/10 rounded-full">
                                </div>
                                <div
                                    class="absolute w-[200px] h-[200px] sm:w-[260px] sm:h-[260px] md:w-[320px] md:h-[320px] border border-white/10 rounded-full">
                                </div>
                                <div
                                    class="absolute w-[140px] h-[140px] sm:w-[180px] sm:h-[180px] md:w-[240px] md:h-[240px] border border-white/10 rounded-full">
                                </div>

                                <div class="relative z-10 p-6 md:p-10 rounded-[35px] shadow-2xl float-anim">
                                    <img src="{{ $slider->image_url ?? asset('./images/saas/hero.png') }}"
                                        class="w-16 h-16 md:w-[55vh] md:h-[40vh] object-contain" alt="Core Platform" />
                                </div>

                                <!-- CUSTOMER REVIEW -->
                                <div class="absolute -bottom-10 md:-bottom-10 flex flex-col items-center">
                                    <svg class="w-12 h-16 md:w-16 md:h-24 text-white/40 mb-2" viewBox="0 0 50 100"
                                        fill="none">
                                        <path d="M10 5C25 35 35 65 30 90" stroke="currentColor" stroke-width="4.5"
                                            stroke-linecap="round" />
                                        <path d="M22 82L30 92L40 84" stroke="currentColor" stroke-width="3.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#fde047] text-xl md:text-2xl">★</span>
                                        <span
                                            class="text-[#fde047] font-bold text-lg md:text-2xl">{{ number_format($avgRating, 1) }}</span>
                                        <a href="#reviews-section"
                                            class="text-gray-300 text-sm md:text-xl underline decoration-gray-500 underline-offset-8 hover:text-white transition font-medium">
                                            {{ $totalReviews }}+ কাস্টমার রিভিউ
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="container mx-auto px-6 relative">
                <div
                    class="hero-pagination absolute bottom-12 md:bottom-20 left-6 flex items-center justify-center lg:justify-start gap-2 z-50">
                </div>
            </div>


        </section>
    @endif
    <!-- LOGO SHOWCASE SECTION -->
    @if ($brands->isNotEmpty())
        <section class="bg-black py-16 border-t border-white/5">
            <div class="container mx-auto">
                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 items-center justify-items-center gap-y-12 gap-x-8 grayscale hover:opacity-100 transition-all duration-500">
                    @forelse($brands as $brand)
                        <div class="w-full flex justify-center text-white">
                            <a href="{{ $brand->link }}" target="_blank" class="block">
                                <img src="{{ $brand->logo_url ?? '' }}" alt="{{ $brand->name }}"
                                    class="h-8 md:h-10 lg:h-14 object-contain hover:grayscale-0 transition cursor-pointer" />
                            </a>
                        </div>
                    @empty
                        <div class="col-span-full text-gray-500 text-sm italic">No active brands available</div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif
    <!-- FEATURES SECTION -->
    @if ($features->where('placement', 1)->isNotEmpty())
        <section class="bg-white py-20">
            <div class="container mx-auto px-6 md:px-10">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <span
                        class="inline-block px-5 md:px-8 py-1.5 md:py-2.5 rounded-full border border-indigo-100 bg-indigo-50/50 text-indigo-600 font-semibold text-sm md:text-lg mb-6">
                        ফিচারসমূহ
                    </span>
                    <h2 class="text-2xl md:text-5xl font-bold text-gray-900 leading-tight">
                        আপনার ব্যবসার জন্য <br class="hidden md:block" />
                        দরকারি সব কিছু এখন এক জায়গায়
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 cursor-pointer">
                    {{-- লজিক: যেখানে placement == 1 (Feature) --}}
                    @foreach ($features->where('placement', 1) as $feature)
                        <div
                            class="bg-[#f9faff] p-8 md:p-12 rounded-[40px] border border-indigo-100 transition-all duration-300 group hover:shadow-xl hover:shadow-indigo-500/5">
                            <!-- Icon Area -->
                            <div
                                class="w-20 h-20 rounded-full border border-indigo-300 bg-white flex items-center justify-center mb-8">
                                <i class="{{ $feature->icon ?? 'fa-solid fa-file-lines' }} text-3xl text-[#34a487]"></i>
                            </div>

                            <!-- Content Area -->
                            <h3 class="text-2xl md:text-3xl font-bold text-[#34a487] mb-5">
                                {{ $feature->title ?? '' }}
                            </h3>
                            <p class="text-gray-700 text-lg leading-relaxed mb-12">
                                {{ $feature->subtitle ?? '' }}
                            </p>

                            <!-- Link Area -->
                            <a href="#"
                                class="inline-flex items-center gap-3 font-bold text-gray-900 group-hover:text-[#34a487] transition-colors text-lg">
                                বিস্তারিত জানুন
                                <i class="fa-solid fa-arrow-right text-sm"></i>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-16 text-center">
                    <button
                        class="bg-[#34a487] text-white px-10 py-4 rounded-xl font-bold hover:bg-[#4a38b8] transition shadow-lg shadow-indigo-100">
                        ফ্রি ট্রায়াল শুরু করুন
                    </button>
                </div>
            </div>
        </section>
    @endif
    <!-- INTEGRATION SECTION -->
    <section class="bg-[#f9faff] py-20 px-4 md:px-10 overflow-hidden">
        <div class="max-w-[1400px] mx-auto">
            <div class="text-center mb-16">
                <span
                    class="inline-block px-6 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#34a487] font-semibold text-[15px] mb-6">
                    ইন্টিগ্রেশন
                </span>
                <h2 class="text-2xl md:text-4xl font-extrabold text-gray-900 leading-tight max-w-4xl mx-auto">
                    আপনার পুরো ব্যবসা ট্র্যাক করুন, অটোমেট করুন
                    <br class="hidden md:block" />
                    এবং দ্রুত গ্রো করুন — একটি স্মার্ট সিস্টেম দিয়ে।
                </h2>
            </div>

            <!-- Integration Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Card 1: Payment Methods -->
                <div class="bg-white border border-indigo-100 rounded-2xl p-8 md:p-14">
                    <div class="flex flex-col items-center w-full">
                        <!-- Top Shopify Icon -->
                        <div
                            class="w-20 h-20 bg-[#34a487] rounded-full flex items-center justify-center shadow-lg shadow-indigo-200 z-10">
                            <i class="fa-brands fa-shopify text-white text-4xl"></i>
                        </div>

                        <!-- Line down to Middle Box -->
                        <div class="w-[2px] h-10 bg-[#34a487]"></div>

                        <!-- Middle Node Box -->
                        <div
                            class="px-8 py-3 border-2 border-[#34a487] rounded-2xl text-gray-900 font-bold text-base bg-white z-10">
                            পেমেন্ট মেথড
                        </div>

                        <!-- The Fork Connection Line -->
                        <div class="w-full relative flex flex-col items-center">
                            <!-- Vertical line from middle box to horizontal bar -->
                            <div class="w-[2px] h-10 bg-[#34a487]"></div>

                            <!-- Horizontal Bar: Exactly connects the centers of 1st and 3rd box -->
                            <div class="absolute bottom-0 w-[66.6%] h-[2px] bg-[#34a487]"></div>
                        </div>

                        <!-- 3 Vertical Lines down to logos -->
                        <div class="flex justify-between w-full px-[16.6%]">
                            <div class="w-[2px] h-10 bg-[#34a487]"></div>
                            <div class="w-[2px] h-10 bg-[#34a487]"></div>
                            <div class="w-[2px] h-10 bg-[#34a487]"></div>
                        </div>

                        <!-- Logo Row -->
                        <div class="grid grid-cols-3 gap-4 w-full">
                            <div
                                class="border-2 border-[#34a487] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/nagad.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="Nagad"
                                    onerror="
                        this.src =
                          'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Nagad_Logo.svg/1200px-Nagad_Logo.svg.png'
                      " />
                            </div>
                            <div
                                class="border-2 border-[#34a487] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/bkash.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="bKash" />
                            </div>
                            <div
                                class="border-2 border-[#34a487] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/sslcommerz.png') }}" class="h-5 md:h-7 object-contain"
                                    alt="SSL" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Courier Management -->
                <div class="bg-white border border-indigo-100 rounded-2xl p-8 md:p-14">
                    <div class="flex flex-col items-center w-full">
                        <!-- Top Shopify Icon -->
                        <div
                            class="w-20 h-20 bg-[#34a487] rounded-full flex items-center justify-center shadow-lg shadow-indigo-200 z-10">
                            <i class="fa-brands fa-shopify text-white text-4xl"></i>
                        </div>

                        <div class="w-[2px] h-10 bg-[#34a487]"></div>

                        <div
                            class="px-8 py-3 border-2 border-[#34a487] rounded-2xl text-gray-900 font-bold text-base bg-white z-10">
                            কুরিয়ার ম্যানেজমেন্ট
                        </div>

                        <div class="w-full relative flex flex-col items-center">
                            <div class="w-[2px] h-10 bg-[#34a487]"></div>
                            <div class="absolute bottom-0 w-[66.6%] h-[2px] bg-[#34a487]"></div>
                        </div>

                        <div class="flex justify-between w-full px-[16.6%]">
                            <div class="w-[2px] h-10 bg-[#34a487]"></div>
                            <div class="w-[2px] h-10 bg-[#34a487]"></div>
                            <div class="w-[2px] h-10 bg-[#34a487]"></div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 w-full">
                            <div
                                class="border-2 border-[#34a487] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/steadfast.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="Steadfast" />
                            </div>
                            <div
                                class="border-2 border-[#34a487] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/pathao.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="Pathao" />
                            </div>
                            <div
                                class="border-2 border-[#34a487] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <span class="font-black italic text-[#FF9900] text-sm md:text-lg">Carry<span
                                        class="text-[#000]">Bee</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- DEMO & TEMPLATE SECTION -->
    @if ($demos->isNotEmpty())
        <section class="bg-white py-20 px-4 md:px-10" id="demo-section">
            <div class="max-w-[1400px] mx-auto">
                <!-- Section Header -->
                <div class="text-center mb-12">
                    <span
                        class="inline-block px-5 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#34a487] font-semibold text-sm md:text-lg mb-6">
                        ডেমো & টেমপ্লেট
                    </span>
                    <h2 class="text-2xl md:text-4xl font-black text-gray-900 leading-tight mb-10">
                        এক প্ল্যাটফর্মে পুরো সিস্টেম লাইভ এক্সপেরিয়েন্স নিন
                    </h2>

                    <!-- Tabs Container (ক্লিকেবল ও হোভার ইফেক্ট ফিক্সড) -->
                    <div class="inline-flex p-1.5 bg-white border-2 border-indigo-100 rounded-2xl" id="tab-container">
                        <button onclick="filterDemos(1, this)"
                            class="tab-btn bg-[#34a487] text-white px-6 md:px-10 py-3 rounded-xl font-bold text-sm md:text-base transition-all">
                            ল্যান্ডিং পেজ টেমপ্লেট
                        </button>
                        <button onclick="filterDemos(2, this)"
                            class="tab-btn text-gray-900 px-6 md:px-10 py-3 rounded-xl font-bold text-sm md:text-base hover:bg-indigo-50 hover:text-[#34a487] transition-all">
                            ই-কমার্স টেমপ্লেট
                        </button>
                    </div>
                </div>

                <!-- Templates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="demo-grid">
                    @forelse($demos as $demo)
                        <div class="demo-card group border border-indigo-100 rounded-xl overflow-hidden bg-white"
                            data-type="{{ $demo->type }}">
                            <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                                <img src="{{ $demo->image_url ?? asset('images/saas/live1.png') }}"
                                    class="w-full h-full object-cover object-top rounded-xl border border-gray-200 transition-transform duration-700 group-hover:scale-105"
                                    alt="{{ $demo->title }}" />
                            </div>
                            <div class="py-6 text-center border-t border-gray-100">
                                <a href="{{ $demo->link ?? '#' }}" target="_blank"
                                    class="text-xl md:text-2xl font-bold text-gray-900 underline hover:text-[#34a487] hover:decoration-[#34a487]">
                                    Live Preview
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-10 text-gray-500">
                            কোনো ডেমো টেমপ্লেট পাওয়া যায়নি।
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif
    @php
        $badges = [
            'ফিটনেস',
            'গিফট আইটেম',
            'অর্গানিক ফুড',
            'গ্যাজেট',
            'ইলেক্ট্রনিক্স',
            'প্রসাধনী',
            'হোম ডেকোর',
            'ইসলামিক',
        ];

        $rows = [
            ['class' => 'animate-scroll-left', 'data' => $badges, 'style' => ''],
            ['class' => 'animate-scroll-right', 'data' => collect($badges)->reverse()->all(), 'style' => ''],
            [
                'class' => 'animate-scroll-left',
                'data' => collect($badges)->shuffle()->all(),
                'style' => 'animation-duration: 35s',
            ],
        ];
    @endphp

    <!-- INFINITY LOOP SECTION -->
    <section class="bg-white py-10 overflow-hidden scroll-container">
        <div class="space-y-6">

            @foreach ($rows as $row)
                <div class="{{ $row['class'] }} flex gap-4" style="{{ $row['style'] }}">

                    @foreach ([1, 2] as $repeat)
                        <div class="flex gap-4">
                            @foreach ($row['data'] as $item)
                                <span class="bg-[#34a487] text-white px-8 py-3 rounded-2xl font-bold whitespace-nowrap">
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    @endforeach

                </div>
            @endforeach

        </div>
    </section>
    <!-- SUCCESS SECTION (Dark Theme) -->
    <section class="bg-[#020410] py-24 px-6 md:px-10 relative overflow-hidden hook-2">
        <style>
            .hook-2::before {
              content: "";
  position: absolute;
  inset: 0;
  background-image: linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
  background-size: 40px 40px;
  pointer-events: none;
            }
        </style>
        <div
            class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#34a487]/10 blur-[120px] rounded-full pointer-events-none">
        </div>

        <div class="container mx-auto">
            <!-- TOP PART: Header and Button -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-10 mb-20">
                <div class="max-w-2xl">
                    <!-- Badge Tag -->
                    <span
                        class="inline-block px-5 py-2 rounded-full border border-white/40 text-gray-300 text-sm md:text-lg font-medium mb-6">
                        Your Business Growth Partner
                    </span>
                    <!-- Heading -->
                    <h2 class="text-white text-4xl md:text-5xl font-bold leading-tight mb-6">
                       The key driver of your online business success
                    </h2>
                    <!-- Description -->
                    <p class="text-gray-300 text-lg leading-relaxed max-w-2xl">
                     Our advanced integrations make your business operations easier, smarter, and significantly more powerful.
                    </p>
                </div>

                <!-- CTA Button -->
                <div class="flex-shrink-0">
                    <a href="https://app.dorja.io/register"
                        class="inline-block bg-[#34a487] hover:bg-[#4a38b8] text-white px-6 py-4 rounded-2xl font-bold text-md transition shadow-lg shadow-indigo-500/20">
                       Start Free Trial
                    </a>
                </div>
            </div>

            <!-- BOTTOM PART: 3 Columns Features -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-16 md:gap-12 lg:gap-20">
                <!-- Feature 1 -->
                <div class="group">
                    <div class="mb-6">
                        <i class="fa-solid fa-table-cells-large text-[#26ffc7] text-4xl"></i>
                    </div>
                    <h3 class="text-[#26ffc7] text-2xl font-bold mb-5">
                        Affordable Pricing
                    </h3>
                    <p class="text-gray-400 text-[17px] leading-relaxed mb-8">
                     Select a plan that fits your business size and goals. dorja.io offers flexible packages for startups to enterprises that grow with you.
                    </p>
                    <a href="#"
                        class="inline-flex items-center gap-3 text-white font-bold text-lg hover:text-[#26ffc7] transition group">
                        Explore Our Pricing
                        <i class="fa-solid fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>

                <!-- Feature 2 -->
                <div class="group">
                    <div class="mb-6">
                        <i class="fa-solid fa-layer-group text-[#26ffc7] text-4xl"></i>
                    </div>
                    <h3 class="text-[#26ffc7] text-2xl font-bold mb-5">
                       Explore Features
                    </h3>
                    <p class="text-gray-400 text-[17px] leading-relaxed mb-8">
                Discover a complete suite of business tools designed to automate operations, improve efficiency, and help you make faster data-driven decisions.
                    </p>
                    <a href="#"
                        class="inline-flex items-center gap-3 text-white font-bold text-lg hover:text-[#26ffc7] transition group">
                        Explore Features
                        <i class="fa-solid fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>

                <!-- Feature 3 -->
                <div class="group">
                    <div class="mb-6">
                        <i class="fa-solid fa-bolt text-[#26ffc7] text-4xl"></i>
                    </div>
                    <h3 class="text-[#26ffc7] text-2xl font-bold mb-5">
                       Start Your Journey Today
                    </h3>
                    <p class="text-gray-400 text-[17px] leading-relaxed mb-8">
                        Join thousands of businesses already using dorja.io. Start your journey today and transform the way you manage and grow your business.
                    </p>
                    <a href="https://app.dorja.io/register"
                        class="inline-flex items-center gap-3 text-white font-bold text-lg hover:text-[#26ffc7] transition group">
                        Get Started
                        <i class="fa-solid fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- SOLUTION SECTION -->
  <section class="bg-white py-20 px-4 md:px-10">
    <div class="container mx-auto">

        <!-- Header -->
        <div class="text-center mb-16">
            <span class="inline-block px-5 py-1.5 rounded-full border border-indigo-100 bg-indigo-50 text-[#34a487] font-semibold text-sm md:text-lg mb-6">
                All-in-One Solution
            </span>

            <h2 class="text-2xl md:text-4xl font-extrabold text-gray-900 mb-10">
                From Operations to Growth — Everything in One System
            </h2>

            <!-- Tabs -->
            <div class="inline-flex p-1.5 bg-indigo-50/30 border-2 border-indigo-100 gap-2 rounded-2xl w-full "
                id="solution-tabs">

                @foreach($solutions as $key => $sol)
                    <button
                        onclick="switchSolution('{{ $key }}', this)"
                        class="sol-tab-btn flex-1 px-2 py-2.5 rounded-xl font-bold text-sm md:text-base transition-all
                        {{ $loop->first ? 'bg-[#34a487] text-white' : 'bg-[#34a48730] text-gray-900 hover:bg-white' }}">
                        {{ $sol['title'] }}
                    </button>
                @endforeach

            </div>
        </div>

        <!-- Content Card -->
        <div class="bg-white border border-indigo-100 rounded-2xl p-8 md:p-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

                <!-- Left Content -->
                <div>
                    <h3 id="sol-title" class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-6"></h3>

                    <p id="sol-desc" class="text-gray-600 text-lg leading-relaxed mb-10 max-w-md"></p>

                    <!-- Features -->
                    <div id="sol-features" class="space-y-5 mb-12"></div>

                    <a href="#"
                        class="inline-flex items-center gap-3 bg-[#34a487] hover:bg-[#2c8a70] text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all shadow-lg">
                        Explore More
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>

                <!-- Right Image -->
                <div>
                    <div class="relative bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden">
                        <div class="bg-gray-50 border-b px-4 py-3 text-xs text-gray-400 font-mono">
                            <span id="sol-mockup"></span>
                        </div>

                        <div class="aspect-video bg-gray-100">
                            <img id="sol-img"
                                src="https://via.placeholder.com/900x600?text=ERP+Dashboard"
                                class="w-full h-full object-cover object-top"
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

    <!-- WHY CHOOSE US SECTION (Placement = 2) -->
    @if ($features->where('placement', 2)->isNotEmpty())
        <section class="bg-[#020410] py-24 px-6 md:px-10 relative overflow-hidden">
            <div class="container mx-auto">
                <div class="text-center mb-20">
                    <h2 class="text-white text-3xl md:text-4xl font-extrabold">
                        কেন আমাদের সিস্টেম বেছে নেবেন?
                    </h2>
                </div>

                <div class="flex flex-col gap-10">
                    @foreach ($features->where('placement', 2) as $index => $benefit)
                        <div
                            class="bg-white rounded-2xl p-8 md:p-14 flex flex-col {{ $loop->even ? 'lg:flex-row-reverse' : 'lg:flex-row' }} items-center gap-12 lg:gap-20">
                            <div class="w-full lg:w-1/2 text-center lg:text-left">
                                <h3 class="text-[#34a487] text-3xl md:text-4xl font-extrabold mb-6">
                                    {{ $benefit->title }}
                                </h3>
                                <p class="text-gray-800 text-lg leading-relaxed font-semibold max-w-xl">
                                    {{ $benefit->description }}
                                </p>
                            </div>
                            <div class="w-full lg:w-1/2">
                                <img src="{{ $benefit->image_url ?? asset('images/saas/choose.jpg') }}"
                                    class="w-full h-auto rounded-3xl shadow-lg" alt="{{ $benefit->title }}" />
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-28 text-center">
                    <a href="#"
                        class="inline-block bg-[#34a487] hover:bg-[#4a38b8] text-white px-10 py-4 rounded-xl font-bold text-lg transition shadow-lg shadow-indigo-500/20">
                        ফ্রি ট্রায়াল শুরু করুন
                    </a>
                </div>
            </div>
        </section>
    @endif
    <!-- BLOG & INSIGHTS SECTION -->
    @if ($blogs->isNotEmpty())
        <section class="bg-white py-20 px-6 md:px-10">
            <div class="container mx-auto">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-12 gap-6">
                    <h2 class="text-2xl md:text-4xl font-black text-gray-900">Our Blogs</h2>
                    <a href=""
                        class="bg-[#34a487] text-white px-8 py-3 rounded-xl font-bold text-lg hover:bg-[#4a38b8] transition shadow-lg shadow-indigo-100">
                        View All <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($blogs as $blog)
                        <div
                            class="bg-white border border-gray-100 rounded-2xl overflow-hidden group hover:shadow-xl transition-all duration-300 flex flex-col h-full">

                            <a href="" class="group block overflow-hidden rounded-xl">
                                <div class="aspect-[16/10] bg-[#eef2ff] relative overflow-hidden">
                                    <img src="{{ $blog->thumbnail_url ? asset($blog->thumbnail_url) : asset('images/saas/live1.png') }}"
                                        alt="{{ $blog->title }}"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                </div>
                            </a>

                            <div class="p-6 md:p-8 flex flex-col flex-grow">
                                <div class="flex justify-between items-center mb-5">
                                    <span
                                        class="bg-indigo-50 text-[#34a487] px-4 py-1 rounded-full text-xs font-bold border border-indigo-100">
                                        {{ $blog->company->shop_name ?? 'Admin' }}
                                    </span>
                                    <div class="flex items-center gap-2 text-gray-400 text-sm font-bold">
                                        <i class="fa-regular fa-clock"></i>
                                        <span>{{ $blog->reading_time ?? '' }} minutes</span>
                                    </div>
                                </div>

                                <h3
                                    class="text-xl md:text-2xl font-semibold text-gray-900 mb-4 leading-tight line-clamp-2">
                                    {{ $blog->title ?? '' }}
                                </h3>

                                <p class="text-gray-500 text-sm mb-8 line-clamp-3">
                                    {{ $blog->short ?? '' }}
                                </p>

                                <div class="mt-auto pt-5 border-t border-gray-50">
                                    <a href=""
                                        class="inline-flex items-center gap-2 text-[#34a487] font-bold text-lg group-hover:gap-3 transition-all">
                                        Read More <i class="fa-solid fa-arrow-right text-sm"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    <!-- Review SECTION -->
    @if ($allReviews->isNotEmpty())
        <section class="bg-[#f9faff] py-20 px-6 md:px-10" id="reviews-section">
            <div class="container mx-auto">
                <div class="text-center mb-16">
                    <span
                        class="inline-block px-5 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#34a487] font-semibold text-sm md:text-lg mb-6">
                        কাস্টমার রিভিউ
                    </span>
                    <h2 class="text-3xl md:text-5xl font-black text-gray-900">
                        আমাদের গ্রাহকদের মতামত
                    </h2>
                </div>

                <!-- Masonry Grid -->
                <div class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6" id="review-container">
                    @foreach ($allReviews as $index => $review)
                        {{-- শুরুতে ৬টার বেশি হলে 'hidden' ক্লাস পাবে --}}
                        <div
                            class="review-card break-inside-avoid bg-white border border-indigo-100 p-8 rounded-2xl hover:shadow-md transition {{ $index >= 6 ? 'hidden' : '' }}">
                            <div class="flex justify-between items-start {{ $review->review ? 'mb-6' : '' }}">
                                <div>
                                    <h4 class="text-[#34a487] font-bold text-lg">
                                        @ {{ $review->name }}
                                    </h4>
                                    <p class="text-gray-500 text-xs">{{ $review->designation }}</p>
                                </div>
                                <div class="flex items-center gap-1 text-gray-900 font-bold">
                                    <i class="fa-solid fa-star text-[#fde047]"></i>
                                    <span>{{ number_format($review->rating, 1) }}</span>
                                </div>
                            </div>

                            @if ($review->review)
                                <p class="text-gray-700 leading-relaxed text-[15px]">
                                    “{{ $review->review }}”
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- যদি রিভিউ ৬টার বেশি হয় তবেই বাটন দেখাবে --}}
                @if ($allReviews->count() > 6)
                    <div class="mt-16 text-center">
                        <button id="load-more-reviews"
                            class="inline-block bg-[#34a487] text-white px-10 py-3 rounded-xl font-bold hover:bg-[#4a38b8] transition shadow-lg shadow-indigo-100">
                            আরও দেখুন
                        </button>
                    </div>
                @endif
            </div>
        </section>
    @endif
    <section class="bg-white py-20 px-6 md:px-10" id="faq-section">
    <div class="container mx-auto">

        <!-- Header -->
        <div class="text-center mb-10">
            <span
                class="inline-block px-5 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#34a487] font-semibold text-sm md:text-lg mb-6">
               Frequently Asked Questions
            </span>

            <h2 class="text-3xl md:text-5xl font-black text-gray-900">
                dorja.io FAQ
            </h2>

            <!-- Search Box -->
            <div class="mt-8 max-w-xl mx-auto">
                <input
                    type="text"
                    id="faqSearch"
                    placeholder="Search FAQ..."
                    class="w-full px-5 py-3 border border-indigo-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400"
                />
            </div>
        </div>

        @php
            $faqs = App\Models\KnowledgeBase::where('status', 1)
                ->where('company_id', null)
                ->orderBy('id', 'asc')
                ->get();
        @endphp

        <!-- FAQ List -->
        <div class=" space-y-4 h-[400px] overflow-y-auto" id="faq-container">

            @foreach ($faqs as $faq)
                <div class="faq-item bg-[#f9faff] border border-indigo-100 rounded-2xl ">

                    <!-- Question -->
                    <button
                        class="p-6 w-full flex justify-between items-center text-left font-bold text-gray-900 text-lg faq-toggle">
                        <span class="faq-question">{{ $faq->title }}</span>

                        <i class="fa-solid fa-chevron-down transition-transform duration-300"></i>
                    </button>

                    <!-- Answer -->
                    <div class="px-6 faq-content mt-[-2px] text-gray-600 leading-relaxed hidden">
                        {!! $faq->content !!}
                    </div>

                </div>
            @endforeach

        </div>
    </div>
</section>

@endsection
@push('scripts')

<!-- Accordion + Search Script -->
<script>
    // Accordion
    document.querySelectorAll('.faq-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const content = btn.nextElementSibling;
            const icon = btn.querySelector('i');

            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        });
    });

    // Search Filter
    document.getElementById('faqSearch').addEventListener('input', function () {
        let value = this.value.toLowerCase();
        let items = document.querySelectorAll('.faq-item');

        items.forEach(item => {
            let text = item.querySelector('.faq-question').innerText.toLowerCase();

            if (text.includes(value)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
</script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('.heroSwiper')) {
                new Swiper('.heroSwiper', {
                    loop: true,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                    speed: 1000,
                    pagination: {
                        el: '.hero-pagination',
                        clickable: true, // ডটস ক্লিক করলে স্লাইড চেঞ্জ হবে
                    },
                });
            }
        });

       const solutions = @json($solutions);

function switchSolution(key, btn) {
    const data = solutions[key];

    document.getElementById('sol-title').innerText = data.title;
    document.getElementById('sol-desc').innerText = data.desc;
    document.getElementById('sol-img').src = data.img;
    document.getElementById('sol-mockup').innerText = data.mockup;

    let featureHtml = '';
    data.features.forEach(f => {
        featureHtml += `
            <div class="flex items-center gap-4">
                <div class="w-8 h-8 bg-[#34a487] rounded-lg flex items-center justify-center text-white">
                    <i class="fa-solid ${f.icon} text-sm"></i>
                </div>
                <span class="font-bold text-gray-900 text-lg">${f.text}</span>
            </div>
        `;
    });

    document.getElementById('sol-features').innerHTML = featureHtml;

    document.querySelectorAll('.sol-tab-btn').forEach(b => {
        b.classList.remove('bg-[#34a487]', 'text-white');
        b.classList.add('bg-[#34a48730]', 'text-gray-900');
    });

    btn.classList.add('bg-[#34a487]', 'text-white');
    btn.classList.remove('bg-[#34a48730]', 'text-gray-900');
}

// default load
document.addEventListener("DOMContentLoaded", function () {
    document.querySelector(".sol-tab-btn").click();
});
    </script>
    <script>
        //review
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreBtn = document.getElementById('load-more-reviews');
            const itemsToShow = 6; // প্রতি ক্লিকে কয়টি করে নতুন রিভিউ দেখাবে

            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    // বর্তমানে লুকানো আছে এমন সব কার্ড খুঁজে বের করা
                    const hiddenCards = document.querySelectorAll('.review-card.hidden');

                    // পরবর্তী ৬টি কার্ড থেকে hidden ক্লাস সরিয়ে দেওয়া
                    for (let i = 0; i < itemsToShow && i < hiddenCards.length; i++) {
                        hiddenCards[i].classList.remove('hidden');
                    }

                    // যদি আর কোনো লুকানো কার্ড না থাকে, তবে বাটনটি হাইড করে দেওয়া
                    if (document.querySelectorAll('.review-card.hidden').length === 0) {
                        loadMoreBtn.style.display = 'none';
                    }
                });
            }
        });
        //demo
        function filterDemos(type, btn) {
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(b => {
                b.classList.remove('bg-[#34a487]', 'text-white');
                b.classList.add('text-gray-900', 'hover:bg-indigo-50', 'hover:text-[#34a487]');
            });

            btn.classList.add('bg-[#34a487]', 'text-white');
            btn.classList.remove('text-gray-900', 'hover:bg-indigo-50', 'hover:text-[#34a487]');

            const cards = document.querySelectorAll('.demo-card');
            cards.forEach(card => {
                if (card.getAttribute('data-type') == type) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        document.addEventListener("DOMContentLoaded", function() {
            const firstTab = document.querySelector('.tab-btn');
            if (firstTab) filterDemos(1, firstTab);
        });
    </script>
@endpush
