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
    <link rel="stylesheet" href="{{ asset('dorja-modules.css') }}">
    <style>
        .hero-bg {
            background-image: linear-gradient(rgba(0, 85, 92, 0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(0, 85, 92, 0.05) 1px, transparent 1px);
            background-size: 56px 56px;
        }

        /* 2x3 Hero Card Grid Styles */
        .hero-card-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            width: 100%;
            max-width: 580px;
        }

        .hero-widget-card {
            border-radius: 20px;
            padding: 16px 18px;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hero-widget-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 40px -22px rgba(0, 58, 63, 0.45);
        }

        .hero-widget-card.card-primary {
            background-color: #00555C;
            color: #ffffff;
            box-shadow: 0 18px 40px -20px rgba(0, 85, 92, 0.6);
        }

        .hero-widget-card.card-light {
            background: rgba(255, 255, 255, 0.55);
            -webkit-backdrop-filter: blur(18px) saturate(170%);
            backdrop-filter: blur(18px) saturate(170%);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.9) inset, 0 18px 40px -24px rgba(0, 58, 63, 0.35);
        }

        .hero-widget-card .mini-bars {
            display: flex;
            align-items: flex-end;
            gap: 4px;
            height: 28px;
            margin-top: 8px;
        }

        .hero-widget-card .mini-bars i {
            flex: 1;
            border-radius: 3px 3px 0 0;
        }

        .card-primary .mini-bars i {
            background: #7CE0C3;
        }

        .card-light .mini-bars i {
            background: #00555C;
        }

        .hero-btn-nav {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            -webkit-backdrop-filter: blur(14px);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 8px 24px -14px rgba(0, 58, 63, 0.4);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #0A1F22;
            font-size: 13px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .hero-btn-nav:hover {
            background: rgba(255, 255, 255, 0.95);
            color: #00555C;
        }

        /* 3D Perspective & EduManager Mockup Styles */
        .perspective-container {
            perspective: 1600px;
        }

        .tilted-device-card {
            transform-origin: center top;
            transform: scale(0.96) rotateX(10deg);
            transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .tilted-device-card:hover {
            transform: scale(0.99) rotateX(2deg);
        }

        @keyframes float-slow {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-9px);
            }
        }

        .float-slow {
            animation: float-slow 4s ease-in-out infinite;
        }

        /* EduManager Exact Heading Scale */
        .hero-display-heading {
            font-size: clamp(1.85rem, 3.6vw, 3.25rem) !important;
            font-weight: 700 !important;
            line-height: 1.18 !important;
            letter-spacing: -0.025em !important;
            color: #0A1F22 !important;
        }

        .hero-gradient-text {
            background: linear-gradient(90deg, #00555C 0%, #008272 50%, #00A08A 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            display: inline-block !important;
        }

        /* Solution Tabs Styling (Matching dorja.io — Modern Homepage Redesign-2nd.html) */
        .sol-tab-btn {
            font-size: 15px !important;
            font-weight: 600 !important;
            padding: 11px 20px !important;
            border-radius: 999px !important;
            cursor: pointer !important;
            min-height: 44px !important;
            border: 1px solid #D7E2DF !important;
            background: rgba(255, 255, 255, 0.6) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            backdrop-filter: blur(12px) !important;
            color: #2C4346 !important;
            outline: none !important;
            transition: all 0.25s cubic-bezier(0.25, 1, 0.5, 1) !important;
        }

        .sol-tab-btn.active {
            background: #00555C !important;
            border-color: #00555C !important;
            color: #FFFFFF !important;
            box-shadow: 0 8px 22px -6px rgba(0, 85, 92, 0.45) !important;
        }

        .sol-tab-btn:not(.active) {
            background: rgba(255, 255, 255, 0.6) !important;
            border-color: #D7E2DF !important;
            color: #2C4346 !important;
        }

        .sol-tab-btn:not(.active):hover {
            background: rgba(255, 255, 255, 0.95) !important;
            border-color: #00555C !important;
            color: #00555C !important;
        }

        @media (max-width: 768px) {
            .tilted-device-card {
                transform: none !important;
            }
        }

        @media (max-width: 640px) {
            .hero-card-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                max-width: 100%;
            }

            .hero-widget-card {
                padding: 12px 14px;
                min-height: 120px;
            }
        }

        /* Hero Dynamic Stage & Satellite Orbit Styles */
        .hero-stage {
            position: relative;
            width: 100%;
            max-width: 580px;
            height: 480px;
            margin: 0 auto;
        }

        .hero-stage svg.hero-svg-lines {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .hero-stage .ln {
            fill: none;
            stroke: url(#heroGradientLine);
            stroke-width: 1.6;
            stroke-dasharray: 5 6;
            animation: heroFlow 1.4s linear infinite;
        }

        @keyframes heroFlow {
            to {
                stroke-dashoffset: -22;
            }
        }

        .hero-hub {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 110px;
            height: 110px;
            margin: -55px 0 0 -55px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle, #0e3b37 0%, #031412 100%);
            border: 2px solid #00D2C4;
            box-shadow: 0 0 35px rgba(0, 210, 196, 0.55), inset 0 0 20px rgba(0, 210, 196, 0.25);
            animation: heroHubPulse 2.6s ease-in-out infinite;
            text-align: center;
            z-index: 10;
        }

        @keyframes heroHubPulse {
            50% {
                box-shadow: 0 0 55px rgba(0, 210, 196, 0.8), inset 0 0 25px rgba(0, 210, 196, 0.35);
            }
        }

        .hero-orb {
            position: absolute;
            left: 50%;
            top: 50%;
            border-radius: 50%;
            border: 1px dashed rgba(0, 210, 196, 0.35);
            pointer-events: none;
        }

        .hero-orb.o1 {
            width: 180px;
            height: 180px;
            margin: -90px 0 0 -90px;
            animation: heroSpin 28s linear infinite;
        }

        .hero-orb.o2 {
            width: 270px;
            height: 270px;
            margin: -135px 0 0 -135px;
            animation: heroSpin 44s linear infinite reverse;
        }

        .hero-orb:after {
            content: "";
            position: absolute;
            top: -4px;
            left: 50%;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #00D2C4;
            box-shadow: 0 0 12px #00D2C4;
        }

        @keyframes heroSpin {
            to {
                transform: rotate(360deg);
            }
        }

        .hero-stage .hero-card {
            position: absolute;
            width: 165px;
            box-sizing: border-box;
            padding: 8px 10px;
            border-radius: 12px;
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(0, 210, 196, 0.35);
            -webkit-backdrop-filter: blur(14px);
            backdrop-filter: blur(14px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35), 0 0 16px rgba(0, 210, 196, 0.12);
            transform: translate(-50%, -50%);
            animation: heroBob 5s ease-in-out infinite;
            z-index: 12;
        }

        .hero-stage .hero-card h3 {
            margin: 0 0 4px;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 5px;
            color: #fff;
            font-weight: 600;
        }

        .hero-stage .hero-card h3 svg {
            width: 13px;
            height: 13px;
            stroke: #00D2C4;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .hero-stage .hero-card p {
            margin: 0;
            font-size: 9.5px;
            color: #A9BEBA;
            line-height: 1.35;
        }

        .hero-stage .hero-card .big {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .hero-stage .hero-card .big em {
            font-style: normal;
            color: #00D2C4;
        }

        .hero-stage .hero-card .bars {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 22px;
            margin-top: 5px;
        }

        .hero-stage .hero-card .bars i {
            flex: 1;
            border-radius: 2px 2px 0 0;
            background: linear-gradient(#00D2C4, rgba(16, 185, 129, 0.25));
        }

        .hero-stage .hero-card .tag {
            display: inline-block;
            font-size: 9px;
            padding: 1px 6px;
            border-radius: 99px;
            background: rgba(0, 210, 196, 0.14);
            color: #8FE9E1;
            margin: 4px 2px 0 0;
        }

        @keyframes heroBob {
            50% {
                margin-top: -6px;
            }
        }

        .hero-stage .c1 {
            left: 16%;
            top: 12%;
        }

        .hero-stage .c2 {
            left: 14%;
            top: 50%;
            animation-delay: 0.8s;
        }

        .hero-stage .c3 {
            left: 16%;
            top: 88%;
            animation-delay: 1.6s;
        }

        .hero-stage .c4 {
            left: 84%;
            top: 12%;
            animation-delay: 0.4s;
        }

        .hero-stage .c5 {
            left: 86%;
            top: 50%;
            animation-delay: 1.2s;
        }

        .hero-stage .c6 {
            left: 84%;
            top: 88%;
            animation-delay: 2s;
        }

        @media (max-width: 640px) {
            .hero-stage {
                height: 330px;
                max-width: 330px;
            }

            .hero-stage .hero-card {
                width: 110px;
                padding: 4px 6px;
                border-radius: 8px;
            }

            .hero-stage .hero-card h3 {
                font-size: 9.5px;
                margin-bottom: 2px;
            }

            .hero-stage .hero-card h3 svg {
                width: 10px;
                height: 10px;
            }

            .hero-stage .hero-card .big {
                font-size: 11.5px;
            }

            .hero-stage .hero-card p {
                font-size: 8.5px;
                line-height: 1.2;
            }

            .hero-stage .hero-card .bars {
                height: 14px;
                margin-top: 3px;
            }

            .hero-stage .hero-card .tag {
                font-size: 8px;
                padding: 1px 4px;
            }

            .hero-hub {
                width: 76px;
                height: 76px;
                margin: -38px 0 0 -38px;
            }

            .hero-orb.o1 {
                width: 130px;
                height: 130px;
                margin: -65px 0 0 -65px;
            }

            .hero-orb.o2 {
                width: 190px;
                height: 190px;
                margin: -95px 0 0 -95px;
            }
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
            display: none !important;
        }

        .hero-pagination .swiper-pagination-bullet {
            width: 8px;
            height: 8px;
            background: #94a3b8 !important;
            opacity: 0.6 !important;
            border-radius: 50%;
            transition: all 0.3s ease;
            cursor: pointer;
            margin: 0 4px !important;
            position: relative;
        }

        .hero-pagination .swiper-pagination-bullet-active {
            width: 26px !important;
            background: #0c4a45 !important;
            opacity: 1 !important;
            border-radius: 12px;
        }

        .hero-pagination .swiper-pagination-bullet::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 48px;
            /* লাইটহাউসের রিকোয়ারমেন্ট */
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
                height: auto !important;
                min-height: auto !important;
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
            stroke: #9CC9C2;
            stroke-width: 1.8;
            fill: none;
            opacity: 0.55;
        }

        .flow-pulse {
            stroke: #00555C;
            stroke-width: 2.4;
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
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            border: 1px solid rgba(0, 85, 92, 0.15);
            box-shadow: 0 4px 14px rgba(0, 58, 63, 0.08);
            transition: all .3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .node-circle:hover {
            border-color: #00555C;
            box-shadow: 0 0 0 6px rgba(0, 85, 92, 0.12);
            transform: translate(-50%, -50%) scale(1.12);
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
            border-radius: 12px;
            background: #00555C;
            border: 2px solid #ffffff;
            box-shadow: 0 10px 25px rgba(0, 85, 92, 0.35);
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
                'mockup' => 'Ecommerce',
                'img' => asset('images/saas/solutions/ecommerce.png'),
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
                'mockup' => 'POS',
                'img' => asset('images/saas/solutions/pos.png'),
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
                'mockup' => 'ERP',
                'img' => asset('images/saas/solutions/erp.png'),
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
                'mockup' => 'CRM',
                'img' => asset('images/saas/solutions/crm.png'),
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
                'mockup' => 'Accounting',
                'img' => asset('images/saas/solutions/accounting.png'),
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
                'mockup' => 'HRM',
                'img' => asset('images/saas/solutions/hrm.png'),
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
                'mockup' => 'Inventory',
                'img' => asset('images/saas/solutions/inventory.png'),
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
                'mockup' => 'Analytics',
                'img' => asset('images/saas/solutions/analytics.png'),
                'features' => [
                    ['icon' => 'fa-chart-line', 'text' => 'Sales Analytics'],
                    ['icon' => 'fa-chart-pie', 'text' => 'Performance Reports'],
                    ['icon' => 'fa-bolt', 'text' => 'Real-time Data'],
                    ['icon' => 'fa-eye', 'text' => 'Product Insights'],
                    ['icon' => 'fa-bullseye', 'text' => 'Business KPIs'],
                ],
            ],
        ];

        $heroSatelliteThemes = [
            // Theme 0: E-Commerce Focus
            [
                'c1' => ['title' => 'E-Commerce', 'type' => 'big', 'big' => '128', 'em' => 'orders', 'bars' => [35, 55, 45, 75, 100]],
                'c2' => ['title' => 'Accounting', 'type' => 'text', 'line1' => 'Invoice #1042 · Paid', 'line2' => 'Invoice #1043 · Due', 'line2_color' => 'text-teal-300', 'bars' => [40, 60, 50, 85, 95]],
                'c3' => ['title' => 'HRM', 'type' => 'tags', 'line1' => 'Team roster · 24', 'tags' => ['In 18', 'Leave 2']],
                'c4' => ['title' => 'POS System', 'type' => 'big', 'big' => 'Sales: 45K', 'sub' => 'Checkout #2217 · Done'],
                'c5' => ['title' => 'Inventory', 'type' => 'tags', 'line1' => 'Stock: 240 in stock', 'line2' => 'Low stock alert: 12', 'line2_color' => 'text-amber-300', 'tags' => ['Auto-reorder']],
                'c6' => ['title' => 'CRM Growth', 'type' => 'big', 'big' => '+18%', 'em' => 'active', 'bars' => [30, 45, 60, 80, 100]],
            ],
            // Theme 1: POS & Retail Speed
            [
                'c1' => ['title' => 'POS Checkout', 'type' => 'big', 'big' => '0.8s', 'em' => 'per bill', 'bars' => [50, 70, 65, 90, 100]],
                'c2' => ['title' => 'Daily Sales', 'type' => 'text', 'line1' => 'Cash: ৳78,500', 'line2' => 'Card/MFS: ৳46,200', 'line2_color' => 'text-teal-300', 'bars' => [30, 50, 65, 80, 95]],
                'c3' => ['title' => 'Cash Drawer', 'type' => 'tags', 'line1' => 'Opening: ৳5,000', 'tags' => ['Balanced', 'No Shortage']],
                'c4' => ['title' => 'Barcode Scanner', 'type' => 'big', 'big' => '1,420', 'em' => 'scanned', 'sub' => 'Instant Stock Sync'],
                'c5' => ['title' => 'Multi-Branch', 'type' => 'tags', 'line1' => '3 Branches Active', 'line2' => 'Dhanmondi, Uttara', 'line2_color' => 'text-cyan-300', 'tags' => ['Live Sync']],
                'c6' => ['title' => 'Repeat Buyers', 'type' => 'big', 'big' => '64%', 'em' => 'loyalty', 'bars' => [40, 55, 70, 85, 100]],
            ],
            // Theme 2: Financial & Accounting Mastery
            [
                'c1' => ['title' => 'Net Profit', 'type' => 'big', 'big' => '+28.4%', 'em' => 'margin', 'bars' => [45, 60, 55, 85, 100]],
                'c2' => ['title' => 'Automated Tax', 'type' => 'text', 'line1' => 'VAT Statement · Ready', 'line2' => 'AIT Reconciled', 'line2_color' => 'text-teal-300', 'bars' => [35, 50, 65, 75, 90]],
                'c3' => ['title' => 'Bank Sync', 'type' => 'tags', 'line1' => '4 Accounts Linked', 'tags' => ['Reconciled', 'Audit OK']],
                'c4' => ['title' => 'Cash Flow', 'type' => 'big', 'big' => '৳3.8M', 'em' => 'surplus', 'sub' => '99.4% Inflow Health'],
                'c5' => ['title' => 'Expense Watch', 'type' => 'tags', 'line1' => '12% Below Budget', 'line2' => 'Zero unauthorized', 'line2_color' => 'text-emerald-300', 'tags' => ['Optimized']],
                'c6' => ['title' => 'Receivables', 'type' => 'big', 'big' => '94%', 'em' => 'collected', 'bars' => [30, 45, 65, 80, 95]],
            ],
            // Theme 3: HRM, Payroll & Team Synergy
            [
                'c1' => ['title' => 'Attendance', 'type' => 'big', 'big' => '98.2%', 'em' => 'on time', 'bars' => [60, 75, 80, 92, 100]],
                'c2' => ['title' => 'Payroll Cycle', 'type' => 'text', 'line1' => 'Salary Disbursed · 100%', 'line2' => 'Payslips Sent', 'line2_color' => 'text-teal-300', 'bars' => [50, 65, 75, 88, 100]],
                'c3' => ['title' => 'Shift Roster', 'type' => 'tags', 'line1' => 'Day & Evening Shift', 'tags' => ['Active: 42', 'Leave: 2']],
                'c4' => ['title' => 'Biometric Sync', 'type' => 'big', 'big' => 'Real-time', 'sub' => 'Device Connected'],
                'c5' => ['title' => 'Provident Fund', 'type' => 'tags', 'line1' => 'All accounts synced', 'line2' => 'Interest calculated', 'line2_color' => 'text-amber-300', 'tags' => ['Automated']],
                'c6' => ['title' => 'Productivity', 'type' => 'big', 'big' => '+22%', 'em' => 'output', 'bars' => [35, 50, 60, 80, 100]],
            ],
            // Theme 4: Inventory, Warehousing & Courier
            [
                'c1' => ['title' => 'Stock Accuracy', 'type' => 'big', 'big' => '99.8%', 'em' => 'counted', 'bars' => [50, 65, 75, 90, 100]],
                'c2' => ['title' => 'Courier API', 'type' => 'text', 'line1' => 'Pathao & Steadfast', 'line2' => 'Auto Tracking Synced', 'line2_color' => 'text-teal-300', 'bars' => [40, 55, 70, 85, 95]],
                'c3' => ['title' => 'Warehouse', 'type' => 'tags', 'line1' => 'Central Hub: Dhaka', 'tags' => ['Capacity: 74%', 'Transfer: 4']],
                'c4' => ['title' => 'Delivery Rate', 'type' => 'big', 'big' => '96.5%', 'em' => 'success', 'sub' => 'Return Ratio: 3.5%'],
                'c5' => ['title' => 'Purchase Order', 'type' => 'tags', 'line1' => 'Supplier: 6 Active', 'line2' => 'Auto Re-stock Triggered', 'line2_color' => 'text-teal-300', 'tags' => ['PO #482']],
                'c6' => ['title' => 'Turnover Rate', 'type' => 'big', 'big' => '3.8x', 'em' => 'faster', 'bars' => [40, 50, 65, 85, 100]],
            ],
        ];
    @endphp

    <!-- HERO SECTION (EduManager Inspired Single Static Showcase) -->
    <section id="top" class="hero-bg relative pt-8 md:pt-36 pb-10 md:pb-20 overflow-hidden">
        <div class="mx-auto px-5 sm:px-8 max-w-[1280px] relative text-center z-10">
            <!-- Top Pill Badge -->
            <div class="hero-reveal inline-flex items-center gap-2.5 h-9 pl-3.5 pr-4 rounded-full bg-white/80 backdrop-blur ring-1 ring-black/[0.06] shadow-sm text-[14px] font-medium text-[#0A1F22] mb-6" style="transition-delay: 0ms;">
                <i class="w-2.5 h-2.5 rounded-full bg-[#00A08A] pulse"></i>
                <span>All-in-One Business Management & ERP</span>
            </div>

            <!-- Display H1 (EduManager Scale & Dual Brand Colors - Clean 2-Line Layout) -->
            <h1 class="hero-reveal hero-display-heading max-w-5xl mx-auto" style="transition-delay: 100ms;">
                <span class="block lg:whitespace-nowrap">Launch Your Complete eCommerce</span>
                <span class="hero-gradient-text block">Business in Minutes.</span>
            </h1>

            <!-- Subtitle Description -->
            <p class="hero-reveal text-base sm:text-lg lg:text-xl text-[#4B6366] max-w-3xl mx-auto mt-6 leading-relaxed" style="transition-delay: 200ms;">
                Dorja is the complete cloud ERP & POS platform for retail, wholesale, eCommerce and multi-branch businesses. Manage sales, inventory, accounting and delivery seamlessly.
            </p>

            <!-- Dual Action CTAs -->
            <div class="hero-reveal mt-9 flex flex-col sm:flex-row items-center justify-center gap-3.5" style="transition-delay: 300ms;">
                <a href="https://app.dorja.io/register"
                    class="inline-flex items-center justify-center gap-2 rounded-full font-semibold whitespace-nowrap transition-all duration-200 active:scale-[0.98] bg-[#00555C] text-white hover:bg-[#003A3F] h-[52px] px-8 text-[17px] shadow-[0_12px_30px_-6px_rgba(0,85,92,0.45)]">
                    <span>Start Free Trial</span>
                    <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                </a>
                <a href="#features"
                    class="inline-flex items-center justify-center gap-2 rounded-full font-semibold whitespace-nowrap transition-all duration-200 active:scale-[0.98] h-[52px] px-8 text-[17px] bg-white/70 text-[#00555C] hover:bg-white border border-white/90 shadow-sm">
                    <span>Explore Features</span>
                </a>
            </div>

            <!-- 3-point Checklist Row -->
            <ul class="hero-reveal mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-[15px] sm:text-[16px] text-[#2C4346] font-medium" style="transition-delay: 400ms;">
                <li class="inline-flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-[#00555C] text-white grid place-items-center text-[12px] font-bold">✓</span>
                    <span>Setup in minutes, start free</span>
                </li>
                <li class="inline-flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-[#00555C] text-white grid place-items-center text-[12px] font-bold">✓</span>
                    <span>Dedicated support & cloud backup</span>
                </li>
                <li class="inline-flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-[#00555C] text-white grid place-items-center text-[12px] font-bold">✓</span>
                    <span>Unified POS, ERP, CRM & eCommerce</span>
                </li>
            </ul>
        </div>
    </section>

    <!-- HERO PREVIEW SHOWCASE SECTION (Separate clean section without grid background) -->
    <section class="relative py-8 md:py-14 overflow-hidden">
        <div class="mx-auto px-5 sm:px-8 max-w-[1280px] relative text-center z-10">
            <!-- Section Badge & Title -->
            <div class="text-center mb-6 md:mb-8">
                <span class="hero-reveal inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/90 backdrop-blur ring-1 ring-black/[0.06] shadow-sm text-[14px] font-medium text-[#0A1F22] mb-4" style="transition-delay: 0ms;">
                    <span class="w-2 h-2 rounded-full bg-[#00A08A]"></span>
                    Interactive Product Tour
                </span>
                <h2 class="hero-reveal text-2xl md:text-4xl font-extrabold text-[#0A1F22]" style="transition-delay: 100ms;">
                    Explore How Your Daily Operations Work
                </h2>
            </div>

            <!-- 3D Perspective Showcase (Exact EduManager Reference) -->
            <div class="relative mt-4 md:mt-8 mx-auto max-w-[1080px] perspective-container">
                <!-- Glowing blur blob beneath -->
                <div class="absolute -inset-x-10 -bottom-10 top-1/3 bg-gradient-to-r from-[#00555C]/20 via-[#7CE0C3]/15 to-[#00A08A]/20 blur-3xl rounded-full pointer-events-none" aria-hidden="true"></div>

                <!-- Product Demo Showcase Card with EduManager 3D Tilt -->
                <div class="relative">
                    <div id="heroDeviceMockup" class="tilted-device-card relative group" style="transform-origin: center top; will-change: transform;">

                        <!-- Interactive Animated Product Demo Frame (Exact Dorja Demo Experience) -->
                        <div id="dorjaDemoContainer" class="w-full relative overflow-visible bg-transparent">
                            <iframe id="dorjaDemoIframe" src="{{ asset('dorja-demo.html') }}" class="w-full border-0 block bg-transparent" style="height: 680px;" title="Dorja Interactive Walkthrough" loading="lazy"></iframe>
                        </div>
                        <script>
                            (function () {
                                const iframe = document.getElementById('dorjaDemoIframe');
                                if (!iframe) return;
                                const moveCtrlToTop = () => {
                                    try {
                                        const doc = iframe.contentDocument || iframe.contentWindow.document;
                                        const stage = doc && doc.getElementById('stage');
                                        const wrap = doc && doc.getElementById('wrap');
                                        const ctrl = doc && doc.querySelector('.ctrl');
                                        if (stage && wrap && ctrl && ctrl.previousElementSibling !== null) {
                                            ctrl.style.marginTop = '0';
                                            ctrl.style.marginBottom = '20px';
                                            stage.insertBefore(ctrl, wrap);
                                        }
                                    } catch (e) {}
                                };
                                iframe.addEventListener('load', moveCtrlToTop);
                                if (iframe.contentDocument && iframe.contentDocument.readyState === 'complete') {
                                    moveCtrlToTop();
                                }
                            })();
                        </script>

                    </div>

                    <!-- 4 Floating Badges exactly like EduManager -->
                    <div style="animation-delay: 0s;" class="hidden lg:flex absolute float-slow items-center gap-2.5 h-12 pl-2.5 pr-5 rounded-2xl bg-white/90 backdrop-blur-xl ring-1 ring-black/[0.06] shadow-[0_12px_40px_-8px_rgba(0,0,0,0.18)] left-[-2%] top-[10%] z-20">
                        <span class="w-8 h-8 rounded-xl grid place-items-center text-white" style="background:#00555C">
                            <i class="fa-solid fa-cash-register text-xs"></i>
                        </span>
                        <span class="text-[15px] font-semibold text-[#0A1F22]">POS & Billing</span>
                    </div>

                    <div style="animation-delay: -1.5s;" class="hidden lg:flex absolute float-slow items-center gap-2.5 h-12 pl-2.5 pr-5 rounded-2xl bg-white/90 backdrop-blur-xl ring-1 ring-black/[0.06] shadow-[0_12px_40px_-8px_rgba(0,0,0,0.18)] right-[-3%] top-[6%] z-20">
                        <span class="w-8 h-8 rounded-xl grid place-items-center text-white" style="background:#00A08A">
                            <i class="fa-solid fa-chart-line text-xs"></i>
                        </span>
                        <span class="text-[15px] font-semibold text-[#0A1F22]">Accounting & Profit</span>
                    </div>

                    <div style="animation-delay: -3s;" class="hidden lg:flex absolute float-slow items-center gap-2.5 h-12 pl-2.5 pr-5 rounded-2xl bg-white/90 backdrop-blur-xl ring-1 ring-black/[0.06] shadow-[0_12px_40px_-8px_rgba(0,0,0,0.18)] left-[-5%] top-[52%] z-20">
                        <span class="w-8 h-8 rounded-xl grid place-items-center text-white" style="background:#B8893B">
                            <i class="fa-solid fa-boxes-stacked text-xs"></i>
                        </span>
                        <span class="text-[15px] font-semibold text-[#0A1F22]">Inventory & Stock</span>
                    </div>

                    <div style="animation-delay: -4.5s;" class="hidden lg:flex absolute float-slow items-center gap-2.5 h-12 pl-2.5 pr-5 rounded-2xl bg-white/90 backdrop-blur-xl ring-1 ring-black/[0.06] shadow-[0_12px_40px_-8px_rgba(0,0,0,0.18)] right-[-5%] top-[48%] z-20">
                        <span class="w-8 h-8 rounded-xl grid place-items-center text-white" style="background:#5B52C8">
                            <i class="fa-solid fa-truck-fast text-xs"></i>
                        </span>
                        <span class="text-[15px] font-semibold text-[#0A1F22]">Courier & Delivery</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- LOGO SHOWCASE SECTION -->
    @if ($brands->isNotEmpty())
        <section class="py-12 relative border-y border-white/40 px-4 md:px-10">
            <div class="container mx-auto max-w-[1240px]">
                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 items-center justify-items-center gap-y-10 gap-x-8 opacity-80 hover:opacity-100 transition-all duration-500">
                    @forelse($brands as $brand)
                        <div class="w-full flex justify-center">
                            <a href="{{ $brand->link }}" target="_blank" class="block grayscale hover:grayscale-0 transition-all duration-300">
                                <img src="{{ $brand->logo_url ?? '' }}" alt="{{ $brand->name }}"
                                    class="h-8 md:h-10 lg:h-12 object-contain cursor-pointer" />
                            </a>
                        </div>
                    @empty
                        <div class="col-span-full text-[#6F8789] text-sm italic">No active brands available</div>
                    @endforelse
                </div>
            </div>
        </section>
    @endif
    <section id="features" class="py-8 md:py-14 px-4 md:px-10">
        <div class="container mx-auto max-w-[1240px]">

            <!-- Header -->
            <div class="text-center mb-8">
                <span
                    class="hero-reveal inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/90 backdrop-blur ring-1 ring-black/[0.06] shadow-sm text-[14px] font-medium text-[#0A1F22] mb-6" style="transition-delay: 0ms;">
                    <span class="w-2 h-2 rounded-full bg-[#00A08A]"></span>
                    All-in-One Solution
                </span>

                <h2 class="hero-reveal text-2xl md:text-4xl font-extrabold text-[#0A1F22] mb-4" style="transition-delay: 100ms;">
                    From Start to Growth — Everything in One System
                </h2>

                <!-- Tabs -->
                <div class="flex justify-center mb-7">
                    <div class="flex flex-wrap items-center justify-center gap-2" id="solution-tabs" role="tablist" aria-label="Modules">
                        @foreach ($solutions as $key => $sol)
                            <button type="button" role="tab" onclick="switchSolution('{{ $key }}', this)"
                                class="sol-tab-btn whitespace-nowrap {{ $loop->first ? 'active' : '' }}">
                                {{ $sol['title'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Content Card (Glass Card) -->
            <div class="glass border border-white/85 rounded-[28px] p-6 md:p-12 pb-16 md:pb-24 shadow-[0_18px_40px_-24px_rgba(0,58,63,0.35)] relative overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 md:gap-10 items-center relative z-10">

                    <!-- Left Content -->
                    <div class="lg:col-span-5 relative flex flex-col justify-between lg:min-h-[480px]">
                        <div>
                            <h3 id="sol-title" class="text-2xl md:text-3xl font-extrabold text-[#0A1F22] mb-5 relative z-10"></h3>

                            <p id="sol-desc" class="text-[#4B6366] text-base md:text-lg leading-relaxed mb-8 max-w-md relative z-10"></p>

                            <!-- Features -->
                            <div id="sol-features" class="space-y-4 mb-8 relative z-10"></div>

                            <a href="https://app.dorja.io/register"
                                class="btnp inline-flex items-center gap-2.5 bg-[#00555C] hover:bg-[#003A3F] text-white px-7 py-3.5 rounded-full font-semibold text-base transition-all shadow-[0_14px_30px_-14px_rgba(0,85,92,0.7)] relative z-10">
                                <span>Explore More</span>
                                <svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                            </a>
                        </div>

                        <!-- Watermark moved to card bottom -->
                    </div>

                    <!-- Right Interactive Coding Mockup (Instead of Static Image) -->
                    <div class="lg:col-span-7">
                        <div class="bw relative rounded-2xl overflow-hidden bg-white border border-[#e4e9ea] shadow-[0_30px_70px_-25px_rgba(0,58,63,0.3)]" id="solBw">
                            <div class="bw-bar h-[42px] bg-[#f1f4f5] border-b border-[#e4e9ea] flex items-center gap-3 px-3.5">
                                <div class="bw-dots flex gap-1.5">
                                    <i class="w-2.5 h-2.5 rounded-full bg-[#ff5f57] block"></i>
                                    <i class="w-2.5 h-2.5 rounded-full bg-[#febc2e] block"></i>
                                    <i class="w-2.5 h-2.5 rounded-full bg-[#28c840] block"></i>
                                </div>
                                <div class="bw-url flex-1 max-w-[420px] mx-auto h-[26px] rounded-md bg-white border border-[#e4e9ea] flex items-center gap-2 px-2.5 text-xs text-[#475569] overflow-hidden whitespace-nowrap">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    <span id="solBwUrl" class="font-mono text-[11.5px]">app.dorja.io/dashboard</span>
                                </div>
                            </div>
                            <div class="bw-body relative w-full aspect-[16/10.5] min-h-[240px] sm:min-h-[300px] md:min-h-[480px] bg-[#f6f6f6] overflow-hidden" id="solBwBody">
                                <div class="ld absolute inset-0 grid place-items-center text-xs text-[#5d6f75]">Loading preview…</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-center gap-2 mt-3 text-xs text-[#5d6f75]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m4 4 7.07 17 2.51-7.39L21 11.07z"></path></svg>
                            <span>Live preview of the real dashboard. Hover and scroll inside it.</span>
                        </div>
                    </div>

                </div>

                <!-- Watermark Outline Text — absolute bottom of glass card, clipped by overflow-hidden -->
                <span id="sol-watermark" aria-hidden="true"
                    class="absolute left-6 md:left-12 bottom-[8px] md:bottom-[36px] font-black leading-none whitespace-nowrap pointer-events-none select-none transition-all duration-300 z-0"
                    style="font-size: clamp(52px, 8.5vw, 115px); color: transparent; -webkit-text-stroke: 2px rgba(0, 85, 92, 0.22); letter-spacing: -0.05em;">Ecommerce</span>

            </div>

        </div>
    </section>

    <!-- INTEGRATION SECTION -->
    <section class="py-8 md:py-14 px-4 md:px-10 overflow-hidden relative" id="intergation">
        <div class="container mx-auto max-w-[1240px]">

            <!-- Section Header -->
            <div class="text-center mb-12">
                <span class="hero-reveal inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/90 backdrop-blur ring-1 ring-black/[0.06] shadow-sm text-[14px] font-medium text-[#0A1F22] mb-4" style="transition-delay: 0ms;">
                    <span class="w-2 h-2 rounded-full bg-[#00A08A]"></span>
                    Smart Integration
                </span>
                <h2 class="hero-reveal text-3xl md:text-5xl font-bold text-[#0A1F22] mb-4 tracking-tight" style="transition-delay: 100ms;">
                    Track and automate your entire business
                </h2>
                <p class="text-base md:text-lg text-[#4B6366] max-w-2xl mx-auto">
                    Seamlessly connect your payment gateways, couriers, sales channels, and communication tools into one unified workflow.
                </p>
            </div>

            <div class="pt-6">
                <!-- Top 2 Feature Showcase Cards (Professional Online Store & Accurate Ad Tracking) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">

                    <!-- Left Card: Professional online store -->
                    <div class="hero-reveal glass rounded-3xl overflow-hidden shadow-sm relative border border-white/80 lift flex flex-col">
                        <div class="relative bg-gradient-to-br from-[#EEF2FF]/80 via-[#F5F8FF]/70 to-[#E6F4F1]/80 p-5 md:p-7 min-h-[300px] sm:min-h-[330px] flex items-center justify-center overflow-hidden">
                            <!-- Mini Interactive Product Details Browser Window -->
                            <div class="w-full max-w-[440px] mx-auto bg-white rounded-2xl border border-gray-200/80 shadow-lg overflow-hidden relative z-10">
                                <div class="h-7 bg-gray-50 border-b border-gray-100 flex items-center gap-1.5 px-3">
                                    <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    <span class="ml-auto mr-auto text-[10px] text-gray-400 font-mono">dorja.io/product/cotton-tee</span>
                                </div>
                                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3.5 items-start">
                                    <!-- Product Image & Color Thumbnails -->
                                    <div class="space-y-2">
                                        <div class="bg-gray-50 rounded-xl p-2.5 flex items-center justify-center border border-gray-100" style="height: 118px;">
                                            <svg id="miniStoreTee" viewBox="0 0 64 64" class="w-16 h-16 drop-shadow-sm transition-colors duration-200">
                                                <path id="miniStoreTeePath" d="M20 12 L26 10 Q32 15 38 10 L44 12 L54 22 L47 29 L43 25 L43 54 L21 54 L21 25 L17 29 L10 22 Z" fill="#00555C"/>
                                            </svg>
                                        </div>
                                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;">
                                            <button type="button" onclick="document.getElementById('miniStoreTeePath').setAttribute('fill','#00555C')" class="h-6 rounded-md bg-[#00555C]/15 border border-[#00555C] flex items-center justify-center cursor-pointer" title="Teal">
                                                <span class="w-3 h-3 rounded-full bg-[#00555C]"></span>
                                            </button>
                                            <button type="button" onclick="document.getElementById('miniStoreTeePath').setAttribute('fill','#1D4ED8')" class="h-6 rounded-md bg-blue-50 border border-gray-200 hover:border-blue-500 flex items-center justify-center cursor-pointer" title="Royal Blue">
                                                <span class="w-3 h-3 rounded-full" style="background-color: #1D4ED8;"></span>
                                            </button>
                                            <button type="button" onclick="document.getElementById('miniStoreTeePath').setAttribute('fill','#1F2937')" class="h-6 rounded-md bg-gray-100 border border-gray-200 hover:border-gray-600 flex items-center justify-center cursor-pointer" title="Charcoal">
                                                <span class="w-3 h-3 rounded-full" style="background-color: #1F2937;"></span>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Product Details Info -->
                                    <div class="space-y-2 text-left min-w-0">
                                        <div class="text-sm font-bold text-[#0A1F22] leading-snug">Premium Cotton Tee</div>
                                        <div class="flex items-center gap-1 text-[10px] text-amber-500 font-semibold">
                                            <span>★★★★★</span>
                                            <span class="text-gray-400 font-normal">4.9 (128)</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-base font-extrabold text-[#00555C]">৳850</span>
                                            <span class="text-[11px] text-gray-400 line-through">৳1,200</span>
                                            <span class="text-[9px] font-bold bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">-29%</span>
                                        </div>
                                        <div class="flex gap-1.5" id="miniSizeGroup">
                                            <button type="button" onclick="this.parentElement.querySelectorAll('button').forEach(b=>{b.className='px-2 py-0.5 text-[10px] border border-gray-200 rounded cursor-pointer text-gray-600'});this.className='px-2 py-0.5 text-[10px] bg-[#0A1F22] text-white rounded font-semibold cursor-pointer'" class="px-2 py-0.5 text-[10px] border border-gray-200 rounded cursor-pointer text-gray-600">S</button>
                                            <button type="button" onclick="this.parentElement.querySelectorAll('button').forEach(b=>{b.className='px-2 py-0.5 text-[10px] border border-gray-200 rounded cursor-pointer text-gray-600'});this.className='px-2 py-0.5 text-[10px] bg-[#0A1F22] text-white rounded font-semibold cursor-pointer'" class="px-2 py-0.5 text-[10px] border border-gray-200 rounded cursor-pointer text-gray-600">M</button>
                                            <button type="button" onclick="this.parentElement.querySelectorAll('button').forEach(b=>{b.className='px-2 py-0.5 text-[10px] border border-gray-200 rounded cursor-pointer text-gray-600'});this.className='px-2 py-0.5 text-[10px] bg-[#0A1F22] text-white rounded font-semibold cursor-pointer'" class="px-2 py-0.5 text-[10px] bg-[#0A1F22] text-white rounded font-semibold cursor-pointer">L</button>
                                            <button type="button" onclick="this.parentElement.querySelectorAll('button').forEach(b=>{b.className='px-2 py-0.5 text-[10px] border border-gray-200 rounded cursor-pointer text-gray-600'});this.className='px-2 py-0.5 text-[10px] bg-[#0A1F22] text-white rounded font-semibold cursor-pointer'" class="px-2 py-0.5 text-[10px] border border-gray-200 rounded cursor-pointer text-gray-600">XL</button>
                                        </div>
                                        <button id="miniOrderBtn" type="button" onclick="this.textContent='✓ Order placed';this.classList.remove('bg-[#00555C]');this.classList.add('bg-emerald-600');" class="w-full py-2 rounded-lg bg-[#00555C] hover:bg-[#003A3F] text-white text-[11px] font-semibold text-center transition-colors cursor-pointer">
                                            Order Now • ৳850
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/80 text-[#3E5759] p-6 text-left text-sm md:text-base border-t border-white/80 flex-grow">
                            <h3 class="text-[#0A1F22] text-xl md:text-2xl font-bold mb-2">Professional online store</h3>
                            Pick a theme, add your products and publish your store and landing pages, with no code. Shoppers who come from Facebook can see the product and order in a few taps.
                        </div>
                    </div>
                    <script>
                        (function () {
                            const colors = ['#00555C', '#1D4ED8', '#1F2937'];
                            const teePath = document.getElementById('miniStoreTeePath');
                            const sizeGroup = document.getElementById('miniSizeGroup');
                            const orderBtn = document.getElementById('miniOrderBtn');
                            if (!teePath || !sizeGroup || !orderBtn) return;

                            let step = 0;
                            let paused = false;
                            const card = orderBtn.closest('.glass');
                            if (card) {
                                card.addEventListener('mouseenter', () => paused = true);
                                card.addEventListener('mouseleave', () => paused = false);
                            }

                            setInterval(() => {
                                if (paused) return;
                                const sizeBtns = sizeGroup.querySelectorAll('button');
                                const phase = step % 4;
                                if (phase === 0) {
                                    orderBtn.textContent = 'Order Now • ৳850';
                                    orderBtn.classList.remove('bg-emerald-600');
                                    orderBtn.classList.add('bg-[#00555C]');
                                    teePath.setAttribute('fill', colors[Math.floor(step / 4) % colors.length]);
                                } else if (phase === 1) {
                                    const idx = Math.floor(step / 4) % sizeBtns.length;
                                    sizeBtns.forEach(b => b.className = 'px-2 py-0.5 text-[10px] border border-gray-200 rounded cursor-pointer text-gray-600');
                                    sizeBtns[idx].className = 'px-2 py-0.5 text-[10px] bg-[#0A1F22] text-white rounded font-semibold cursor-pointer';
                                } else if (phase === 2) {
                                    orderBtn.textContent = '✓ Order placed';
                                    orderBtn.classList.remove('bg-[#00555C]');
                                    orderBtn.classList.add('bg-emerald-600');
                                }
                                step++;
                            }, 1100);
                        })();
                    </script>

                    <!-- Right Card: Accurate ad tracking -->
                    <div class="hero-reveal glass rounded-3xl overflow-hidden shadow-sm relative border border-white/80 lift flex flex-col">
                        <div class="relative bg-gradient-to-br from-[#F0F4FF]/80 via-[#F7F9FC]/70 to-[#E6F4F1]/80 p-5 md:p-6 min-h-[300px] sm:min-h-[330px] flex flex-col justify-center gap-3">
                            <!-- Top Row: Live Events + ROAS -->
                            <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 12px;">
                                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-[9px] font-bold text-gray-500 uppercase tracking-wider">Live Events</span>
                                        <span class="text-[8px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded-full">• LIVE</span>
                                    </div>
                                    <div class="mt-2.5 space-y-1.5 text-[10px] text-gray-600">
                                        <div class="flex justify-between gap-2"><span>Purchase • ৳850</span><span class="text-emerald-600 font-semibold whitespace-nowrap">2s ago</span></div>
                                        <div class="flex justify-between gap-2"><span>InitiateCheckout</span><span class="text-gray-400 whitespace-nowrap">14s ago</span></div>
                                    </div>
                                </div>
                                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-3 flex flex-col justify-between">
                                    <div class="text-[9px] font-bold text-gray-400 uppercase">ROAS</div>
                                    <div class="text-xl font-extrabold text-[#0A1F22]">5.0x</div>
                                </div>
                            </div>

                            <!-- Bottom Box: Pixels & Tracking -->
                            <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-3">
                                <div class="flex items-center justify-between text-[9px] font-bold text-gray-400 uppercase pb-2 border-b border-gray-100">
                                    <span>Pixels &amp; Tracking</span>
                                    <span>Last Event</span>
                                </div>
                                <div class="divide-y divide-gray-100 text-[11px]">
                                    <div class="py-1.5 flex items-center justify-between">
                                        <span class="font-semibold text-gray-800">Meta Pixel <span class="ml-1 text-[8px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">Ready</span></span>
                                        <span class="text-emerald-600 text-[10px]">✓</span>
                                    </div>
                                    <div class="py-1.5 flex items-center justify-between">
                                        <span class="font-semibold text-gray-800">Conversions API <span class="ml-1 text-[8px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">Ready</span></span>
                                        <span class="text-emerald-600 text-[10px]">✓</span>
                                    </div>
                                    <div class="py-1.5 flex items-center justify-between">
                                        <span class="font-semibold text-gray-800">TikTok Pixel <span class="ml-1 text-[8px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">Ready</span></span>
                                        <span class="text-emerald-600 text-[10px]">✓</span>
                                    </div>
                                    <div class="py-1.5 flex items-center justify-between">
                                        <span class="font-semibold text-gray-800">Google Tag Manager <span class="ml-1 text-[8px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">Ready</span></span>
                                        <span class="text-emerald-600 text-[10px]">✓</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/80 text-[#3E5759] p-6 text-left text-sm md:text-base border-t border-white/80 flex-grow">
                            <h3 class="text-[#0A1F22] text-xl md:text-2xl font-bold mb-2">Accurate ad tracking</h3>
                            Meta Pixel, Conversions API, TikTok Pixel and Google Tag Manager are built in. Every order reaches Meta and TikTok through server-side tracking, so they can show your ads to more people who buy.
                        </div>
                    </div>

                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- নতুন কার্ড: Smart Order & Inventory Management -->
                    <div class="hero-reveal glass rounded-3xl overflow-hidden shadow-sm relative group border border-white/80 lift flex flex-col">
                        <div class="relative bg-gradient-to-br from-[#E6F4F1]/80 via-[#F4F9F8]/70 to-[#EEF2FF]/80 p-4 md:p-6 min-h-[260px] flex flex-col justify-center gap-3">
                            <!-- Order Pipeline Card -->
                            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-3.5 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Order Pipeline</span>
                                    <span class="text-[10px] font-bold text-[#00555C] bg-[#DDEEEA] px-2 py-0.5 rounded-full">Auto Sync</span>
                                </div>
                                <div class="space-y-2 text-[11px]">
                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-2.5 py-1.5">
                                        <span class="font-semibold text-[#0A1F22]">#DRJ-1048 • ৳1,450</span>
                                        <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[9px] font-bold">Pending</span>
                                    </div>
                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-2.5 py-1.5">
                                        <span class="font-semibold text-[#0A1F22]">#DRJ-1047 • ৳850</span>
                                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[9px] font-bold">Shipped</span>
                                    </div>
                                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-2.5 py-1.5">
                                        <span class="font-semibold text-[#0A1F22]">#DRJ-1046 • ৳2,100</span>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[9px] font-bold">Delivered</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Live Stock & Invoice Mini Bar -->
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-2.5">
                                    <div class="text-[9px] text-gray-400 font-semibold uppercase">Live Stock</div>
                                    <div class="text-sm font-extrabold text-[#00555C] mt-0.5">1,284 pcs</div>
                                </div>
                                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm p-2.5">
                                    <div class="text-[9px] text-gray-400 font-semibold uppercase">Auto Invoice</div>
                                    <div class="text-sm font-extrabold text-emerald-600 mt-0.5">✓ Ready</div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/80 text-[#3E5759] p-6 text-left text-sm md:text-base border-t border-white/80 flex-grow">
                            <h3 class="text-[#00555C] text-2xl font-bold mb-2">Smart Order &amp; Inventory</h3>
                            Process orders from pending to delivery in one click, sync stock levels in real time across warehouses, and generate automated invoices effortlessly.
                        </div>
                    </div>

                    <!-- গ্রাফ ১: পেমেন্ট ও কুরিয়ার মেথড (বামে Payment, মাঝে Dorja, ডানে Courier) -->
                    <div class="hero-reveal glass rounded-3xl overflow-hidden shadow-sm relative group border border-white/80 lift flex flex-col">
                        <div class="relative bg-white/40 p-4 md:p-6">
                            <svg viewBox="0 0 460 300" class="w-full h-auto max-h-[340px]">
                                <!-- Left 4 Input Paths (Payment) -->
                                <path class="flow-line" d="M60 40 C 150 40, 170 150, 230 150" />
                                <path class="flow-line" d="M60 113 C 150 113, 170 150, 230 150" />
                                <path class="flow-line" d="M60 186 C 150 186, 170 150, 230 150" />
                                <path class="flow-line" d="M60 260 C 150 260, 170 150, 230 150" />

                                <!-- Left Pulse Animation -->
                                <path class="flow-pulse" d="M60 40 C 150 40, 170 150, 230 150" />
                                <path class="flow-pulse" d="M60 113 C 150 113, 170 150, 230 150" style="animation-delay:.4s" />
                                <path class="flow-pulse" d="M60 186 C 150 186, 170 150, 230 150" style="animation-delay:.8s" />
                                <path class="flow-pulse" d="M60 260 C 150 260, 170 150, 230 150" style="animation-delay:1.2s" />

                                <!-- Right 4 Input Paths (Courier) -->
                                <path class="flow-line" d="M400 40 C 310 40, 290 150, 230 150" />
                                <path class="flow-line" d="M400 113 C 310 113, 290 150, 230 150" />
                                <path class="flow-line" d="M400 186 C 310 186, 290 150, 230 150" />
                                <path class="flow-line" d="M400 260 C 310 260, 290 150, 230 150" />

                                <!-- Right Pulse Animation -->
                                <path class="flow-pulse" d="M400 40 C 310 40, 290 150, 230 150" style="animation-delay:.2s" />
                                <path class="flow-pulse" d="M400 113 C 310 113, 290 150, 230 150" style="animation-delay:.6s" />
                                <path class="flow-pulse" d="M400 186 C 310 186, 290 150, 230 150" style="animation-delay:1.0s" />
                                <path class="flow-pulse" d="M400 260 C 310 260, 290 150, 230 150" style="animation-delay:1.4s" />

                                <!-- সেন্ট্রাল পালস রিং -->
                                <circle class="ring-pulse" cx="230" cy="150" r="45" fill="none" stroke="#00555c" stroke-width="1.5" />
                            </svg>

                            <!-- Left: 4 Payment Source Logos -->
                            <div class="node-circle small" style="left:13%; top:13.3%;"><img src="{{ asset('./images/saas/nagad.png') }}" alt="Nagad"></div>
                            <div class="node-circle small" style="left:13%; top:37.6%;"><img src="{{ asset('./images/saas/bkash.png') }}" alt="bKash"></div>
                            <div class="node-circle small" style="left:13%; top:62.1%;"><img src="{{ asset('./images/saas/rocket.png') }}" alt="Rocket"></div>
                            <div class="node-circle small" style="left:13%; top:86.7%;"><img src="{{ asset('./images/saas/ssl.png') }}" alt="SSLCommerz"></div>

                            <!-- Right: 4 Courier Partner Logos -->
                            <div class="node-circle small" style="left:87%; top:13.3%;"><img src="{{ asset('./images/saas/steadfast.svg') }}" alt="Steadfast"></div>
                            <div class="node-circle small" style="left:87%; top:37.6%;"><img src="{{ asset('./images/saas/pathao.svg') }}" alt="Pathao"></div>
                            <div class="node-circle small" style="left:87%; top:62.1%;"><img src="{{ asset('./images/saas/redx.svg') }}" alt="RedX"></div>
                            <div class="node-circle small" style="left:87%; top:86.7%;"><img src="{{ asset('./images/saas/carrybee.png') }}" alt="CarryBee"></div>

                            <!-- Center Node: Dorja Icon -->
                            <div class="center-node" style="background: #ffffff; border: 2px solid #00555C; width: 60px; height: 60px; padding: 0; border-radius: 16px;">
                                <img src="{{ asset('images/saas/dorja-favicon.png') }}" alt="Dorja" class="w-8 h-8 object-contain">
                            </div>
                        </div>
                        <div class="bg-white/80 text-[#3E5759] p-6 text-left text-sm md:text-base border-t border-white/80 flex-grow">
                            <h3 class="text-[#00555C] text-2xl font-bold mb-2">Multiple Payment Gateways &amp; Courier Partners</h3>
                            Integrate leading payment gateways (bKash, Nagad, Rocket, SSLCommerz) and trusted courier services (Pathao, SteadFast, CarryBee, RedX) in one place. Accept secure payments, create shipments, and track deliveries seamlessly from dorja.io.
                        </div>
                    </div>
                    <!-- গ্রাফ ২: ওমনিচ্যানেল মেসেজিং ও সেলস চ্যানেল (বামে Messaging, মাঝে Dorja, ডানে Sales Channels) -->
                    <div class="hero-reveal glass rounded-3xl overflow-hidden shadow-sm relative group border border-white/80 lift flex flex-col">
                        <div class="relative bg-white/40 p-4 md:p-6">
                            <svg viewBox="0 0 460 300" class="w-full h-auto max-h-[340px]">
                                <!-- Left 4 Input Paths (Messaging) -->
                                <path class="flow-line" d="M60 40 C 150 40, 170 150, 230 150" />
                                <path class="flow-line" d="M60 113 C 150 113, 170 150, 230 150" />
                                <path class="flow-line" d="M60 186 C 150 186, 170 150, 230 150" />
                                <path class="flow-line" d="M60 260 C 150 260, 170 150, 230 150" />

                                <!-- Left Pulse Animation -->
                                <path class="flow-pulse" d="M60 40 C 150 40, 170 150, 230 150" />
                                <path class="flow-pulse" d="M60 113 C 150 113, 170 150, 230 150" style="animation-delay:.4s" />
                                <path class="flow-pulse" d="M60 186 C 150 186, 170 150, 230 150" style="animation-delay:.8s" />
                                <path class="flow-pulse" d="M60 260 C 150 260, 170 150, 230 150" style="animation-delay:1.2s" />

                                <!-- Right 4 Input Paths (Sales Channels) -->
                                <path class="flow-line" d="M400 40 C 310 40, 290 150, 230 150" />
                                <path class="flow-line" d="M400 113 C 310 113, 290 150, 230 150" />
                                <path class="flow-line" d="M400 186 C 310 186, 290 150, 230 150" />
                                <path class="flow-line" d="M400 260 C 310 260, 290 150, 230 150" />

                                <!-- Right Pulse Animation -->
                                <path class="flow-pulse" d="M400 40 C 310 40, 290 150, 230 150" style="animation-delay:.2s" />
                                <path class="flow-pulse" d="M400 113 C 310 113, 290 150, 230 150" style="animation-delay:.6s" />
                                <path class="flow-pulse" d="M400 186 C 310 186, 290 150, 230 150" style="animation-delay:1.0s" />
                                <path class="flow-pulse" d="M400 260 C 310 260, 290 150, 230 150" style="animation-delay:1.4s" />

                                <!-- সেন্ট্রাল পালস রিং -->
                                <circle class="ring-pulse" cx="230" cy="150" r="45" fill="none" stroke="#00555c" stroke-width="1.5" />
                            </svg>

                            <!-- Left: 4 Messaging Channel Logos -->
                            <div class="node-circle small" style="left:13%; top:13.3%;"><img src="{{ asset('./images/saas/whatsapp.svg') }}" alt="WhatsApp"></div>
                            <div class="node-circle small" style="left:13%; top:37.6%;"><img src="{{ asset('./images/saas/fb.png') }}" alt="Facebook"></div>
                            <div class="node-circle small" style="left:13%; top:62.1%;"><img src="{{ asset('./images/saas/inst.png') }}" alt="Instagram"></div>
                            <div class="node-circle small" style="left:13%; top:86.7%;"><img src="{{ asset('./images/saas/live.png') }}" alt="Live Support"></div>

                            <!-- Right: 4 Sales Channel Logos -->
                            <div class="node-circle small" style="left:87%; top:13.3%;"><img src="{{ asset('./images/saas/ecim.png') }}" alt="e-commerce"></div>
                            <div class="node-circle small" style="left:87%; top:37.6%;"><img src="{{ asset('./images/saas/landing.png') }}" alt="Landing Page"></div>
                            <div class="node-circle small" style="left:87%; top:62.1%;"><img src="{{ asset('./images/saas/woo.png') }}" alt="Woocommerce"></div>
                            <div class="node-circle small" style="left:87%; top:86.7%;"><img src="{{ asset('./images/saas/daraz.png') }}" alt="Daraz"></div>

                            <!-- Center Node: Dorja Icon -->
                            <div class="center-node" style="background: #ffffff; border: 2px solid #00555C; width: 60px; height: 60px; padding: 0; border-radius: 16px;">
                                <img src="{{ asset('images/saas/dorja-favicon.png') }}" alt="Dorja" class="w-8 h-8 object-contain">
                            </div>
                        </div>
                        <div class="bg-white/80 text-[#3E5759] p-6 text-left text-sm md:text-base border-t border-white/80 flex-grow">
                            <h3 class="text-[#00555C] text-2xl font-bold mb-2">Omnichannel Messaging &amp; Sales Channels</h3>
                            Handle customer inquiries from WhatsApp, Facebook, Instagram, and Live Chat while managing orders from your Website, Landing Pages, WooCommerce, and Daraz in one unified dashboard.
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
                    class="inline-block px-6 py-2 rounded-full border border-[#00555C]/20 bg-[#DDEEEA] text-[#00555C] font-semibold text-base mb-6">
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
                <div class="bg-white border border-white/80 rounded-2xl p-8 md:p-14">
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
                                    alt="Nagad" onerror="
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
                <div class="bg-white border border-white/80 rounded-2xl p-8 md:p-14">
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
                <div class="bg-white border border-white/80 rounded-2xl p-8 md:p-14">
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
                <div class="bg-white border border-white/80 rounded-2xl p-8 md:p-14">
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
        <section class="py-16 px-4 md:px-10 relative">
            <div class="container mx-auto max-w-[1240px]">
                <!-- Section Header -->
                <div class="text-center mb-14">
                    <span class="hero-reveal inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/90 backdrop-blur ring-1 ring-black/[0.06] shadow-sm text-[14px] font-medium text-[#0A1F22] mb-4" style="transition-delay: 0ms;">
                        <span class="w-2 h-2 rounded-full bg-[#00A08A]"></span>
                        Features
                    </span>
                    <h2 class="hero-reveal text-3xl md:text-5xl font-bold text-[#0A1F22] mb-4 tracking-tight" style="transition-delay: 100ms;">
                        Everything you need for your business <br class="hidden md:block" />
                        now in one place
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($topFeatures as $feature)
                        <div class="hero-reveal" style="transition-delay: {{ ($loop->index % 4) * 80 }}ms;">
                            <a href="{{ route('saas.feature.details', $feature->slug) }}"
                                class="glass shine flex items-center gap-4 p-5 group rounded-2xl border border-white/80 lift transition-all duration-300">

                                <!-- Left Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-14 h-14 rounded-xl bg-white/70 border border-white/80 flex items-center justify-center text-[#00555C]">
                                        @if ($feature->image_url)
                                            <img src="{{ $feature->image_url ?? asset('images/saas/live1.png') }}" class="w-10 h-10 object-contain"
                                                alt="{{ $feature->title }}" />
                                        @else
                                            <i class="{{ $feature->icon ?? 'fa-solid fa-file-lines' }} text-xl text-[#00555C]"></i>
                                        @endif
                                    </div>
                                </div>

                                <!-- Right Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="block truncate text-lg font-semibold text-[#0A1F22] group-hover:text-[#00555C] transition-colors">
                                            {{ $feature->title }}
                                        </span>
                                        <span aria-hidden="true" style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #8AA3A0;" class="shrink-0 font-medium">
                                            {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>

                                    <span class="inline-flex items-center gap-1.5 font-semibold text-xs text-[#00555C]">
                                        <span>Read More</span>
                                        <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition-transform"></i>
                                    </span>
                                </div>

                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 text-center">
                    <a href="https://app.dorja.io/register"
                        class="btnp inline-block bg-[#00555C] hover:bg-[#003A3F] text-white px-8 py-3.5 rounded-full font-semibold text-[17px] transition shadow-lg shadow-teal-900/20">
                        Start Free Trial
                    </a>
                </div>
            </div>
        </section>
    @endif

    <!-- SOLUTION SECTION -->
    <!-- DEMO & TEMPLATE SECTION -->
    @php
        $displayDemos = ($demos && $demos->isNotEmpty()) ? $demos : collect([
            (object)['id' => 1, 'type' => 1, 'title' => 'Landing Template 1 (Classic)', 'link' => url('/landing-preview/1'), 'image_url' => asset('images/saas/landing1.png')],
            (object)['id' => 2, 'type' => 1, 'title' => 'Landing Template 2 (Modern Gadget)', 'link' => url('/landing-preview/2'), 'image_url' => asset('images/saas/landing2.png')],
            (object)['id' => 3, 'type' => 1, 'title' => 'Landing Template 3 (Beauty & Cosmetics)', 'link' => url('/landing-preview/3'), 'image_url' => asset('images/saas/landing3.png')],
            (object)['id' => 4, 'type' => 1, 'title' => 'Landing Template 4 (Multi Product)', 'link' => url('/landing-preview/4'), 'image_url' => asset('images/saas/landing4.png')],
            (object)['id' => 5, 'type' => 1, 'title' => 'Landing Template 5 (High Conversion)', 'link' => url('/landing-preview/5'), 'image_url' => asset('images/saas/landingcustom.png')],
            (object)['id' => 6, 'type' => 1, 'title' => 'Landing Template 6 (Mega Storefront)', 'link' => url('/landing-preview/6'), 'image_url' => asset('images/saas/landing1.png')],
            (object)['id' => 7, 'type' => 2, 'title' => 'Theme 1 (Modern Minimalist)', 'link' => url('/theme-preview/1'), 'image_url' => asset('images/ecom/ecom1.png')],
            (object)['id' => 8, 'type' => 2, 'title' => 'Theme 2 (Ocean Lifestyle)', 'link' => url('/theme-preview/2'), 'image_url' => asset('images/ecom/ecom2.png')],
            (object)['id' => 9, 'type' => 2, 'title' => 'Theme 3 (Organic Nature)', 'link' => url('/theme-preview/3'), 'image_url' => asset('images/ecom/ecom3.png')],
            (object)['id' => 10, 'type' => 2, 'title' => 'Theme 4 (Urban Streetwear)', 'link' => url('/theme-preview/4'), 'image_url' => asset('images/ecom/ecom4.png')],
            (object)['id' => 11, 'type' => 2, 'title' => 'Theme 5 (Luxury Boutique)', 'link' => url('/theme-preview/5'), 'image_url' => asset('images/ecom/ecom5.png')],
        ]);
    @endphp
    @if ($displayDemos->isNotEmpty())
        <style>
            .demo-card {
                border-radius: 24px;
                display: flex;
                flex-direction: column;
                height: 100%;
                background-color: rgba(255, 255, 255, 0.85);
                backdrop-filter: blur(8px);
                border: 1px solid rgba(255, 255, 255, 0.9);
                box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
                transition: box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 1.1s cubic-bezier(0.22, 1, 0.36, 1), transform 1.1s cubic-bezier(0.22, 1, 0.36, 1);
                cursor: pointer;
            }
            .demo-card:hover {
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            }
            .demo-image-frame {
                position: relative;
                display: block;
                height: 340px;
                max-height: 340px;
                min-height: 340px;
                overflow: hidden;
                border-radius: 16px;
                margin: 10px;
                background-color: #E9F1EF;
                border: 1px solid rgba(0, 0, 0, 0.04);
                cursor: pointer;
            }
            .demo-preview-img {
                display: block;
                width: 100%;
                height: auto;
                min-height: 100%;
                object-fit: cover;
                object-position: top;
                transform: translateY(0);
                transition: transform 9000ms cubic-bezier(0.25, 1, 0.5, 1);
                will-change: transform;
            }
            .demo-card:hover .demo-preview-img,
            .demo-image-frame:hover .demo-preview-img {
                transform: translateY(calc(-100% + 340px));
            }
            #demo-tab-container {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                box-shadow: 0 4px 20px -2px rgba(0, 85, 92, 0.08);
            }
        </style>
        <section class="py-20 px-4 md:px-10 relative" id="demo-section" style="background: #0A1F22; color: #fff;">
            <div class="container mx-auto max-w-[1240px]">
                <!-- Header with Redesign Alignment (Title on Left, Pill Tabs on Right) -->
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12">
                    <div class="max-w-2xl">
                        <span class="hero-reveal inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/90 backdrop-blur ring-1 ring-black/[0.06] shadow-sm text-[14px] font-medium text-[#0A1F22] mb-4" style="transition-delay: 0ms;">
                            <span class="w-2 h-2 rounded-full bg-[#00A08A]"></span>
                            Demo &amp; Template
                        </span>
                        <h2 class="hero-reveal text-3xl md:text-5xl font-bold text-white tracking-tight leading-tight" style="transition-delay: 100ms;">
                            Get a complete system live experience on one platform
                        </h2>
                    </div>

                    <!-- Tabs Pill Switcher with Smooth Glider (Matching Header Glider) -->
                    <div role="tablist" aria-label="Templates" class="relative inline-flex p-1.5 rounded-full mb-1" id="demo-tab-container" style="align-self: flex-end;">
                        <!-- Sliding Pill Glider -->
                        <div id="demo-glider" class="absolute bg-[#00555C] rounded-full pointer-events-none z-0 shadow-sm" style="transition: all 0.45s cubic-bezier(0.25, 1, 0.5, 1);"></div>

                        <button type="button" role="tab" onclick="filterDemos(1, this)"
                            class="demo-tab-btn relative z-10 px-3.5 sm:px-6 md:px-8 py-2 sm:py-2.5 md:py-3 rounded-full font-semibold text-xs sm:text-sm md:text-base whitespace-nowrap transition-colors duration-200 text-white">
                            Landing Page Template
                        </button>
                        <button type="button" role="tab" onclick="filterDemos(2, this)"
                            class="demo-tab-btn relative z-10 px-3.5 sm:px-6 md:px-8 py-2 sm:py-2.5 md:py-3 rounded-full font-semibold text-xs sm:text-sm md:text-base whitespace-nowrap transition-colors duration-200 text-[#2C4346] hover:text-[#00555C]">
                            E-Commerce Template
                        </button>
                    </div>
                </div>

                <!-- Templates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch" id="demo-grid">
                    @php $ecomThemeIdx = 0; @endphp
                    @forelse($displayDemos as $demo)
                        @php
                            $demoHref = $demo->link ?? '#';
                            if ((int) ($demo->type ?? 0) === 2 && $demoHref !== '#') {
                                $ecomThemeIdx++;
                                $themeNum = preg_match('/Theme\s*(\d+)/i', $demo->title ?? '', $m) ? (int) $m[1] : $ecomThemeIdx;
                                $parsed = parse_url($demoHref);
                                if (!empty($parsed['scheme']) && !empty($parsed['host'])) {
                                    $port = !empty($parsed['port']) ? ':' . $parsed['port'] : '';
                                    $demoHref = $parsed['scheme'] . '://' . $parsed['host'] . $port . '/?preview_theme=' . $themeNum;
                                } else {
                                    $demoHref = '/?preview_theme=' . $themeNum;
                                }
                            }
                        @endphp
                        <div class="hero-reveal demo-card group overflow-hidden flex flex-col h-full"
                            data-type="{{ $demo->type }}" style="transition-delay: {{ ($loop->index % 3) * 100 }}ms;">
                            <!-- Image Frame with Padding & Rounded Corners matching redesign -->
                            <a href="{{ $demoHref }}" target="_blank" class="demo-image-frame" style="height: 340px; max-height: 340px; min-height: 340px; overflow: hidden;">
                                <img src="{{ $demo->image_url ?? asset('images/saas/live1.png') }}"
                                    alt="{{ $demo->title ?? 'Theme Preview' }}"
                                    loading="lazy"
                                    class="demo-preview-img" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/15 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                            </a>
                            <div class="p-5 sm:p-6 flex items-center justify-between gap-3 mt-auto">
                                <div class="min-w-0 pr-2">
                                    <h3 class="text-base sm:text-lg font-bold text-[#0A1F22] truncate group-hover:text-[#00555C] transition-colors" title="{{ $demo->title ?? 'Template Preview' }}">
                                        {{ $demo->title ?? 'Template Preview' }}
                                    </h3>
                                    <span class="text-xs text-[#6F8789] font-medium">Interactive Demo</span>
                                </div>
                                <a href="{{ $demoHref }}" target="_blank"
                                    class="flex-shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-[#00555C] hover:bg-[#003A3F] text-white font-semibold text-sm transition shadow-sm hover:shadow whitespace-nowrap">
                                    <span>Live Preview</span>
                                    <svg aria-hidden="true" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
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
    <section class="py-12 overflow-hidden scroll-container" style="background: #0A1F22;">
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
                                        class="px-5 py-2.5 md:px-10 md:py-4 rounded-full font-bold whitespace-nowrap text-sm md:text-xl shadow-sm border" style="color: #C6DDDA; border-color: #234B4D; background: transparent;">
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
    <!-- ============ READY TO GROW ============ -->
    <section class="py-16 px-4 md:px-10 relative">
        <div class="container mx-auto max-w-[1240px]">
            <div class="glass shine border border-white/85 rounded-[32px] p-8 md:p-16 flex flex-col items-center text-center gap-6 shadow-[0_18px_40px_-24px_rgba(0,58,63,0.35)]">

                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#7CE0C3]/35 text-[#00555C] font-semibold text-sm">
                    🚀 Business Made Simple
                </span>

                <h2 class="text-3xl md:text-5xl font-extrabold text-[#0A1F22] tracking-tight max-w-4xl md:whitespace-nowrap">
                    Ready to Grow Your Business?
                </h2>

                <p class="text-[#2F4A4D] text-base md:text-lg max-w-2xl leading-relaxed">
                    Join thousands of businesses using <strong>Dorja.io</strong> to manage sales,
                    inventory, accounting, CRM, HRM, POS, warehouses, and marketing from one
                    powerful cloud platform.
                </p>

                <!-- Buttons -->
                <div class="flex flex-wrap gap-3 justify-center mt-2">
                    <a href="https://app.dorja.io/register"
                        class="btnp inline-flex items-center gap-2 bg-[#00555C] hover:bg-[#003A3F] text-white font-semibold text-[17px] px-8 py-3.5 rounded-full transition shadow-[0_14px_30px_-14px_rgba(0,85,92,0.7)]">
                        <span>Start Free Trial</span>
                    </a>

                    <a href="{{ route('saas.package.list') }}"
                        class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-[#0A1F22] border border-[#BFD6D1] font-semibold text-[17px] px-8 py-3.5 rounded-full transition">
                        <span>View Pricing</span>
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="flex flex-wrap gap-x-6 gap-y-3 justify-center mt-2 text-[15px] font-medium text-[#2F4A4D]">
                    <span class="inline-flex items-center gap-2">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00555C" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="9"></circle><path d="M8 12l3 3 5-6"></path></svg>
                        Free Trial
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00555C" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="9"></circle><path d="M8 12l3 3 5-6"></path></svg>
                        No Credit Card Required
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00555C" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="9"></circle><path d="M8 12l3 3 5-6"></path></svg>
                        Setup in Minutes
                    </span>
                    <span class="inline-flex items-center gap-2">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#00555C" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="9"></circle><path d="M8 12l3 3 5-6"></path></svg>
                        Secure Cloud Platform
                    </span>
                </div>

            </div>
        </div>
    </section>

    @if ($pricingPlans->isNotEmpty())
        <section class="py-8 md:py-14 px-4 md:px-10 relative" id="pricing">
            <div class="container mx-auto max-w-[1240px]">

                <div class="text-center mb-10">
                    <span
                        class="hero-reveal inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/90 backdrop-blur ring-1 ring-black/[0.06] shadow-sm text-[14px] font-medium text-[#0A1F22] mb-6" style="transition-delay: 0ms;">
                        <span class="w-2 h-2 rounded-full bg-[#00A08A]"></span>
                        Choose Best Plan for Your Business
                    </span>
                    <h2 class="hero-reveal text-2xl md:text-4xl animated-text font-extrabold text-gray-900 mb-6" style="transition-delay: 100ms;">
                        Here are the best packages for your business below.
                    </h2>
                </div>

                <!-- Billing Cycle Toggle Switch -->
                <div class="flex items-center justify-center mb-14">
                    <div id="pricing-toggle-wrap" class="relative inline-flex items-center p-1.5 rounded-full"
                        style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); box-shadow: 0 4px 20px -2px rgba(0, 85, 92, 0.08);">
                        <!-- Sliding Glider (Matching Demo Switcher) -->
                        <div id="pricing-glider" class="absolute bg-[#00555C] rounded-full shadow-sm pointer-events-none z-0" style="transition: all 0.45s cubic-bezier(0.25, 1, 0.5, 1);"></div>

                        <button type="button" id="home-monthly-btn" onclick="switchHomePricingCycle('monthly')"
                            class="home-cycle-btn relative z-10 px-3.5 sm:px-6 py-2 sm:py-2.5 rounded-full text-xs sm:text-sm whitespace-nowrap font-semibold transition-colors duration-200 text-white cursor-pointer">
                            Monthly Billing
                        </button>
                        <button type="button" id="home-yearly-btn" onclick="switchHomePricingCycle('yearly')"
                            class="home-cycle-btn relative z-10 px-3.5 sm:px-6 py-2 sm:py-2.5 rounded-full text-xs sm:text-sm whitespace-nowrap font-semibold transition-colors duration-200 text-[#2C4346] hover:text-[#00555C] flex items-center gap-1.5 sm:gap-2 cursor-pointer">
                            <span>Yearly Billing</span>
                            <span
                                class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] whitespace-nowrap font-extrabold bg-[#7CE0C3] text-[#00555C] shadow-sm">
                                Save 20%
                            </span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">

                    @foreach ($pricingPlans as $plan)
                        @php
                            $mode = strtolower($plan->mode);

                            $monthlyTier = $plan->tiers->firstWhere('billing_cycle', 'monthly');
                            $yearlyTier = $plan->tiers->firstWhere('billing_cycle', 'yearly');

                            $monthlyReg = floatval($monthlyTier?->regular_price ?? 0);
                            $monthlyDisc = floatval($monthlyTier?->discount_price ?? 0);
                            $monthlyFinal = ($monthlyDisc > 0 && $monthlyDisc < $monthlyReg) ? $monthlyDisc : ($monthlyReg > 0 ? $monthlyReg : 0);

                            if ($yearlyTier) {
                                $yearlyReg = floatval($yearlyTier->regular_price ?? ($monthlyReg * 12));
                                $yearlyDisc = floatval($yearlyTier->discount_price ?? 0);
                                $yearlyFinal = ($yearlyDisc > 0 && $yearlyDisc < $yearlyReg) ? $yearlyDisc : ($yearlyReg > 0 ? $yearlyReg : ($monthlyFinal * 12 * 0.8));
                            } else {
                                $yearlyReg = $monthlyReg * 12;
                                $yearlyFinal = round(($monthlyFinal > 0 ? $monthlyFinal : $monthlyReg) * 12 * 0.80);
                            }
                            $yearlySaving = max(0, ($monthlyFinal * 12) - $yearlyFinal);
                            $effectiveMonthlyFromYearly = $yearlyFinal > 0 ? round($yearlyFinal / 12) : 0;

                            $themeColor = '#1e1b4b';
                            $isSpecialMode = false;

                            if ($mode === 'regular') {
                                $themeColor = '#7e22ce';
                                $isSpecialMode = true;
                            } elseif ($mode === 'popular') {
                                $themeColor = '#0073ea';
                                $isSpecialMode = true;
                            }
                        @endphp

                        <!-- Card Container -->
                        <div class="hero-reveal h-full" style="transition-delay: {{ ($loop->index % 4) * 100 }}ms;">
                        <div class="{{ $mode === 'popular' ? 'rounded-2xl flex flex-col h-full p-7 transition-all duration-300 relative text-white shadow-2xl border border-white/20' : 'glass rounded-2xl flex flex-col h-full p-7 transition-all duration-300 relative shadow-sm hover:shadow-xl' }}"
                            @if ($mode === 'popular') style="background-color: #1c696f !important;" @endif>

                            <!-- Header: Title and Most Popular Badge -->
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-xl font-bold {{ $mode === 'popular' ? 'text-white' : 'text-gray-900' }}">{{ $plan->name }}</h2>

                                @if ($mode === 'popular')
                                    <div
                                        class="relative bg-[#7CE0C3] text-[#052528] text-[10px] font-black uppercase px-2.5 py-1.5 rounded-sm flex items-center shadow-sm tracking-tighter">
                                        <span>Most Popular</span>
                                        <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#7CE0C3] rotate-45">
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Price Section -->
                            <div class="mb-4 min-h-[76px] flex flex-col justify-center">
                                <!-- Monthly Display -->
                                <div class="home-pricing-monthly-block transition-all duration-200">
                                    <div class="flex items-start gap-1">
                                        <span class="text-3xl font-extrabold {{ $mode === 'popular' ? '!text-white' : '' }}"
                                            style="color: {{ $mode === 'popular' ? '#fff' : ($isSpecialMode ? $themeColor : '#111') }};">
                                            {{ $setup->currency ?? '৳' }} {{ number_format($monthlyFinal, 0) }}
                                            @if ($monthlyReg > $monthlyFinal)
                                                <span class="inline-block {{ $mode === 'popular' ? 'text-white/60' : 'text-gray-400' }} text-lg font-semibold line-through ml-1">
                                                    <del>{{ number_format($monthlyReg, 0) }}</del>
                                                </span>
                                            @endif
                                        </span>
                                        <div class="text-xs {{ $mode === 'popular' ? 'text-white/75' : 'text-gray-500' }} font-bold pt-2 leading-tight">
                                            <span>/month</span>
                                        </div>
                                    </div>
                                    <p class="text-xs {{ $mode === 'popular' ? 'text-white/70' : 'text-gray-400' }} mt-1 font-medium">Billed monthly</p>
                                </div>

                                <!-- Yearly Display (hidden by default) -->
                                <div class="home-pricing-yearly-block transition-all duration-200 hidden">
                                    <div class="flex items-start gap-1">
                                        <span class="text-3xl font-extrabold {{ $mode === 'popular' ? '!text-white' : '' }}"
                                            style="color: {{ $mode === 'popular' ? '#fff' : ($isSpecialMode ? $themeColor : '#111') }};">
                                            {{ $setup->currency ?? '৳' }} {{ number_format($effectiveMonthlyFromYearly, 0) }}
                                            @if ($monthlyFinal > $effectiveMonthlyFromYearly)
                                                <span class="inline-block {{ $mode === 'popular' ? 'text-white/60' : 'text-gray-400' }} text-lg font-semibold line-through ml-1">
                                                    <del>{{ number_format($monthlyFinal, 0) }}</del>
                                                </span>
                                            @endif
                                        </span>
                                        <div class="text-xs {{ $mode === 'popular' ? 'text-white/75' : 'text-gray-500' }} font-bold pt-2 leading-tight">
                                            <span>/month</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mt-1 gap-1">
                                        <span class="text-xs font-semibold {{ $mode === 'popular' ? 'text-white/90' : 'text-gray-700' }}">
                                            {{ $setup->currency ?? '৳' }}{{ number_format($yearlyFinal, 0) }} / year
                                        </span>
                                        @if ($yearlySaving > 0)
                                            <span
                                                class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $mode === 'popular' ? 'bg-[#7CE0C3] text-[#052528]' : 'bg-emerald-100 text-emerald-700' }} whitespace-nowrap">
                                                Save {{ $setup->currency ?? '৳' }}{{ number_format($yearlySaving, 0) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <div class="mb-4">
                                <a href="https://app.dorja.io/register?plan={{ $plan->id }}&billing=monthly"
                                    data-base-url="https://app.dorja.io/register?plan={{ $plan->id }}"
                                    class="home-plan-cta-link block text-center border-[1.5px] py-2.5 rounded-full font-bold text-sm transition-all shadow-sm {{ $mode === 'popular' ? 'bg-[#7CE0C3] hover:bg-[#A5ECD6] text-[#052528] !border-[#7CE0C3]' : 'hover:bg-gray-50' }}"
                                    style="{{ $mode === 'popular' ? '' : 'border-color: ' . $themeColor . '; color: ' . $themeColor . ';' }}">
                                    Start Free Trial
                                </a>
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <p class="{{ $mode === 'popular' ? 'text-white/80' : 'text-gray-600' }} text-sm leading-relaxed">
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
                                <hr class="{{ $mode === 'popular' ? 'border-white/20' : 'border-gray-200' }} border-1 mb-4">

                                <div class="space-y-2">

                                    <div class="space-y-2">

                                        @foreach ([['User Limit', $plan->user_limit], ['Product Limit', $plan->product_limit], ['Order Limit', $plan->order_limit], ['Extra Order', $plan->extra_order_charge]] as [$label, $value])
                                            <div class="flex items-center justify-between {{ $mode === 'popular' ? 'border-b border-white/15' : 'border-b border-gray-100' }} pb-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-5 h-5 rounded-full {{ $mode === 'popular' ? 'bg-[#7CE0C3]/20' : 'bg-[#7e22ce17]' }} flex items-center justify-center">
                                                        <i class="fa-solid fa-check text-[10px] {{ $mode === 'popular' ? 'text-[#7CE0C3]' : 'text-[#7e22ce]' }}"></i>
                                                    </div>

                                                    <span class="text-sm {{ $mode === 'popular' ? 'text-white' : 'text-[#7e22ce]' }} font-semibold">
                                                        {{ $label }}
                                                    </span>
                                                </div>

                                                <span class="text-sm font-semibold {{ $mode === 'popular' ? 'text-white' : 'text-[#7e22ce]' }}">
                                                    {{ $label === 'Extra Order' ? '৳.' : '' }}{{ $value ?: 'Unlimited' }}
                                                    {{ $label === 'Extra Order' ? '/order' : '' }}
                                                </span>
                                            </div>
                                        @endforeach

                                    </div>

                                    {{-- Custom multiple input loop --}}
                                    @if (!empty($plan->multiple_input))
                                        @foreach ($plan->multiple_input as $extraDetail)
                                            <div class="flex items-center justify-between {{ $mode === 'popular' ? 'border-b border-white/15' : 'border-b border-gray-100' }} pb-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-5 h-5 rounded-full {{ $mode === 'popular' ? 'bg-[#7CE0C3]/20' : 'bg-emerald-100' }} flex items-center justify-center">
                                                        <i class="fa-solid fa-check text-[10px] {{ $mode === 'popular' ? 'text-[#7CE0C3]' : 'text-emerald-600' }}"></i>
                                                    </div>

                                                    <span class="text-sm {{ $mode === 'popular' ? 'text-white/90' : 'text-gray-600' }}">
                                                        {{ $extraDetail }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        </div>
                    @endforeach

                </div>
                <div class="mt-16 text-center">
                    <a href="{{ route('saas.package.list') }}"
                        class="group inline-flex items-center gap-2.5 bg-[#00555C] hover:bg-[#003A3F] text-white px-8 py-3.5 rounded-full font-semibold text-[17px] transition-all duration-200 active:scale-[0.98] shadow-[0_14px_30px_-14px_rgba(0,85,92,0.7)]">
                        <span>More Packages</span>
                        <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        </section>
    @endif
    <!-- WHY CHOOSE US SECTION (Placement = 2) -->
    {{-- @if ($whyChooseUs->isNotEmpty())
        <section class="py-20 px-4 md:px-10 relative">
            <div class="container mx-auto max-w-[1240px]">
                <div class="text-center mb-20">
                    <span
                        class="hero-reveal inline-block px-5 py-1.5 rounded-full border border-[#00555C]/20 bg-[#DDEEEA] text-[#00555C] font-semibold text-sm md:text-lg mb-6" style="transition-delay: 0ms;">
                        Why Businesses Choose Dorja.io
                    </span>

                    <h2 class="hero-reveal text-2xl animated-text  md:text-4xl font-extrabold text-gray-900 mb-10" style="transition-delay: 100ms;">
                        From Operations to Growth — Everything in One System
                    </h2>
                </div>

                <div class="space-y-6 md:space-y-8">
                    <!-- Top Row: 2 Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                        @foreach ($whyChooseUs->take(2) as $benefit)
                            <div class="glass rounded-2xl p-6 md:p-8 flex flex-col h-full hover:shadow-xl transition-all duration-300">
                                <div class="overflow-hidden rounded-xl mb-6">
                                    <img src="{{ $benefit->image_url ?? asset('images/saas/choose.jpg') }}"
                                        class="w-full h-auto object-cover rounded-xl" alt="{{ $benefit->title }}" />
                                </div>
                                <h3 class="text-[#0A1F22] text-xl md:text-2xl font-bold mb-3">
                                    {{ $benefit->title }}
                                </h3>
                                <div class="text-[#4B6366] text-sm md:text-base leading-relaxed">
                                    {!! $benefit->description !!}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Bottom Row: 3 Cards -->
                    @if ($whyChooseUs->count() > 2)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                            @foreach ($whyChooseUs->skip(2)->take(3) as $benefit)
                                <div class="glass rounded-2xl p-6 md:p-8 flex flex-col h-full hover:shadow-xl transition-all duration-300">
                                    <div class="overflow-hidden rounded-xl mb-6">
                                        <img src="{{ $benefit->image_url ?? asset('images/saas/choose.jpg') }}"
                                            class="w-full h-auto object-cover rounded-xl" alt="{{ $benefit->title }}" />
                                    </div>
                                    <h3 class="text-[#0A1F22] text-lg md:text-xl font-bold mb-3">
                                        {{ $benefit->title }}
                                    </h3>
                                    <div class="text-[#4B6366] text-sm leading-relaxed">
                                        {!! $benefit->description !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mt-4 text-center">
                    <a href="https://app.dorja.io/register"
                        class="btnp inline-flex items-center justify-center gap-2.5 bg-[#00555C] hover:bg-[#003A3F] text-white px-8 py-3.5 rounded-full font-semibold text-[17px] transition-all duration-200 active:scale-[0.98] shadow-[0_14px_30px_-14px_rgba(0,85,92,0.7)]">
                        <span>Start Free Trial</span>
                    </a>
                </div>
            </div>
        </section>
    @endif --}}

    <!-- BLOG & INSIGHTS SECTION -->
    @if ($blogs->isNotEmpty())
        <section class="py-8 md:py-14 px-4 md:px-10 relative" id="blog-section">
            <div class="container mx-auto max-w-[1240px]">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-12 gap-6">
                    <h2 class="hero-reveal text-2xl md:text-4xl font-black text-gray-900">Our Blogs</h2>
                    <a href="{{ route('saas.blog.list') }}"
                        class="group inline-flex items-center gap-2 bg-[#00555C] hover:bg-[#003A3F] text-white px-6 py-2.5 rounded-full font-semibold text-[17px] transition-all duration-200 active:scale-[0.98] shadow-sm hover:shadow whitespace-nowrap">
                        <span>View All</span>
                        <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($blogs as $blog)
                        <div class="hero-reveal h-full" style="transition-delay: {{ ($loop->index % 3) * 100 }}ms;">
                        <a href="{{ route('saas.blog.details', $blog->slug) }}"
                            class="glass shine border border-white/80 rounded-2xl overflow-hidden group hover:shadow-xl transition-all duration-300 flex flex-col h-full">

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
                                        class="bg-indigo-50 text-[#00555c] px-4 py-1 rounded-full text-xs font-bold border border-white/80">
                                        {{ 'Admin' }}
                                    </span>
                                    <!-- <div class="flex items-center gap-2 text-gray-600 text-sm font-bold">
                                                                                                                    <i class="fa-regular fa-clock"></i>
                                                                                                                    <span>{{ $blog->reading_time ?? '' }} minutes</span>
                                                                                                                </div> -->
                                </div>

                                <h3 class="move-up text-xl md:text-2xl font-semibold text-gray-900 mb-4 leading-tight line-clamp-2"
                                    data-speed="0.05">
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
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
    <!-- Review SECTION -->
    @if ($allReviews->isNotEmpty())
        <section class="py-8 md:py-14 px-4 md:px-10 relative" id="reviews-section">
            <div class="container mx-auto max-w-[1240px]">
                <div class="text-center mb-16">
                    <span
                        class="hero-reveal inline-block px-5 py-2 rounded-full border border-[#00555C]/20 bg-[#DDEEEA] text-[#00555C] font-semibold text-sm md:text-lg mb-6" style="transition-delay: 0ms;">
                        Customer Reviews
                    </span>
                    <h2 class="hero-reveal text-3xl md:text-5xl font-black text-gray-900" style="transition-delay: 100ms;">
                        What our customers say
                    </h2>
                </div>

                <!-- Masonry Grid -->
                <div class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6" id="review-container">
                    @foreach ($allReviews as $index => $review)
                        <div
                            class="review-card break-inside-avoid bg-white border border-white/80 p-8 rounded-2xl hover:shadow-md transition {{ $index >= 6 ? 'hidden' : '' }}">
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
    <section class="py-8 md:py-14 px-4 md:px-10 relative" id="faq-section">
        <div class="container mx-auto max-w-[1240px]">

            <!-- Header -->
            <div class="text-center mb-10">
                <span class="hero-reveal inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/90 backdrop-blur ring-1 ring-black/[0.06] shadow-sm text-[14px] font-medium text-[#0A1F22] mb-4" style="transition-delay: 0ms;">
                    <span class="w-2 h-2 rounded-full bg-[#00A08A]"></span>
                    Frequently Asked Questions
                </span>

                <h2 class="hero-reveal text-2xl animated-text  md:text-4xl font-extrabold text-gray-900 mb-10" style="transition-delay: 100ms;">
                    Better Understanding of Dorja.io and its Features
                </h2>

                <!-- Search Box -->
                <div class="mt-8 max-w-xl mx-auto px-5">
                    <input type="text" id="faqSearch" placeholder="Search FAQ..."
                        class="w-full px-5 py-3 bg-white/90 border border-[#00555C]/30 rounded-xl focus:outline-none focus:border-[#00555C] focus:ring-2 focus:ring-[#00555C]/20 shadow-sm" />
                </div>
            </div>



            <!-- FAQ List -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="faq-container">

                    @foreach ($faqs as $index => $faq)
                        <div class="faq-item glass border border-white/80 rounded-2xl {{ $index >= 10 ? 'faq-hidden' : '' }}"
                             style="{{ $index >= 10 ? 'display:none;' : '' }}">

                            <!-- Question -->
                            <button
                                class="p-6 w-full flex justify-between items-center text-left font-bold text-gray-900 text-lg faq-toggle">
                                <span class="faq-question">{{ $faq->title }}</span>
                                <i class="fa-solid fa-plus faq-icon text-[#00555C] text-lg transition-transform duration-200"></i>
                            </button>

                            <!-- Answer -->
                            <div class="px-6 pb-6 faq-content mt-[-2px] text-gray-600 leading-relaxed hidden">
                                {!! $faq->content !!}
                            </div>

                        </div>
                    @endforeach

                </div>

                <!-- Load More Button -->
                <div class="text-center mt-8" id="faq-load-more-wrap">
                    <button id="faq-load-more"
                        class="px-8 py-3.5 rounded-full font-bold text-[17px] border-2 text-[#00555C] border-[#00555C] hover:bg-[#00555C] hover:text-white transition-all duration-300">
                        Load More
                    </button>
                </div>
        </div>
    </section>
    <section class="py-10 px-4 md:px-10">
        <div class="container mx-auto max-w-[1240px]">





            <div class="hero-reveal rounded-3xl p-10 md:p-14 text-center text-white border border-[#00555c]/40 shadow-2xl" style="background: radial-gradient(circle at 70% 30%, #00555c 0%, #030f0c 60%);">

                <h3 class="text-3xl md:text-4xl font-bold">
                    Start Managing Your Business Smarter Today
                </h3>

                <p class="mt-4 text-gray-300 max-w-2xl mx-auto leading-8 text-base md:text-lg">
                    Join businesses using Dorja.io to simplify operations, increase productivity,
                    and grow faster with one complete cloud-based business management platform.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row justify-center gap-4">

                    <a href="https://app.dorja.io/register"
                        class="bg-white text-[#00555c] font-bold text-[17px] px-8 py-3.5 rounded-full hover:bg-gray-100 transition shadow-md">
                        Start Free Trial
                    </a>

                    <a href="{{ route('saas.package.list') }}"
                        class="border border-white/80 text-white font-semibold text-[17px] px-8 py-3.5 rounded-full hover:bg-white hover:text-[#00555c] transition">
                        View Pricing
                    </a>

                </div>

            </div>

        </div>
    </section>
@endsection
@push('scripts')
    <!-- Accordion + Search + Load More Script -->
    <script>
        // Accordion
        function initFaqAccordion() {
            document.querySelectorAll('.faq-toggle').forEach((btn) => {
                if (btn._faqBound) return;
                btn._faqBound = true;
                btn.addEventListener('click', () => {
                    const content = btn.nextElementSibling;
                    const icon = btn.querySelector('.faq-icon') || btn.querySelector('i');
                    const isHidden = content.classList.contains('hidden');
                    if (isHidden) {
                        content.classList.remove('hidden');
                        if (icon) { icon.classList.remove('fa-plus'); icon.classList.add('fa-minus'); }
                    } else {
                        content.classList.add('hidden');
                        if (icon) { icon.classList.remove('fa-minus'); icon.classList.add('fa-plus'); }
                    }
                });
            });
        }
        initFaqAccordion();

        // Load More
        const loadMoreBtn = document.getElementById('faq-load-more');
        const loadMoreWrap = document.getElementById('faq-load-more-wrap');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function () {
                const hidden = document.querySelectorAll('.faq-hidden[style*="display:none"]');
                let shown = 0;
                hidden.forEach(function (item) {
                    if (shown < 10) {
                        item.style.display = '';
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(16px)';
                        setTimeout(function () {
                            item.style.transition = 'opacity 0.35s ease, transform 0.35s ease';
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, 20);
                        shown++;
                    }
                });
                // Hide button if no more hidden items
                const remaining = document.querySelectorAll('.faq-hidden[style*="display:none"]');
                if (remaining.length === 0) {
                    loadMoreWrap.style.display = 'none';
                }
                initFaqAccordion();
            });

            // Hide button if total faqs <= 10
            const allHidden = document.querySelectorAll('.faq-hidden');
            if (allHidden.length === 0) {
                loadMoreWrap.style.display = 'none';
            }
        }

        // Search Filter (shows all matching, hides load more while searching)
        document.getElementById('faqSearch').addEventListener('input', function () {
            let value = this.value.toLowerCase().trim();
            let items = document.querySelectorAll('.faq-item');
            if (value === '') {
                // Reset to initial state
                items.forEach(function (item, i) {
                    if (item.classList.contains('faq-hidden')) {
                        item.style.display = 'none';
                    } else {
                        item.style.display = '';
                    }
                });
                if (loadMoreWrap) {
                    const remaining = document.querySelectorAll('.faq-hidden[style*="display:none"]');
                    loadMoreWrap.style.display = remaining.length > 0 ? '' : 'none';
                }
                return;
            }
            if (loadMoreWrap) loadMoreWrap.style.display = 'none';
            items.forEach(function (item) {
                let text = item.querySelector('.faq-question').innerText.toLowerCase();
                item.style.display = text.includes(value) ? '' : 'none';
            });
        });
    </script>
    <script>
        // Auto-adjust dorja-demo iframe height based on content
        window.addEventListener('message', function(e) {
            if (e.data && e.data.type === 'dorja-demo-resize' && e.data.height) {
                const iframe = document.getElementById('dorjaDemoIframe');
                const container = document.getElementById('dorjaDemoContainer');
                if (iframe) {
                    iframe.style.height = (e.data.height + 6) + 'px';
                }
                if (container) {
                    container.style.height = (e.data.height + 6) + 'px';
                }
            }
        });

        // EduManager Scroll-based 3D Tilt Effect
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.getElementById('heroDeviceMockup');
            if (!card) return;

            let ticking = false;
            function updateTilt() {
                if (window.innerWidth < 768) {
                    card.style.transform = 'none';
                    ticking = false;
                    return;
                }
                const rect = card.getBoundingClientRect();
                const winH = window.innerHeight;
                // As you scroll down and the card moves into focus, it rotates smoothly from 14deg to 0deg
                const progress = Math.min(1, Math.max(0, (winH - rect.top) / (winH * 0.95)));
                const rotateX = Math.max(0, (1 - progress) * 14);
                const scale = 0.95 + (progress * 0.05);
                card.style.transform = `scale(${scale}) rotateX(${rotateX.toFixed(2)}deg)`;
                ticking = false;
            }

            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(updateTilt);
                    ticking = true;
                }
            }, { passive: true });

            window.addEventListener('resize', updateTilt, { passive: true });
            updateTilt();
        });
    </script>
    <script>
        function formatVideoTime(seconds) {
            if (isNaN(seconds) || seconds === Infinity) return '00:00';
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m < 10 ? '0' : ''}${m}:${s < 10 ? '0' : ''}${s}`;
        }

        function toggleHeroVideo() {
            const video = document.getElementById('heroVideo');
            const pauseIcon = document.getElementById('pauseIcon');
            const playIcon = document.getElementById('playIcon');
            if (!video) return;
            if (video.paused) {
                video.play();
                if (pauseIcon) pauseIcon.classList.remove('hidden');
                if (playIcon) playIcon.classList.add('hidden');
            } else {
                video.pause();
                if (pauseIcon) pauseIcon.classList.add('hidden');
                if (playIcon) playIcon.classList.remove('hidden');
            }
        }

        function toggleHeroAudio() {
            const video = document.getElementById('heroVideo');
            const muteIcon = document.getElementById('muteIcon');
            const unmuteIcon = document.getElementById('unmuteIcon');
            if (!video) return;
            video.muted = !video.muted;
            if (video.muted) {
                if (muteIcon) muteIcon.classList.remove('hidden');
                if (unmuteIcon) unmuteIcon.classList.add('hidden');
            } else {
                if (muteIcon) muteIcon.classList.add('hidden');
                if (unmuteIcon) unmuteIcon.classList.remove('hidden');
            }
        }

        function seekHeroVideo(event) {
            const video = document.getElementById('heroVideo');
            const track = document.getElementById('heroVideoProgressTrack');
            if (!video || !track || !video.duration) return;
            const rect = track.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const percentage = Math.max(0, Math.min(1, clickX / rect.width));
            video.currentTime = percentage * video.duration;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const video = document.getElementById('heroVideo');
            const progressBar = document.getElementById('heroVideoProgressBar');
            const pauseIcon = document.getElementById('pauseIcon');
            const playIcon = document.getElementById('playIcon');

            if (video) {
                // Ensure video plays smoothly
                const tryPlay = () => {
                    const playPromise = video.play();
                    if (playPromise !== undefined) {
                        playPromise.then(() => {
                            if (pauseIcon) pauseIcon.classList.remove('hidden');
                            if (playIcon) playIcon.classList.add('hidden');
                        }).catch(() => {
                            // Autoplay was prevented by browser, ensure mute and retry
                            video.muted = true;
                            video.play().catch(() => {});
                        });
                    }
                };
                tryPlay();

                video.addEventListener('timeupdate', function () {
                    if (!video.duration) return;
                    const percent = (video.currentTime / video.duration) * 100;
                    if (progressBar) {
                        progressBar.style.width = percent + '%';
                    }
                });

                video.addEventListener('play', function () {
                    if (pauseIcon) pauseIcon.classList.remove('hidden');
                    if (playIcon) playIcon.classList.add('hidden');
                });

                video.addEventListener('pause', function () {
                    if (pauseIcon) pauseIcon.classList.add('hidden');
                    if (playIcon) playIcon.classList.remove('hidden');
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            window.SwiperPromise && window.SwiperPromise.then((Swiper) => {
                if (!Swiper) return;
                const heroEl = document.querySelector('.heroSwiper');
                if (heroEl) {
                    new Swiper('.heroSwiper', {
                        loop: true,
                        speed: 1000,
                    });
                }
            });
        });

        const solutions = @json($solutions);

        function moveSolGlider(target, immediate = false) {
            const tabsContainer = document.getElementById("solution-tabs");
            const glider = document.getElementById("sol-tab-glider");
            if (!tabsContainer || !glider || !target) return;

            const navRect = tabsContainer.getBoundingClientRect();
            const targetRect = target.getBoundingClientRect();

            if (immediate) {
                glider.style.transition = "none";
            } else {
                glider.style.transition = "all 0.45s cubic-bezier(0.25, 1, 0.5, 1)";
            }

            glider.style.left = (targetRect.left - navRect.left) + "px";
            glider.style.top = (targetRect.top - navRect.top) + "px";
            glider.style.width = targetRect.width + "px";
            glider.style.height = targetRect.height + "px";
            glider.style.opacity = "1";
        }

        function switchSolution(key, btn) {
            const data = solutions[key];
            if (!data) return;

            const solTitle = document.getElementById('sol-title');
            const solDesc = document.getElementById('sol-desc');
            const solMockup = document.getElementById('sol-mockup');
            const solWatermark = document.getElementById('sol-watermark');

            if (solTitle) solTitle.innerText = data.title;
            if (solDesc) solDesc.innerText = data.desc;
            if (solMockup) solMockup.innerText = data.mockup;
            if (solWatermark) solWatermark.innerText = data.title;

            // Trigger live module preview in right coding window
            if (typeof window.showDorjaModule === 'function') {
                window.showDorjaModule(key);
            }

            let featureHtml = '';
            data.features.forEach(f => {
                featureHtml += `
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 bg-[#00555C] rounded-lg flex items-center justify-center text-white shrink-0">
                            <i class="fa-solid ${f.icon} text-sm"></i>
                        </div>
                        <span class="font-bold text-[#0A1F22] text-base md:text-lg">${f.text}</span>
                    </div>
                `;
            });

            const solFeatures = document.getElementById('sol-features');
            if (solFeatures) solFeatures.innerHTML = featureHtml;

            document.querySelectorAll('.sol-tab-btn').forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });
            if (btn) {
                btn.classList.add('active');
                btn.setAttribute('aria-selected', 'true');
            }
        }

        // default load
        document.addEventListener("DOMContentLoaded", function () {
            const firstTab = document.querySelector(".sol-tab-btn");
            if (firstTab) {
                firstTab.click();
            }
        });
            </script>
            <script>
                //review
                document.addEventListener('DOMContentLoaded', function () {
                    const loadMoreBtn = document.getElementById('load-more-reviews');
                    const itemsToShow = 6;

                    if (loadMoreBtn) {
                        loadMoreBtn.addEventListener('click', function () {
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
                function moveDemoGlider(target, immediate = false) {
                    const container = document.getElementById('demo-tab-container');
                    const glider = document.getElementById('demo-glider');
                    if (!container || !glider || !target) return;

                    const cRect = container.getBoundingClientRect();
                    const tRect = target.getBoundingClientRect();

                    if (immediate) {
                        glider.style.transition = 'none';
                    } else {
                        glider.style.transition = 'all 0.45s cubic-bezier(0.25, 1, 0.5, 1)';
                    }

                    glider.style.left = (tRect.left - cRect.left) + 'px';
                    glider.style.top = (tRect.top - cRect.top) + 'px';
                    glider.style.width = tRect.width + 'px';
                    glider.style.height = tRect.height + 'px';
                    glider.style.opacity = '1';
                }

                function filterDemos(type, btn) {
                    const buttons = document.querySelectorAll('#demo-tab-container .demo-tab-btn');
                    buttons.forEach(b => {
                        b.classList.remove('text-white');
                        b.classList.add('text-[#2C4346]', 'hover:text-[#00555C]');
                    });

                    if (btn) {
                        btn.classList.add('text-white');
                        btn.classList.remove('text-[#2C4346]', 'hover:text-[#00555C]');
                        moveDemoGlider(btn);
                    }

                    const cards = document.querySelectorAll('.demo-card');
                    cards.forEach(card => {
                        if (card.getAttribute('data-type') == type) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }

                document.addEventListener("DOMContentLoaded", function () {
                    const firstTab = document.querySelector('#demo-tab-container .demo-tab-btn');
                    if (firstTab) {
                        filterDemos(1, firstTab);
                        moveDemoGlider(firstTab, true);
                        requestAnimationFrame(() => {
                            const glider = document.getElementById('demo-glider');
                            if (glider) glider.style.transition = 'all 0.45s cubic-bezier(0.25, 1, 0.5, 1)';
                        });
                    }
                });

                window.addEventListener('resize', function () {
                    const activeBtn = document.querySelector('#demo-tab-container .demo-tab-btn.text-white');
                    if (activeBtn) moveDemoGlider(activeBtn, true);
                });

                function movePricingGlider(target, immediate = false) {
                    const wrap = document.getElementById('pricing-toggle-wrap');
                    const glider = document.getElementById('pricing-glider');
                    if (!wrap || !glider || !target) return;

                    const wrapRect = wrap.getBoundingClientRect();
                    const targetRect = target.getBoundingClientRect();

                    if (immediate) {
                        glider.style.transition = 'none';
                    } else {
                        glider.style.transition = 'all 0.5s cubic-bezier(0.25, 1, 0.5, 1)';
                    }

                    glider.style.left = (targetRect.left - wrapRect.left) + 'px';
                    glider.style.top = (targetRect.top - wrapRect.top) + 'px';
                    glider.style.width = targetRect.width + 'px';
                    glider.style.height = targetRect.height + 'px';
                    glider.style.opacity = '1';
                }

                // Initialize glider on page load
                document.addEventListener('DOMContentLoaded', function () {
                    const monthlyBtn = document.getElementById('home-monthly-btn');
                    if (monthlyBtn) {
                        movePricingGlider(monthlyBtn, true);
                        requestAnimationFrame(() => {
                            const glider = document.getElementById('pricing-glider');
                            if (glider) glider.style.transition = 'all 0.5s cubic-bezier(0.25, 1, 0.5, 1)';
                        });
                    }
                });

                window.addEventListener('resize', function () {
                    const activeBtn = document.querySelector('.home-cycle-btn.text-white');
                    if (activeBtn) movePricingGlider(activeBtn, true);
                });

                function switchHomePricingCycle(cycle) {
                    const monthlyBtn = document.getElementById('home-monthly-btn');
                    const yearlyBtn = document.getElementById('home-yearly-btn');
                    const monthlyBlocks = document.querySelectorAll('.home-pricing-monthly-block');
                    const yearlyBlocks = document.querySelectorAll('.home-pricing-yearly-block');
                    const ctaLinks = document.querySelectorAll('.home-plan-cta-link');

                    if (cycle === 'yearly') {
                        if (monthlyBtn) {
                            monthlyBtn.classList.remove('text-white');
                            monthlyBtn.classList.add('text-[#2C4346]', 'hover:text-[#00555C]');
                        }
                        if (yearlyBtn) {
                            yearlyBtn.classList.remove('text-[#2C4346]', 'hover:text-[#00555C]');
                            yearlyBtn.classList.add('text-white');
                            movePricingGlider(yearlyBtn);
                        }
                        monthlyBlocks.forEach(el => el.classList.add('hidden'));
                        yearlyBlocks.forEach(el => el.classList.remove('hidden'));
                        ctaLinks.forEach(link => {
                            const base = link.getAttribute('data-base-url');
                            if (base) link.setAttribute('href', base + '&billing=yearly');
                        });
                    } else {
                        if (yearlyBtn) {
                            yearlyBtn.classList.remove('text-white');
                            yearlyBtn.classList.add('text-[#2C4346]', 'hover:text-[#00555C]');
                        }
                        if (monthlyBtn) {
                            monthlyBtn.classList.remove('text-[#2C4346]', 'hover:text-[#00555C]');
                            monthlyBtn.classList.add('text-white');
                            movePricingGlider(monthlyBtn);
                        }
                        yearlyBlocks.forEach(el => el.classList.add('hidden'));
                        monthlyBlocks.forEach(el => el.classList.remove('hidden'));
                        ctaLinks.forEach(link => {
                            const base = link.getAttribute('data-base-url');
                            if (base) link.setAttribute('href', base + '&billing=monthly');
                        });
                    }
                }
            </script>
            <script src="https://unpkg.com/lucide@0.511.0/dist/umd/lucide.min.js"></script>
            <script src="{{ asset('js/dorja-modules.js') }}"></script>
@endpush
