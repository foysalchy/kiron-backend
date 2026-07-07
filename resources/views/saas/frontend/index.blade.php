@extends('saas.layouts.layout')
@section('meta')
    @php
        $pageData = \App\Services\Saas\SystemPageService::get(\App\Enums\SystemPageType::HOME, null);
    @endphp

    @include('components.meta-info.saas-meta', [
        'setup' => $setup,
        'type' => 'WebPage',
        'title' => $pageData->meta_title ?? ($setup->title ?? 'Home'),
        'description' =>
            $pageData->meta_description ??
            ($setup->description ??
                'Dorja is a powerful all-in-one business management platform that helps you streamline your operations, automate tasks, and grow your business with ease.'),
        'keywords' => $pageData?->meta_keywords
            ? (is_array($pageData->meta_keywords)
                ? implode(',', $pageData->meta_keywords)
                : $pageData->meta_keywords)
            : $setup?->tags ?? 'business management, automation, growth, all-in-one platform',

        'image' =>
            $setup?->meta_image ?? null
                ? asset('storage/' . $setup->meta_image)
                : asset('storage/' . ($setup?->logo ?? '')),
        'canonical' => url()->current(),
        'breadcrumb' => [
            [
                'name' => 'Home',
                'url' => url('/'),
            ],
        ],
    ])
