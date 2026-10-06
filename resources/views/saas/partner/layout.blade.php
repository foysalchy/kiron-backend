<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Partner Dashboard') | Dorja Partner Program</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0fdfa',
                            100: '#ccfbf1',
                            500: '#078e9a',
                            600: '#13565e',
                            700: '#0f464d',
                            800: '#0c383e',
                            900: '#082529',
                        }
                    }
                }
            }
        }
    </script>
    @include('components.fontawesome')
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
@php
    $siteSetup = $setup ?? \App\Models\SiteSetting::whereNull('company_id')->first() ?? \App\Models\SiteSetting::first();
@endphp
                <div class="flex items-center space-x-4">
                    <a href="{{ route('partner.dashboard') }}" class="flex items-center space-x-2.5">
                        @if (!empty($siteSetup?->logo_url))
                            <img src="{{ $siteSetup->logo_url }}" alt="{{ $siteSetup->shop_name ?? 'Dorja' }}" class="h-8 md:h-9 w-auto object-contain"
                                onerror="this.onerror=null; this.src='{{ asset('images/saas/Shopify_Logo.png') }}';">
                        @elseif (file_exists(public_path('images/saas/Shopify_Logo.png')))
                            <img src="{{ asset('images/saas/Shopify_Logo.png') }}" alt="Dorja Logo" class="h-8 md:h-9 w-auto object-contain">
                        @else
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-brand-500/20">
                                D
                            </div>
                            <span class="text-xl font-bold text-slate-800 tracking-tight">{{ $siteSetup->shop_name ?? 'Dorja' }}</span>
                        @endif
                        <span class="text-brand-600 font-semibold text-xs px-2.5 py-0.5 rounded-full bg-brand-50 border border-brand-100">Partners</span>
                    </a>
                </div>

                @if(session('partner_id'))
                <nav class="hidden md:flex items-center space-x-1 font-medium text-sm">
                    <a href="{{ route('partner.dashboard') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('partner.dashboard') ? 'text-brand-600 bg-brand-50 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-chart-pie mr-1.5 text-xs"></i> Overview
                    </a>
                    <a href="{{ route('partner.referrals') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('partner.referrals') ? 'text-brand-600 bg-brand-50 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-users mr-1.5 text-xs"></i> Referred Accounts
                    </a>
                    <a href="{{ route('partner.earnings') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('partner.earnings') ? 'text-brand-600 bg-brand-50 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-receipt mr-1.5 text-xs"></i> Commissions
                    </a>
                    <a href="{{ route('partner.withdrawals') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('partner.withdrawals') ? 'text-brand-600 bg-brand-50 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <i class="fa-solid fa-wallet mr-1.5 text-xs"></i> Payouts
                    </a>
                </nav>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('partner.profile') }}" class="flex items-center space-x-2 text-sm text-slate-700 hover:text-slate-900 px-3 py-1.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 transition">
                        <div class="w-6 h-6 rounded-full bg-brand-600 text-white flex items-center justify-center text-xs font-bold">
                            {{ substr(session('partner_name', 'P'), 0, 1) }}
                        </div>
                        <span class="font-medium hidden sm:inline">{{ session('partner_name') }}</span>
                    </a>
                    <form action="{{ route('partner.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-slate-500 hover:text-red-600 font-medium px-2 py-1.5 rounded border border-transparent hover:border-red-100 hover:bg-red-50 transition" title="Logout">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
                @else
                <div class="flex items-center space-x-3">
                    <a href="{{ route('partner.login') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5">Sign In</a>
                    <a href="{{ route('partner.register') }}" class="text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 px-4 py-2 rounded-lg shadow-sm">Join as Partner</a>
                </div>
                @endif
            </div>
        </div>
    </header>

    <!-- Flash Alerts -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
        <div class="flex items-center p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-200">
            <i class="fa-solid fa-circle-check text-emerald-500 mr-2 text-lg"></i>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div class="flex items-center p-4 mb-4 text-sm text-rose-800 rounded-xl bg-rose-50 border border-rose-200">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 mr-2 text-lg"></i>
            <div>{{ session('error') }}</div>
        </div>
        @endif

        @if($errors->any())
        <div class="p-4 mb-4 text-sm text-rose-800 rounded-xl bg-rose-50 border border-rose-200">
            <div class="font-bold flex items-center mb-1">
                <i class="fa-solid fa-circle-xmark text-rose-500 mr-2 text-lg"></i> Please fix the following errors:
            </div>
            <ul class="list-disc list-inside ml-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            &copy; {{ date('Y') }} {{ $siteSetup->shop_name ?? 'Dorja' }} Partner Program. All rights reserved. Empowering business growth together.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