@endsection
@push('styles')
    <style>
        .hero-bg {
            background: radial-gradient(circle at 70% 30%, #00555c 0%, #030f0c 60%);
        }

        .pin-wrap {
            display: flex;
            gap: 24px;
            /* width:max-content; */
        }
        .float-anim {
            animation: heroFloat 4s ease-in-out infinite;
            transform-origin: center;
        }

        @keyframes heroFloat {
            0% {
                transform: translateY(0px) scale(0.9);
            }

            50% {
                transform: translateY(-12px) scale(1);
            }

            100% {
                transform: translateY(0px) scale(0.9);
            }
        }

        .animated-border {
            position: relative;

            color: #fff;
            border: 5px solid transparent;
            border-radius: 12px;
            background:
                linear-gradient(#ffff, rgb(255, 255, 255)) padding-box,
                linear-gradient(90deg,
                    #00555c,
                    #fff,
                    #fff,
                    #fff,
                    #fff) border-box;
            background-size: 100% 100%, 300% 100%;
            animation: borderAnimation 5s linear infinite;
        }

        @keyframes borderAnimation {
            0% {
                background-position: 0 0, 0% 50%;
            }

            100% {
                background-position: 0 0, 300% 50%;
            }
        }

        /* .animated-text {
            background: linear-gradient(90deg,
                    #00555c,
                    #4fd1c5,
                    #7c3aed,
                    #00555c);
            background-size: 300% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: textGradient 4s linear infinite;
        } */

        @keyframes textGradient {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 300% 50%;
            }
        }

        .integration-card {
            flex: 0 0 calc(50% - 12px);
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

       .hero-pagination .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
        background: rgba(255, 255, 255, 0.3) !important;
        opacity: 1 !important;
        border-radius: 50%;
        transition: all 0.4s ease;
        cursor: pointer;
        margin: 0 8px !important;
        position: relative;
    }

    .hero-pagination .swiper-pagination-bullet-active {
        width: 45px !important;
        background: #ffffff !important;
        border-radius: 20px;
    }

    .hero-pagination .swiper-pagination-bullet::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 48px; /* লাইটহাউসের রিকোয়ারমেন্ট */
        height: 48px;
        background: transparent;
        z-index: 1;
    }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-4 {
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        @media (max-width: 640px) {
            .heroSwiper {
                height: 100vh !important;
                min-height: 800px !important;
            }

            .hero-pagination {
                bottom: 15% !important;
                justify-content: center !important;
            }
        }

        .hook-2::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }
    </style>
    <style>
        @keyframes marqueeLeft {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        @keyframes marqueeRight {
            0% {
                transform: translateX(-50%);
            }

            100% {
                transform: translateX(0);
            }
        }

        .animate-marquee-left {
            display: flex;
            width: max-content;
            animation: marqueeLeft var(--duration, 30s) linear infinite;
        }

        .animate-marquee-right {
            display: flex;
            width: max-content;
            animation: marqueeRight var(--duration, 30s) linear infinite;
        }

        .scroll-container:hover .animate-marquee-left,
        .scroll-container:hover .animate-marquee-right {
            animation-play-state: paused;
        }

        .integrate-bg {
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.075) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.075) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .flow-line {
            stroke: #e2e8f0;
            stroke-width: 1.8;
            fill: none;
            opacity: 0.4;
        }

        .flow-pulse {
            stroke: white;
            stroke-width: 2.2;
            fill: none;
            stroke-dasharray: 8 200;
            stroke-dashoffset: 0;
            animation: pulse-flow 3.5s linear infinite;
        }

        @keyframes pulse-flow {
            to {
                stroke-dashoffset: -208;
            }
        }

        .ring-pulse {
            animation: ring-grow 2.8s ease-out infinite;
            transform-origin: center;
        }

        @keyframes ring-grow {
            0% {
                r: 35;
                opacity: 0.6;
            }

            100% {
                r: 65;
                opacity: 0;
            }
        }

        .node-circle {
            position: absolute;
            transform: translate(-50%, -50%);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1.4px solid #2A303B;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            transition: all .4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .node-circle:hover {
            border-color: #00555c;
            box-shadow: 0 0 0 6px rgba(52, 164, 135, 0.1);
            transform: translate(-50%, -50%) scale(1.15);
        }

        .node-circle.small {
            width: 90px;
            height: 52px;
        }

        .node-circle img {
            width: 70%;
            height: 70%;
            object-fit: contain;
        }

        .center-node {
            position: absolute;
            left: 48.9%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 120px;
            height: 60px;
            border-radius: 5px;
            background: linear-gradient(145deg, #00555c, #2c8a71);
            border: 2px solid #ffffff;
            box-shadow: 0 10px 25px rgba(52, 164, 135, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            z-index: 10;
            animation: node-breathe 3s ease-in-out infinite;
        }

        @keyframes node-breathe {

            0%,
            100% {
                transform: translate(-50%, -50%) scale(1);
            }

            50% {
                transform: translate(-50%, -50%) scale(1.06);
            }
        }

        @media (max-width: 768px) {
            .node-circle.small {
                width: 42px;
                height: 42px;
            }

            .center-node {
                width: 75px;
                height: 75px;
            }
        }
    </style>
@endpush
@section('content')
    @php
        $solutions = [
            'ecommerce' => [
                'title' => 'E-Commerce',
                'desc' =>
                    'Power your online store with automated order processing, inventory sync, and seamless customer experience.',
                'mockup' => 'ecommerce_dashboard_preview',
                'img' => '',
                'features' => [
                    ['icon' => 'fa-globe', 'text' => 'Website Integration'],
                    ['icon' => 'fa-cart-shopping', 'text' => 'Order Management'],
                    ['icon' => 'fa-box', 'text' => 'Product Catalog'],
                    ['icon' => 'fa-truck', 'text' => 'Shipping Integration'],
                    ['icon' => 'fa-credit-card', 'text' => 'Online Payments'],
                ],
            ],

            'pos' => [
                'title' => 'POS System',
                'desc' => 'Fast and reliable POS system for retail and wholesale billing with barcode support.',
                'mockup' => 'pos_system_preview',
                'img' => '',
                'features' => [
                    ['icon' => 'fa-barcode', 'text' => 'Barcode Scanning'],
                    ['icon' => 'fa-receipt', 'text' => 'Instant Invoice'],
                    ['icon' => 'fa-cash-register', 'text' => 'Fast Billing'],
                    ['icon' => 'fa-credit-card', 'text' => 'Multiple Payments'],
                    ['icon' => 'fa-rotate-left', 'text' => 'Sales Return'],
                ],
            ],

            'erp' => [
                'title' => 'ERP Core',
                'desc' => 'Manage your entire business operations including sales, purchase, inventory and warehouse.',
                'mockup' => 'erp_core_system',
                'img' => '',
                'features' => [
                    ['icon' => 'fa-layer-group', 'text' => 'Centralized System'],
                    ['icon' => 'fa-sitemap', 'text' => 'Multi Module Control'],
                    ['icon' => 'fa-boxes-stacked', 'text' => 'Inventory Management'],
                    ['icon' => 'fa-cart-plus', 'text' => 'Sales & Purchase'],
                    ['icon' => 'fa-warehouse', 'text' => 'Warehouse Control'],
                ],
            ],

            'crm' => [
                'title' => 'CRM',
                'desc' => 'Manage leads, customers, and improve sales conversion with smart tracking.',
                'mockup' => 'crm_dashboard',
                'img' => '',
                'features' => [
                    ['icon' => 'fa-user', 'text' => 'Lead Management'],
                    ['icon' => 'fa-users', 'text' => 'Customer Profiles'],
                    ['icon' => 'fa-bullseye', 'text' => 'Sales Tracking'],
                    ['icon' => 'fa-bell', 'text' => 'Follow-up Reminders'],
                    ['icon' => 'fa-chart-line', 'text' => 'Conversion Analytics'],
                ],
            ],

            'accounting' => [
                'title' => 'Accounting',
                'desc' => 'Complete financial management with profit, loss, cash flow and reporting tools.',
                'mockup' => 'accounting_system',
                'img' => '',
                'features' => [
                    ['icon' => 'fa-coins', 'text' => 'Income Tracking'],
                    ['icon' => 'fa-money-bill', 'text' => 'Expense Management'],
                    ['icon' => 'fa-file-invoice', 'text' => 'Profit & Loss'],
                    ['icon' => 'fa-wallet', 'text' => 'Cash Flow'],
                    ['icon' => 'fa-chart-pie', 'text' => 'Financial Reports'],
                ],
            ],

            'hrm' => [
                'title' => 'HRM',
                'desc' => 'Employee management, attendance, payroll and leave tracking system.',
                'mockup' => 'hrm_system',
                'img' => '',
                'features' => [
                    ['icon' => 'fa-users', 'text' => 'Employee Records'],
                    ['icon' => 'fa-clock', 'text' => 'Attendance System'],
                    ['icon' => 'fa-calendar-check', 'text' => 'Leave Management'],
                    ['icon' => 'fa-money-check', 'text' => 'Payroll System'],
                    ['icon' => 'fa-id-card', 'text' => 'Staff Profiles'],
                ],
            ],

            'inventory' => [
                'title' => 'Inventory',
                'desc' => 'Real-time stock management with warehouse control and alerts.',
                'mockup' => 'inventory_system',
                'img' => '',
                'features' => [
                    ['icon' => 'fa-boxes', 'text' => 'Stock Tracking'],
                    ['icon' => 'fa-truck', 'text' => 'Warehouse Transfer'],
                    ['icon' => 'fa-bell', 'text' => 'Low Stock Alerts'],
                    ['icon' => 'fa-qrcode', 'text' => 'Barcode System'],
                    ['icon' => 'fa-warehouse', 'text' => 'Multi Warehouse'],
                ],
            ],

            'analytics' => [
                'title' => 'Analytics',
                'desc' => 'Get real-time business insights with charts, reports and performance tracking.',
                'mockup' => 'analytics_dashboard',
                'img' => '',
                'features' => [
                    ['icon' => 'fa-chart-line', 'text' => 'Sales Analytics'],
                    ['icon' => 'fa-chart-pie', 'text' => 'Performance Reports'],
                    ['icon' => 'fa-bolt', 'text' => 'Real-time Data'],
                    ['icon' => 'fa-eye', 'text' => 'Product Insights'],
                    ['icon' => 'fa-bullseye', 'text' => 'Business KPIs'],
                ],
            ],
        ];
    @endphp

    <!-- HERO SECTION -->
    @if ($sliders->isNotEmpty())
        <section class="swiper heroSwiper hero-bg  relative overflow-hidden h-[90vh] min-h-[500px]">
            <div class="swiper-wrapper">
                @foreach ($sliders as $slider)
                    <div class="swiper-slide min-h-screen flex items-center pt-12 pb-32 lg:pt-20 relative overflow-hidden">
                        <div
                            class="container mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 lg:gap-12 gap-4 lg:gap-16 items-center">

                            <div class="text-center lg:text-left order-2 lg:order-1 lg:pt-14 pt-5 md:pt-0">

                                <h1
                                    class="text-white text-2xl md:text-3xl lg:text-4xl font-bold leading-tight mb-4 line-clamp-2">
                                    {{ $slider->title }}
                                </h1>

                                <p
                                    class="text-gray-400 text-sm md:text-lg lg:text-xl leading-relaxed mb-10 max-w-2xl mx-auto lg:mx-0    ">
                                    {{ $slider->description }}
                                </p>

                                <!-- Action Buttons -->
                                <div
                                    class="flex flex-col sm:flex-row flex-wrap gap-4 items-center justify-center lg:justify-start mb-12">
                                    <a href="https://app.dorja.io/register"
                                        class="w-full sm:w-auto bg-[#00555c] hover:bg-[#078e9a] text-white px-8 py-4 rounded-xl font-bold text-lg transition shadow-lg shadow-indigo-500/20 text-center">
                                        Start Free Trial
                                    </a>
                                    <a href="#"
                                        class="w-full sm:w-auto bg-white/10 hover:bg-white/20 text-white border border-white/20 px-8 py-4 rounded-xl font-bold text-lg flex items-center justify-center gap-2 transition text-center">
                                        Watch Demo <i class="fa-solid fa-play text-xs"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Right Side: Graphics -->
                            <div class="relative flex justify-center items-center order-1 lg:order-2 ">
                                <div
                                    class="absolute w-[280px] h-[280px] sm:w-[350px] sm:h-[350px] md:w-[400px] md:h-[400px] border border-white/10 rounded-full">
                                </div>
                                <div
                                    class="absolute w-[200px] h-[200px] sm:w-[260px] sm:h-[260px] md:w-[320px] md:h-[320px] border border-white/10 rounded-full">
                                </div>
                                <div
                                    class="absolute w-[140px] h-[140px] sm:w-[180px] sm:h-[180px] md:w-[240px] md:h-[240px] border border-white/10 rounded-full">
                                </div>

                                <div class="relative z-10 px-6 md:px-10   float-anim">
                                    <img src="{{ $slider->image_url ?? asset('./images/saas/hero.png') }}" 
                                        class=" lg:w-[90%] lg:max-w-[90%] w-[60%] max-w-[60%] block m-auto"
                                        alt="Core Platform" />
                                </div>

                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="absolute inset-0 pointer-events-none z-50">
                <div class="container mx-auto px-6 h-full flex flex-col justify-center">
                    <div class="lg:w-1/2 flex justify-center lg:justify-start pt-[580px] md:pt-[420px]">
                        <div class="hero-pagination pointer-events-auto flex items-center gap-2"></div>
                    </div>
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
    <section class="bg-white py-10 px-4 md:px-10">
        <div class="container mx-auto">

            <!-- Header -->
            <div class="text-center mb-8">
                <span
                    class="inline-block px-5 py-1.5 rounded-full border border-indigo-100 bg-indigo-50 text-[#00555c] font-semibold text-sm md:text-lg mb-6">
                    All-in-One Solution
                </span>

                <h2 class="text-2xl md:text-4xl animated-text  font-extrabold text-gray-900 mb-10">
                    From Start to Growth — Everything in One System
                </h2>

                <!-- Tabs -->
                <div class="inline-flex p-1.5 bg-indigo-50/30 border-2 border-indigo-100 gap-2 rounded-2xl w-full overflow-auto max-w-[700px]"
                    id="solution-tabs">

                    @foreach ($solutions as $key => $sol)
                        <button onclick="switchSolution('{{ $key }}', this)"
                            class="sol-tab-btn whitespace-nowrap flex-1 px-2 py-2.5 rounded-xl font-bold text-sm md:text-base transition-all
                        {{ $loop->first ? 'bg-[#00555c] text-white' : 'bg-[#00555c30] text-gray-900  ' }}">
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
                            class="inline-flex items-center gap-3 bg-[#00555c] hover:bg-[#2c8a70] text-white px-8 py-4 rounded-2xl font-bold text-lg transition-all shadow-lg">
                            Explore More
                            <i class="fa-solid fa-arrow-right text-sm"></i>
                        </a>
                    </div>

                    <!-- Right Image -->
                    <div>
                        <div class="relative bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden">
                            <div class="bg-gray-50 border-b px-4 py-3 text-xs text-gray-600 font-mono">
                                <span id="sol-mockup"></span>
                            </div>

                            <div class="aspect-video bg-gray-100">
                                <img id="sol-img" src="https://via.placeholder.com/900x600?text=ERP+Dashboard"
                                    class="w-full h-full object-cover object-top" alt="right image" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>
    
    <!-- INTEGRATION SECTION -->
    <section class="bg-black integrate-bg py-20 px-4 md:px-10 overflow-hidden " id="intergation">
        <div class="max-w-[1400px] mx-auto">

            <!-- Section Header -->
            <div class="text-center mb-8">
                <span
                    class="inline-block px-6 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#00555c] font-bold text-[14px] mb-6 uppercase tracking-wider">
                    Smart Integration
                </span>
                <h2 class="text-2xl md:text-4xl animated-text  font-extrabold text-white mb-10">
                    Track and automate your entire business
                </h2>

            </div>

            <!-- <div class="integration-section pt-10">
                <div class="pin-wrap"> -->
            <div class="  pt-10">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2">

                    <!-- গ্রাফ ১: পেমেন্ট মেথড (4 Paths) -->
                    <div class="  border integration-card    shadow-sm relative group">


                        <div class="relative border-[#262626] border-[20px]">
                            <svg viewBox="0 0 460 300" class="w-full h-auto">
                                <!-- 4 Input Paths -->
                                <path class="flow-line" d="M60 40 C 150 40, 170 150, 225 150" />
                                <path class="flow-line" d="M60 113 C 150 113, 170 150, 225 150" />
                                <path class="flow-line" d="M60 186 C 150 186, 170 150, 225 150" />
                                <path class="flow-line" d="M60 260 C 150 260, 170 150, 225 150" />

                                <!-- Pulse Animation (4 Paths) -->
                                <path class="flow-pulse" d="M60 40 C 150 40, 170 150, 225 150" />
                                <path class="flow-pulse" d="M60 113 C 150 113, 170 150, 225 150"
                                    style="animation-delay:.4s" />
                                <path class="flow-pulse" d="M60 186 C 150 186, 170 150, 225 150"
                                    style="animation-delay:.8s" />
                                <path class="flow-pulse" d="M60 260 C 150 260, 170 150, 225 150"
                                    style="animation-delay:1.2s" />

                                <!-- সেন্ট্রাল পালস রিং -->
                                <circle class="ring-pulse" cx="225" cy="150" r="45" fill="none"
                                    stroke="#00555c" stroke-width="1.5" />

                                <!-- Output Path -->
                                <path class="flow-line" d="M255 150 C 320 150, 340 150, 400 150" />
                                <path class="flow-pulse" d="M255 150 C 320 150, 340 150, 400 150"
                                    style="animation-delay:.6s" />
                            </svg>

                            <!-- 4 Payment Source Logos -->
                            <div class="node-circle small" style="left:13%; top:13.3%;"><img
                                    src="{{ asset('./images/saas/nagad.png') }}" alt="Nagad"></div>
                            <div class="node-circle small" style="left:13%; top:37.6%;"><img
                                    src="{{ asset('./images/saas/bkash.png') }}" alt="bKash"></div>

                            <div class="node-circle small" style="left:13%; top:62.1%;"><img
                                    src="{{ asset('./images/saas/rocket.png') }}" alt="SSL"></div>
                            <div class="node-circle small" style="left:13%; top:86.7%;"><img
                                    src="{{ asset('./images/saas/ssl.png') }}" alt="Rocket"></div>

                            <!-- Output Logo -->
                            <div class="node-circle small shadow-lg" style="left:87%; top:50%; border-color:#00555c">
                                <i class="fa-brands fa-shopify text-2xl text-[#00555c]"></i>
                            </div>

                            <!-- Main Center Node -->
                            <div class="center-node">
                                <span
                                    class="text-white text-[11px] md:text-[13px] font-black leading-tight uppercase">Payment</span>
                            </div>
                        </div>
                        <div class="bg-[#262626] text-white px-5 py-5 pt-2  text-left text-sm md:text-base">
                            <h3 class="text-[#12dae8] text-2xl font-bold mb-2"> Multiple Payment Gateways </h3>
                            Integrate leading payment gateways including bKash, Nagad, Rocket, SSLCommerz, and more. Accept
                            secure online payments, automate payment confirmation, and manage every transaction from a
                            single platform.
                        </div>
                    </div>

                    <div class="  border  integration-card   shadow-sm relative group">


                        <div class="relative border-[#262626] border-[20px]">
                            <svg viewBox="0 0 460 300" class="w-full h-auto">
                                <!-- ৪টি ইনপুট পাথ -->
                                <path class="flow-line" d="M60 40 C 150 40, 170 150, 225 150" />
                                <path class="flow-line" d="M60 113 C 150 113, 170 150, 225 150" />
                                <path class="flow-line" d="M60 186 C 150 186, 170 150, 225 150" />
                                <path class="flow-line" d="M60 260 C 150 260, 170 150, 225 150" />

                                <!-- পালস অ্যানিমেশন (৪টি পাথ) -->
                                <path class="flow-pulse" d="M60 40 C 150 40, 170 150, 225 150" />
                                <path class="flow-pulse" d="M60 113 C 150 113, 170 150, 225 150"
                                    style="animation-delay:.4s" />
                                <path class="flow-pulse" d="M60 186 C 150 186, 170 150, 225 150"
                                    style="animation-delay:.8s" />
                                <path class="flow-pulse" d="M60 260 C 150 260, 170 150, 225 150"
                                    style="animation-delay:1.2s" />

                                <!-- সেন্ট্রাল পালস রিং -->
                                <circle class="ring-pulse" cx="225" cy="150" r="45" fill="none"
                                    stroke="#00555c" stroke-width="1.5" />

                                <!-- আউটপুট পাথ -->
                                <path class="flow-line" d="M255 150 C 320 150, 340 150, 400 150" />
                                <path class="flow-pulse" d="M255 150 C 320 150, 340 150, 400 150"
                                    style="animation-delay:.6s" />
                            </svg>

                            <div class="node-circle small" style="left:13%; top:13.3%;"><img
                                    src="{{ asset('./images/saas/steadfast.svg') }}" alt="Steadfast"></div>
                            <div class="node-circle small" style="left:13%; top:37.6%;"><img
                                    src="{{ asset('./images/saas/pathao.svg') }}" alt="Pathao"></div>
                            <div class="node-circle small" style="left:13%; top:86.7%;"><img
                                    src="{{ asset('./images/saas/carrybee.png') }}" alt="carrybee"></div>
                            <div class="node-circle small" style="left:13%; top:62.1%;"><img
                                    src="{{ asset('./images/saas/redx.svg') }}" alt="redx"></div>

                            <!-- Output Logo -->
                            <div class="node-circle small shadow-lg" style="left:87%; top:50%; border-color:#00555c">
                                <i class="fa-brands fa-shopify text-2xl text-[#00555c]"></i>
                            </div>

                            <!-- Main Center Node -->
                            <div class="center-node">
                                <span
                                    class="text-white text-[11px] md:text-[13px] font-black leading-tight uppercase">Courier</span>
                            </div>
                        </div>
                        <div class="bg-[#262626] text-white px-5 py-5 pt-2  text-left text-sm md:text-base">
                            <h3 class="text-[#12dae8] text-2xl font-bold mb-2"> Ship Orders with Multiple Courier Partners
                            </h3>
                            Connect with trusted courier services like Pathao, SteadFast, CarryBee, RedX, and more. Create
                            shipments, track deliveries, manage returns, and update order statuses without leaving dorja.io.
                        </div>
                    </div>
                    <div class="  border  integration-card   shadow-sm relative group">


                        <div class="relative border-[#262626] border-[20px]">
                            <svg viewBox="0 0 460 300" class="w-full h-auto">
                                <!-- ৪টি ইনপুট পাথ -->
                                <path class="flow-line" d="M60 40 C 150 40, 170 150, 225 150" />
                                <path class="flow-line" d="M60 113 C 150 113, 170 150, 225 150" />
                                <path class="flow-line" d="M60 186 C 150 186, 170 150, 225 150" />
                                <path class="flow-line" d="M60 260 C 150 260, 170 150, 225 150" />

                                <!-- পালস অ্যানিমেশন (৪টি পাথ) -->
                                <path class="flow-pulse" d="M60 40 C 150 40, 170 150, 225 150" />
                                <path class="flow-pulse" d="M60 113 C 150 113, 170 150, 225 150"
                                    style="animation-delay:.4s" />
                                <path class="flow-pulse" d="M60 186 C 150 186, 170 150, 225 150"
                                    style="animation-delay:.8s" />
                                <path class="flow-pulse" d="M60 260 C 150 260, 170 150, 225 150"
                                    style="animation-delay:1.2s" />

                                <!-- সেন্ট্রাল পালস রিং -->
                                <circle class="ring-pulse" cx="225" cy="150" r="45" fill="none"
                                    stroke="#00555c" stroke-width="1.5" />

                                <!-- আউটপুট পাথ -->
                                <path class="flow-line" d="M255 150 C 320 150, 340 150, 400 150" />
                                <path class="flow-pulse" d="M255 150 C 320 150, 340 150, 400 150"
                                    style="animation-delay:.6s" />
                            </svg>

                            <!-- ৪টি পেমেন্ট সোর্স লোগো -->
                            <div class="node-circle small" style="left:13%; top:13.3%;"><img
                                    src="{{ asset('./images/saas/whatsapp.svg') }}" alt="WhatsApp"></div>
                            <div class="node-circle small" style="left:13%; top:37.6%;"><img
                                    src="{{ asset('./images/saas/fb.png') }}" alt="Facebook"></div>
                            <div class="node-circle small" style="left:13%; top:86.7%;"><img
                                    src="{{ asset('./images/saas/live.png') }}" alt="Live Support"></div>
                            <div class="node-circle small" style="left:13%; top:62.1%;"><img
                                    src="{{ asset('./images/saas/inst.png') }}" alt="Instagram"></div>


                            <!-- Output Logo -->
                            <div class="node-circle small shadow-lg" style="left:87%; top:50%; border-color:#00555c">
                                <i class="fa-brands fa-shopify text-2xl text-[#00555c]"></i>
                            </div>

                            <!-- Main Center Node -->
                            <div class="center-node">
                                <span
                                    class="text-white text-[11px] md:text-[13px] font-black leading-tight uppercase">Omnichannel</span>
                            </div>
                        </div>
                        <div class="bg-[#262626] text-white px-5 py-5 pt-2  text-left text-sm md:text-base">
                            <h3 class="text-[#12dae8] text-2xl font-bold mb-2"> Manage Customer Conversations from Every
                                Channel </h3>
                            Handle customer inquiries from Facebook Messenger, WhatsApp, Live Chat, and more in one unified
                            inbox. Respond faster, manage conversations efficiently, and deliver a better customer
                            experience.
                        </div>
                    </div>
                    <div class="  border  integration-card   shadow-sm relative group">


                        <div class="relative border-[#262626] border-[20px]">
                            <svg viewBox="0 0 460 300" class="w-full h-auto">
                                <!-- ৪টি ইনপুট পাথ -->
                                <path class="flow-line" d="M60 40 C 150 40, 170 150, 225 150" />
                                <path class="flow-line" d="M60 113 C 150 113, 170 150, 225 150" />
                                <path class="flow-line" d="M60 186 C 150 186, 170 150, 225 150" />
                                <path class="flow-line" d="M60 260 C 150 260, 170 150, 225 150" />

                                <!-- পালস অ্যানিমেশন (৪টি পাথ) -->
                                <path class="flow-pulse" d="M60 40 C 150 40, 170 150, 225 150" />
                                <path class="flow-pulse" d="M60 113 C 150 113, 170 150, 225 150"
                                    style="animation-delay:.4s" />
                                <path class="flow-pulse" d="M60 186 C 150 186, 170 150, 225 150"
                                    style="animation-delay:.8s" />
                                <path class="flow-pulse" d="M60 260 C 150 260, 170 150, 225 150"
                                    style="animation-delay:1.2s" />

                                <!-- সেন্ট্রাল পালস রিং -->
                                <circle class="ring-pulse" cx="225" cy="150" r="45" fill="none"
                                    stroke="#00555c" stroke-width="1.5" />

                                <!-- আউটপুট পাথ -->
                                <path class="flow-line" d="M255 150 C 320 150, 340 150, 400 150" />
                                <path class="flow-pulse" d="M255 150 C 320 150, 340 150, 400 150"
                                    style="animation-delay:.6s" />
                            </svg>

                            <!-- ৪টি পেমেন্ট সোর্স লোগো -->
                            <div class="node-circle small" style="left:13%; top:13.3%;"><img
                                    src="{{ asset('./images/saas/ecim.png') }}" alt="e-commerce"></div>
                            <div class="node-circle small" style="left:13%; top:37.6%;"><img
                                    src="{{ asset('./images/saas/landing.png') }}" alt="Landing Page"></div>
                            <div class="node-circle small" style="left:13%; top:62.1%;"><img
                                    src="{{ asset('./images/saas/woo.png') }}" alt="Woocommerce"></div>
                            <div class="node-circle small" style="left:13%; top:86.7%;"><img
                                    src="{{ asset('./images/saas/daraz.png') }}" alt="Daraz"></div>

                            <!-- Output Logo -->
                            <div class="node-circle small shadow-lg" style="left:87%; top:50%; border-color:#00555c">
                                <i class="fa-brands fa-shopify text-2xl text-[#00555c]"></i>
                            </div>

                            <!-- Main Center Node -->
                            <div class="center-node">
                                <span
                                    class="text-white text-[11px] md:text-[13px] font-black leading-tight uppercase">Omnichannel</span>
                            </div>
                        </div>
                        <div class="bg-[#262626] text-white px-5 py-5 pt-2  text-left text-sm md:text-base">
                            <h3 class="text-[#12dae8] text-2xl font-bold mb-2"> Manage Orders from Every Sales Channel
                            </h3>
                            Receive and manage orders from your Website, Landing Pages, WooCommerce, Daraz, and other
                            connected sales channels through a single dashboard. Process, fulfill, and track every order
                            from one centralized platform.
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </section>
    {{-- <!-- INTEGRATION SECTION -->
    <section class="bg-[#f9faff] py-10 px-4 md:px-10 overflow-hidden">
        <div class="max-w-[1400px] mx-auto">
            <div class="text-center mb-16">
                <span
                    class="inline-block px-6 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#00555c] font-semibold text-base mb-6">
                    Integration
                </span>
                <h2 class="text-2xl md:text-4xl font-extrabold text-gray-900 leading-tight max-w-4xl mx-auto">
                    Track and automate your entire business
                    <br class="hidden md:block" />
                    and grow fast — with one smart system. — একটি স্মার্ট সিস্টেম দিয়ে।
                </h2>
            </div>

            <!-- Integration Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Card 1: Payment Methods -->
                <div class="bg-white border border-indigo-100 rounded-2xl p-8 md:p-14">
                    <div class="flex flex-col items-center w-full">
                        <!-- Top Shopify Icon -->
                        <div
                            class="w-20 h-20 bg-[#00555c] rounded-full flex items-center justify-center shadow-lg shadow-indigo-200 z-10">
                            <i class="fa-brands fa-shopify text-white text-4xl"></i>
                        </div>

                        <!-- Line down to Middle Box -->
                        <div class="w-[2px] h-10 bg-[#00555c]"></div>

                        <!-- Middle Node Box -->
                        <div
                            class="px-8 py-3 border-2 border-[#00555c] rounded-2xl text-gray-900 font-bold text-base bg-white z-10">
                            Payment Methods
                        </div>

                        <!-- The Fork Connection Line -->
                        <div class="w-full relative flex flex-col items-center">
                            <!-- Vertical line from middle box to horizontal bar -->
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>

                            <!-- Horizontal Bar: Exactly connects the centers of 1st and 3rd box -->
                            <div class="absolute bottom-0 w-[66.6%] h-[2px] bg-[#00555c]"></div>
                        </div>

                        <!-- 3 Vertical Lines down to logos -->
                        <div class="flex justify-between w-full px-[16.6%]">
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                        </div>

                        <!-- Logo Row -->
                        <div class="grid grid-cols-3 gap-4 w-full">
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/nagad.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="Nagad"
                                    onerror="
                        this.src =
                          'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Nagad_Logo.svg/1200px-Nagad_Logo.svg.png'
                      " />
                            </div>
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/bkash.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="bKash" />
                            </div>
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
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
                            class="w-20 h-20 bg-[#00555c] rounded-full flex items-center justify-center shadow-lg shadow-indigo-200 z-10">
                            <i class="fa-brands fa-shopify text-white text-4xl"></i>
                        </div>

                        <div class="w-[2px] h-10 bg-[#00555c]"></div>

                        <div
                            class="px-8 py-3 border-2 border-[#00555c] rounded-2xl text-gray-900 font-bold text-base bg-white z-10">
                            Courier Management
                        </div>

                        <div class="w-full relative flex flex-col items-center">
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="absolute bottom-0 w-[66.6%] h-[2px] bg-[#00555c]"></div>
                        </div>

                        <div class="flex justify-between w-full px-[16.6%]">
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 w-full">
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/steadfast.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="Steadfast" />
                            </div>
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/pathao.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="Pathao" />
                            </div>
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <span class="font-black italic text-[#FF9900] text-sm md:text-lg">Carry<span
                                        class="text-[#000]">Bee</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-indigo-100 rounded-2xl p-8 md:p-14">
                    <div class="flex flex-col items-center w-full">
                        <!-- Top Shopify Icon -->
                        <div
                            class="w-20 h-20 bg-[#00555c] rounded-full flex items-center justify-center shadow-lg shadow-indigo-200 z-10">
                            <i class="fa-brands fa-shopify text-white text-4xl"></i>
                        </div>

                        <div class="w-[2px] h-10 bg-[#00555c]"></div>

                        <div
                            class="px-8 py-3 border-2 border-[#00555c] rounded-2xl text-gray-900 font-bold text-base bg-white z-10">
                            Omni Channel Chat
                        </div>

                        <div class="w-full relative flex flex-col items-center">
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="absolute bottom-0 w-[66.6%] h-[2px] bg-[#00555c]"></div>
                        </div>

                        <div class="flex justify-between w-full px-[16.6%]">
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 w-full">
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/steadfast.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="Steadfast" />
                            </div>
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/pathao.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="Pathao" />
                            </div>
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <span class="font-black italic text-[#FF9900] text-sm md:text-lg">Carry<span
                                        class="text-[#000]">Bee</span></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-indigo-100 rounded-2xl p-8 md:p-14">
                    <div class="flex flex-col items-center w-full">
                        <!-- Top Shopify Icon -->
                        <div
                            class="w-20 h-20 bg-[#00555c] rounded-full flex items-center justify-center shadow-lg shadow-indigo-200 z-10">
                            <i class="fa-brands fa-shopify text-white text-4xl"></i>
                        </div>

                        <div class="w-[2px] h-10 bg-[#00555c]"></div>

                        <div
                            class="px-8 py-3 border-2 border-[#00555c] rounded-2xl text-gray-900 font-bold text-base bg-white z-10">
                           Order Source
                        </div>

                        <div class="w-full relative flex flex-col items-center">
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="absolute bottom-0 w-[66.6%] h-[2px] bg-[#00555c]"></div>
                        </div>

                        <div class="flex justify-between w-full px-[16.6%]">
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                            <div class="w-[2px] h-10 bg-[#00555c]"></div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 w-full">
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/steadfast.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="Steadfast" />
                            </div>
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <img src="{{ asset('./images/saas/pathao.png') }}" class="h-8 md:h-10 object-contain"
                                    alt="Pathao" />
                            </div>
                            <div
                                class="border-2 border-[#00555c] rounded-2xl p-4 flex items-center justify-center bg-white h-20 md:h-24 hover:shadow-md transition cursor-pointer">
                                <span class="font-black italic text-[#FF9900] text-sm md:text-lg">Carry<span
                                        class="text-[#000]">Bee</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> --}}
   
    <!-- FEATURES SECTION -->
    @if ($topFeatures->isNotEmpty())
        <section class="bg-white py-10">
            <div class="container mx-auto px-6 md:px-10">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <span
                        class="inline-block px-5 md:px-8 py-1.5 md:py-2.5 rounded-full border border-indigo-100 bg-indigo-50/50 text-[#00555c] font-semibold text-sm md:text-lg mb-6">
                        Features
                    </span>
                    <h2 class="text-2xl md:text-4xl animated-text  font-extrabold text-gray-900 ">
                        Everything you need for your business <br class="hidden md:block" />
                        now in one place
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($topFeatures as $feature)
                        <a href="{{ route('saas.feature.details', $feature->slug) }}"
                            class="flex items-center gap-5 p-4 group rounded border border-gray-200 hover:border-[#00555c] transition-all duration-300">

                            <!-- Left Icon -->
                            <div class="flex-shrink-0">
                                <div
                                    class="w-12 h-12  bg-[#00555c21] rounded
                   border border-white/20
                   flex items-center justify-center">
                                    <i class="{{ $feature->icon ?? 'fa-solid fa-file-lines' }} text-2xl text-[#00555c]"></i>
                                </div>
                            </div>

                            <!-- Right Content -->
                            <div class="flex-1 min-w-0">
                                <div class="block w-full truncate text-lg text-gray-900 group-hover:text-[#00555c]">
                                    {{ $feature->title }}
                                </div>



                                <span class="inline-flex items-center gap-2 font-semibold text-[#00555c]">
                                    Read More
                                    <i
                                        class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                                </span>
                            </div>

                        </a>
                    @endforeach
                </div>

                <div class="mt-16 text-center">
                    <a href="https://app.dorja.io/register"
                        class="inline-block bg-[#00555c] text-white px-10 py-4 rounded-xl font-bold hover:bg-[#078e9a] transition shadow-lg shadow-indigo-100">
                        Start Free Trial
                    </a>
                </div>
            </div>
        </section>
    @endif
     <!-- SUCCESS SECTION (Dark Theme) -->
    <section class="bg-[#020410] py-24 px-6 md:px-10 relative overflow-hidden hook-2">
        <div
            class="absolute top-0 right-0 w-[500px] h-[500px] bg-[#00555c]/10 blur-[120px] rounded-full pointer-events-none">
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
                        Start Your Online Business Now and Grow with dorja.io
                    </h2>
                    <!-- Description -->
                    <p class="text-gray-300 text-lg leading-relaxed max-w-2xl">
                        Our advanced integrations make your business operations easier, smarter, and significantly more
                        powerful.
                    </p>
                </div>

                <!-- CTA Button -->
                <div class="flex-shrink-0">
                    <a href="https://app.dorja.io/register"
                        class="inline-block bg-[#00555c] hover:bg-[#078e9a] text-white px-6 py-4 rounded-2xl font-bold text-md transition shadow-lg shadow-indigo-500/20">
                        Start Now
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
                    <p class="text-gray-300 text-[17px] leading-relaxed mb-8">
                        Select a plan that fits your business size and goals. dorja.io offers flexible packages for startups
                        to enterprises that grow with you.
                    </p>
                    <a href="{{ route('saas.package.list') }}"
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
                    <p class="text-gray-300 text-[17px] leading-relaxed mb-8">
                        Discover a complete suite of business tools designed to automate operations, improve efficiency, and
                        help you make faster data-driven decisions.
                    </p>
                    <a href="{{ route('saas.feature.list') }}"
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
                    <p class="text-gray-300 text-[17px] leading-relaxed mb-8">
                        Join thousands of businesses already using dorja.io. Start your journey today and transform the way
                        you manage and grow your business.
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
    <!-- DEMO & TEMPLATE SECTION -->
    @if ($demos->isNotEmpty())
        <section class="bg-white py-10 px-4 md:px-10" id="demo-section">
            <div class="max-w-[1400px] mx-auto">
                <!-- Section Header -->
                <div class="text-center mb-12">
                    <span
                        class="inline-block px-5 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#00555c] font-semibold text-sm md:text-lg mb-6">
                        Demo & Template
                    </span>
                    <h2 class="text-2xl md:text-4xl animated-text  font-extrabold text-gray-900  mb-10">
                        Get a complete system live experience on one platform
                    </h2>

                    <!-- Tabs Container (Clickable and Hover Effect Fixed) -->
                    <div class="inline-flex p-1.5 bg-white border-2 border-indigo-100 rounded-2xl" id="tab-container">
                        <button onclick="filterDemos(1, this)"
                            class="tab-btn bg-[#00555c] text-white px-6 md:px-10 py-3 rounded-xl font-bold text-sm md:text-base transition-all">
                            Landing Page Template
                        </button>
                        <button onclick="filterDemos(2, this)"
                            class="tab-btn text-gray-900 px-6 md:px-10 py-3 rounded-xl font-bold text-sm md:text-base hover:bg-indigo-50 hover:text-[#00555c] transition-all">
                            E-Commerce Template
                        </button>
                    </div>
                </div>

                <!-- Templates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="demo-grid">
                    @forelse($demos as $demo)
                        <div class="demo-card group border border-indigo-100 rounded-xl overflow-hidden bg-white"
                            data-type="{{ $demo->type }}">
                            <div class="aspect-[4/3] overflow-hidden bg-gray-100">
                                <img src="{{ $demo->image_url ?? asset('images/saas/live1.png') }}" alt="demo image"
                                    class="w-full h-full object-cover object-top rounded-xl border border-gray-200 transition-transform duration-700 group-hover:scale-105"
                                    alt="{{ $demo->title }}" />
                            </div>
                            <div class="py-6 text-center border-t border-gray-100">
                                <a href="{{ $demo->link ?? '#' }}" target="_blank"
                                    class="text-xl md:text-2xl font-bold text-gray-900 underline hover:text-[#00555c] hover:decoration-[#00555c]">
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
            'ERP',
            'Business Management',
            'Cloud ERP',
            'Business Software',
            'SaaS',
            'Business Automation',
            'Enterprise Software',
            'Digital Business',
            'Business Solution',
            'Workflow Automation',
            'Business Dashboard',
            'Business Analytics',
            'Reports',
            'Financial Management',
            'Business Intelligence',
            'Organization Management',
            'Operations Management',
            'Productivity',
            'SME Software',
            'Enterprise Resource Planning',
        ];
        $badges2 = [
            'POS System',
            'Point of Sale',
            'Sales Management',
            'Order Management',
            'Inventory Management',
            'Stock Management',
            'Warehouse Management',
            'Product Management',
            'SKU Management',
            'Barcode Management',
            'Purchase Management',
            'Supplier Management',
            'Invoice Management',
            'Quotation',
            'Billing Software',
            'eCommerce',
            'WooCommerce',
            'Courier Integration',
            'Payment Gateway',
            'Multi Warehouse',
        ];
        $badges3 = [
            'CRM',
            'Customer Management',
            'Customer Analytics',
            'Lead Management',
            'HRM',
            'Payroll',
            'Employee Management',
            'Attendance Management',
            'Marketing Automation',
            'Email Marketing',
            'SMS Marketing',
            'Landing Page Builder',
            'CMS',
            'Website Management',
            'Multi Branch',
            'Profit & Loss',
            'Accounting',
            'Expense Management',
            'Income Management',
            'Cloud Software',
        ];

        $rows = [
            ['class' => 'animate-marquee-left', 'data' => $badges, 'duration' => '30s'],

            ['class' => 'animate-marquee-right', 'data' => collect($badges2)->reverse()->all(), 'duration' => '35s'],

            ['class' => 'animate-marquee-left', 'data' => collect($badges3)->shuffle()->all(), 'duration' => '40s'],
        ];
    @endphp

    <!-- INFINITY LOOP SECTION -->
    <section class="bg-white py-14 overflow-hidden scroll-container">
        <div class="flex flex-col gap-8">

            @foreach ($rows as $row)
                <div class="relative flex overflow-hidden">
                    {{-- Container that animation will work on --}}
                    <div class="{{ $row['class'] }}" style="--duration: {{ $row['duration'] }}">

                        {{-- একই কন্টেন্ট দুইবার দেওয়া হয়েছে যাতে লুপটি নিরবচ্ছিন্ন হয় --}}
                        @foreach ([1, 2] as $repeat)
                            <div class="flex gap-6 pr-6"> {{-- pr-6 গ্যাপ বজায় রাখার জন্য --}}
                                @foreach ($row['data'] as $item)
                                    <span
                                        class="bg-[#00555c] text-white px-10 py-4 rounded-2xl font-bold whitespace-nowrap text-lg shadow-sm border border-[#2d8a71]">
                                        {{ $item }}
                                    </span>
                                @endforeach
                            </div>
                        @endforeach

                    </div>
                </div>
            @endforeach

        </div>
    </section>
    <section class="py-24 hero-bg relative overflow-hidden">
        <!-- Background Blur -->
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl"></div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">

                <span
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold">
                    🚀 Business Made Simple
                </span>

                <h2 class="mt-6 text-4xl md:text-5xl font-extrabold text-white leading-tight">
                    Ready to Grow Your Business?
                </h2>

                <p class="mt-6 text-lg text-gray-300 leading-8 max-w-3xl mx-auto">
                    Join thousands of businesses using <strong>Dorja.io</strong> to manage sales,
                    inventory, accounting, CRM, HRM, POS, warehouses, and marketing from one
                    powerful cloud platform.
                </p>

                <!-- Buttons -->
                <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4">

                    <a href="https://app.dorja.io/register"
                        class="px-8 py-4 rounded-xl bg-[#00555c] hover:bg-[#078e9a] text-white font-semibold transition duration-300 shadow-lg">
                        Start Free Trial
                    </a>

                    <a href="{{ route('saas.package.list') }}"
                        class="px-8 py-4 rounded-xl border border-white/20 hover:border-white text-white font-semibold transition duration-300">
                        View Pricing
                    </a>

                </div>

                <!-- Trust Badges -->
                <div class="mt-12 flex flex-wrap justify-center gap-x-8 gap-y-4 text-sm text-gray-400">

                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        Free Trial
                    </div>

                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        No Credit Card Required
                    </div>

                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        Setup in Minutes
                    </div>

                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        Secure Cloud Platform
                    </div>

                </div>

            </div>
        </div>
    </section>
    
    @if ($pricingPlans->isNotEmpty())
        <section class="bg-[#fcfcfc] py-24 px-6 md:px-10 ">
            <div class="container mx-auto">

                <div class="text-center mb-20">
                   <span
                        class="inline-block px-5 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#00555c] font-semibold text-sm md:text-lg mb-6">
                        Choose Best Plan for Your Business
                    </span>
                    <h2 class="text-2xl md:text-4xl animated-text  font-extrabold text-gray-900  mb-10">
                      Here are the best packages for your business below.
                    </h2>
 
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">

                    @foreach ($pricingPlans as $plan)
                        @php
                            $mode = strtolower($plan->mode);
                            $monthlyTier = collect($plan->tiers)->firstWhere('billing_cycle', 'monthly');
                            $price = $monthlyTier['discount_price'] ?? ($monthlyTier['regular_price'] ?? 0);

                            $themeColor = '#1e1b4b'; // Default Navy (For all other modes)
                            $isSpecialMode = false;

                            if ($mode === 'regular') {
                                $themeColor = '#7e22ce'; // Purple
                                $isSpecialMode = true;
                            } elseif ($mode === 'popular') {
                                $themeColor = '#0073ea'; // Blue
                                $isSpecialMode = true;
                            }
                        @endphp

                        <!-- Card Container -->
                        <div class="bg-white border border-gray-200 flex flex-col h-full p-7 transition-all duration-300 relative border-t-[6px] shadow-sm hover:shadow-xl"
                            style="border-top-color: {{ $themeColor }};">

                            <!-- Header: Title and Most Popular Badge -->
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h2>


                                @if ($mode === 'popular')
                                    <div
                                        class="relative bg-[#0073ea] text-white text-[10px] font-black uppercase px-2.5 py-1.5 rounded-sm flex items-center shadow-sm tracking-tighter">
                                        <span>Most Popular</span>
                                        <div
                                            class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0073ea] rotate-45">
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Price Section -->
                            <div class="mb-4">
                                <div class="flex items-start gap-1">
                                    <span class="text-3xl font-bold"
                                        style="color: {{ $isSpecialMode ? $themeColor : '#111' }};">
                                        {{ $setup->currency ?? '$' }} {{ number_format($price, 0) }}
                                        <span
                                            class="inline-block text-gray-600 text-xl font-semibold line-through ml-[-5px]">
                                            <del>{{ number_format($monthlyTier['regular_price'] ?? 0, 0) }}</del>
                                        </span>
                                    </span>
                                    <div class="text-xs text-gray-500 font-bold pt-2 leading-tight">

                                        <span>/month</span>
                                    </div>
                                </div>

                                <!-- <div class="mt-4">
                                    <p class="text-gray-900 font-bold text-sm">Total
                                        {{ $setup->currency ?? '$' }}{{ number_format($price, 0) }} / Yearly</p>
                                    <p class="text-gray-600 text-xs">Billed annually</p>
                                </div> -->
                            </div>

                            <!-- CTA Button -->
                            <div class="mb-4">
                                <a href="https://app.dorja.io/register?plan={{ $plan->id }}"
                                    class="block text-center border-[1.5px] py-2.5 rounded-full font-bold text-sm transition-all hover:bg-gray-50"
                                    style="border-color: {{ $themeColor }}; color: {{ $themeColor }};">
                                    Start Free Trial
                                </a>
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <p class="text-gray-600 text-sm leading-relaxed">
                                    {{ strtolower($plan->name) == 'starter'
                                        ? 'Start your business with confidence.'
                                        : (strtolower($plan->name) == 'growth'
                                            ? 'Scale faster with smarter tools.'
                                            : (strtolower($plan->name) == 'business'
                                                ? 'Powerful tools for growing teams.'
                                                : 'Enterprise-grade performance & support.')) }}
                                </p>
                            </div>

                            <!-- Features/Limits Section (Fixed at bottom) -->
                            <div class="mt-auto">
                                <hr class="border-gray-200 border-1 mb-4">

                                <div class="space-y-2">

                                    <div class="space-y-2">

                                        @foreach ([['User Limit', $plan->user_limit], ['Product Limit', $plan->product_limit], ['Order Limit', $plan->order_limit], ['Extra Order', $plan->extra_order_charge]] as [$label, $value])
                                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-5 h-5 rounded-full bg-[#7e22ce17] flex items-center justify-center">
                                                        <i class="fa-solid fa-check text-[10px] text-[#7e22ce]"></i>
                                                    </div>

                                                    <span class="text-sm text-[#7e22ce] font-semibold">
                                                        {{ $label }}
                                                    </span>
                                                </div>

                                                <span class="text-sm font-semibold text-[#7e22ce]">
                                                    {{ $label === 'Extra Order' ? '৳.' : '' }}{{ $value ?: 'Unlimited' }}
                                                    {{ $label === 'Extra Order' ? '/order' : '' }}
                                                </span>
                                            </div>
                                        @endforeach

                                    </div>

                                    <!-- <div class="flex justify-between items-center text-gray-700 text-sm">
                                        <span>Invoice Limit: {{ $plan->invoice_limit ?: 'Unlimited' }}</span>
                                        <i class="fa-regular fa-circle-info text-gray-300 text-xs"></i>
                                    </div> -->

                                    {{-- Custom multiple input loop --}}
                                    @if (!empty($plan->multiple_input))
                                        @foreach ($plan->multiple_input as $extraDetail)
                                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center">
                                                        <i class="fa-solid fa-check text-[10px] text-emerald-600"></i>
                                                    </div>

                                                    <span class="text-sm text-gray-600">
                                                        {{ $extraDetail }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
                <div class="mt-16 text-center">
                    <a href="{{ route('saas.package.list') }}"
                        class="inline-block bg-[#00555c] text-white px-10 py-4 rounded-xl font-bold hover:bg-[#078e9a] transition shadow-lg shadow-indigo-100">
                        More Packages <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>
            </div>
        </section>
    @endif
    <!-- WHY CHOOSE US SECTION (Placement = 2) -->
    @if ($whyChooseUs->isNotEmpty())
        <section class="bg-[#f9fafb] py-24 px-6 md:px-10 relative overflow-hidden">
            <div class="container mx-auto">
                <div class="text-center mb-20">
                    <span
                        class="inline-block px-5 py-1.5 rounded-full border border-indigo-100 bg-indigo-50 text-[#00555c] font-semibold text-sm md:text-lg mb-6">
                       Why Businesses Choose Dorja.io 
                    </span>

                    <h2 class="text-2xl animated-text  md:text-4xl font-extrabold text-gray-900 mb-10">
                        From Operations to Growth — Everything in One System
                    </h2>
                </div>

                <div class="flex flex-col gap-10">
                    @foreach ($whyChooseUs as $index => $benefit)
                        <div
                            class="bg-white   border-black-100 rounded-2xl p-8 md:p-14 flex flex-col-reverse {{ $loop->even ? 'lg:flex-row-reverse' : 'lg:flex-row' }} items-center gap-12 lg:gap-20">
                            <div class="w-full lg:w-1/2 text-center lg:text-left">
                                <h3 class="text-[#00555c] md:text-3xl text-xl md:text-4xl font-extrabold lg:mb-6 mb-2">
                                    {{ $benefit->title }}
                                </h3>
                                <div class="text-gray-800 lg:text-lg text-sm leading-relaxed   max-w-xl">
                                    {!! $benefit->description !!}
                                </div>
                            </div>
                            <div class="w-full lg:w-1/2">
                                <img src="{{ $benefit->image_url ?? asset('images/saas/choose.jpg') }}"
                                    class="w-full h-auto rounded-3xl  " alt="{{ $benefit->title }}" />
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 text-center">
                    <a href="#"
                        class="inline-block bg-[#00555c] hover:bg-[#078e9a] text-white px-10 py-4 rounded-xl font-bold text-lg transition shadow-lg shadow-indigo-500/20">
                        Start Free Trial
                    </a>
                </div>
            </div>
        </section>
    @endif
   
    <!-- BLOG & INSIGHTS SECTION -->
    @if ($blogs->isNotEmpty())
        <section class="bg-white py-10 px-6 md:px-10">
            <div class="container mx-auto">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-12 gap-6">
                    <h2 class="text-2xl md:text-4xl font-black text-gray-900">Our Blogs</h2>
                    <a href="{{ route('saas.blog.list') }}"
                        class="bg-[#00555c] text-white px-8 py-3 rounded-xl font-bold text-lg hover:bg-[#078e9a] transition shadow-lg shadow-indigo-100">
                        View All <i class="fa-solid fa-arrow-right text-sm"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($blogs as $blog)
                        <a href="{{ route('saas.blog.details', $blog->slug) }}"
                            class="bg-white border border-gray-100 rounded-2xl overflow-hidden group hover:shadow-xl transition-all duration-300 flex flex-col h-full">

                            <div class="group block overflow-hidden rounded-xl">
                                <div class="aspect-[16/10] bg-[#eef2ff] relative overflow-hidden">
                                    <img src="{{ $blog->thumbnail_url ? asset($blog->thumbnail_url) : asset('images/saas/live1.png') }}"
                                        alt="{{ $blog->title }}"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                </div>
                            </div>

                            <div class="p-6 md:p-8 flex flex-col flex-grow">
                                <div class="flex justify-between items-center mb-5">
                                    <span
                                        class="bg-indigo-50 text-[#00555c] px-4 py-1 rounded-full text-xs font-bold border border-indigo-100">
                                        {{ $blog->company->shop_name ?? 'Admin' }}
                                    </span>
                                    <div class="flex items-center gap-2 text-gray-600 text-sm font-bold">
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
                                    <div
                                        class="inline-flex items-center gap-2 text-[#00555c] font-bold text-lg group-hover:gap-3 transition-all">
                                        Read More <i class="fa-solid fa-arrow-right text-sm"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    <!-- Review SECTION -->
    @if ($allReviews->isNotEmpty())
        <section class="bg-[#f9faff] py-10 px-6 md:px-10" id="reviews-section">
            <div class="container mx-auto">
                <div class="text-center mb-16">
                    <span
                        class="inline-block px-5 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#00555c] font-semibold text-sm md:text-lg mb-6">
                        Customer Reviews
                    </span>
                    <h2 class="text-3xl md:text-5xl font-black text-gray-900">
                        What our customers say
                    </h2>
                </div>

                <!-- Masonry Grid -->
                <div class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6" id="review-container">
                    @foreach ($allReviews as $index => $review)
                        <div
                            class="review-card break-inside-avoid bg-white border border-indigo-100 p-8 rounded-2xl hover:shadow-md transition {{ $index >= 6 ? 'hidden' : '' }}">
                            <div class="flex justify-between items-start {{ $review->review ? 'mb-6' : '' }}">
                                <div>
                                    <h3 class="text-[#00555c] font-bold text-lg">
                                        @ {{ $review->name }}
                                    </h3>
                                    <p class="text-gray-500 text-xs">{{ $review->designation }}</p>
                                </div>
                                <div class="flex items-center gap-1 text-gray-900 font-bold">
                                    <i class="fa-solid fa-star text-[#fde047]"></i>
                                    <span>{{ number_format($review->rating, 1) }}</span>
                                </div>
                            </div>

                            @if ($review->review)
                                <p class="text-gray-700 leading-relaxed text-base">
                                    “{{ $review->review }}”
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($allReviews->count() > 6)
                    <div class="mt-16 text-center">
                        <button id="load-more-reviews"
                            class="inline-block bg-[#00555c] text-white px-10 py-3 rounded-xl font-bold hover:bg-[#078e9a] transition shadow-lg shadow-indigo-100">
                            See More
                        </button>
                    </div>
                @endif
            </div>
        </section>
    @endif
    <section class="bg-white py-10 px-6 md:px-10" id="faq-section">
        <div class="container mx-auto">

            <!-- Header -->
            <div class="text-center mb-10">
                <span
                    class="inline-block px-5 py-2 rounded-full border border-indigo-100 bg-indigo-50 text-[#00555c] font-semibold text-sm md:text-lg mb-6">
                    Frequently Asked Questions
                </span>

              <h2 class="text-2xl animated-text  md:text-4xl font-extrabold text-gray-900 mb-10">
                   Better Understanding of Dorja.io and its Features
                </h2>

                <!-- Search Box -->
                <div class="mt-8 max-w-xl mx-auto">
                    <input type="text" id="faqSearch" placeholder="Search FAQ..."
                        class="w-full px-5 py-3 border border-indigo-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                </div>
            </div>

            @php
                $faqs = App\Models\KnowledgeBase::where('status', 1)
                    ->where('company_id', null)
                    ->orderBy('id', 'asc')
                    ->get();
            @endphp

            <div class="div space-y-4 h-[700px] overflow-y-auto">
                <!-- FAQ List -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 " id="faq-container">

                    @foreach ($faqs as $faq)
                        <div class="faq-item bg-[#f9faff] border border-indigo-100 rounded-2xl">

                            <!-- Question -->
                            <button
                                class="p-6 w-full flex justify-between items-center text-left font-bold text-gray-900 text-lg faq-toggle">
                                <span class="faq-question">{{ $faq->title }}</span>

                                <i class="fa-solid fa-chevron-down transition-transform duration-300"></i>
                            </button>

                            <!-- Answer -->
                            <div class="px-6 pb-6 faq-content mt-[-2px] text-gray-600 leading-relaxed hidden">
                                {!! $faq->content !!}
                            </div>

                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </section>
 <section class="py-10 bg-white">
    <div class="container mx-auto px-6">

        

       

        <div class=" bg-gradient-to-r from-emerald-600 to-teal-600 rounded-3xl p-10 text-center text-white">

            <h3 class="text-3xl font-bold">
                Start Managing Your Business Smarter Today
            </h3>

            <p class="mt-4 text-emerald-100 max-w-2xl mx-auto leading-8">
                Join businesses using Dorja.io to simplify operations, increase productivity,
                and grow faster with one complete cloud-based business management platform.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

                <a href="https://app.dorja.io/register"
                    class="bg-white text-emerald-700 font-bold px-7 py-3 rounded-xl hover:bg-gray-100 transition">
                    Start Free Trial
                </a>

                <a href="{{ route('saas.package.list') }}"
                    class="border border-white px-7 py-3 rounded-xl hover:bg-white hover:text-emerald-700 transition">
                    View Pricing
                </a>

            </div>

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
        document.getElementById('faqSearch').addEventListener('input', function() {
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
                    effect: 'fade',
                    fadeEffect: {
                        crossFade: true
                    },
                    autoplay: {
                        delay: 2000,
                        disableOnInteraction: false,
                    },
                    speed: 900,
                    pagination: {
                        el: '.hero-pagination',
                        clickable: true,
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
                <div class="w-8 h-8 bg-[#00555c] rounded-lg flex items-center justify-center text-white">
                    <i class="fa-solid ${f.icon} text-sm"></i>
                </div>
                <span class="font-bold text-gray-900 text-lg">${f.text}</span>
            </div>
        `;
            });

            document.getElementById('sol-features').innerHTML = featureHtml;

            document.querySelectorAll('.sol-tab-btn').forEach(b => {
                b.classList.remove('bg-[#00555c]', 'text-white');
                b.classList.add('bg-[#00555c30]', 'text-gray-900');
            });

            btn.classList.add('bg-[#00555c]', 'text-white');
            btn.classList.remove('bg-[#00555c30]', 'text-gray-900');
        }

        // default load
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector(".sol-tab-btn").click();
        });
    </script>
    <script>
        //review
        document.addEventListener('DOMContentLoaded', function() {
            const loadMoreBtn = document.getElementById('load-more-reviews');
            const itemsToShow = 6;

            if (loadMoreBtn) {
                loadMoreBtn.addEventListener('click', function() {
                    const hiddenCards = document.querySelectorAll('.review-card.hidden');

                    for (let i = 0; i < itemsToShow && i < hiddenCards.length; i++) {
                        hiddenCards[i].classList.remove('hidden');
                    }

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
                b.classList.remove('bg-[#00555c]', 'text-white');
                b.classList.add('text-gray-900', 'hover:bg-indigo-50', 'hover:text-[#00555c]');
            });

            btn.classList.add('bg-[#00555c]', 'text-white');
            btn.classList.remove('text-gray-900', 'hover:bg-indigo-50', 'hover:text-[#00555c]');

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
