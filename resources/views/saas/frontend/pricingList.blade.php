@extends('saas.layouts.layout')
@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::PRICING,
        $setup->company_id ?? null
    );
@endphp

@include('components.meta-info.saas-meta', [
    'setup' => $setup,

    'type' => 'WebPage',

    'title' => $pageData?->meta_title ?? ('Pricing Plans | ' . $setup->shop_name),

    'description' => $pageData?->meta_description ?? ('Explore flexible pricing plans for ' . $setup->shop_name . '. Choose the perfect plan for your business with powerful ERP, POS, Inventory, CRM, Accounting, and HRM features.'),

    'keywords' => $pageData?->meta_keywords 
        ? implode(',', $pageData->meta_keywords) 
        : 'pricing, ERP pricing, POS pricing, inventory software pricing, business software',

    'image' => $setup->meta_image
        ? asset('storage/' . $setup->meta_image)
        : asset('storage/' . $setup->logo),

    'canonical' => route('saas.package.list'),

    'breadcrumb' => [
        [
            'name' => 'Home',
            'url' => url('/'),
        ],
        [
            'name' => 'Pricing',
            'url' => route('saas.package.list'),
        ],
    ],
])
@section('content')
    <section class="bg-[#fcfcfc] py-24 px-6 md:px-10 ">
        <div class="container mx-auto">

            <div class="text-center mb-20">
                <h1 class="text-4xl md:text-5xl font-black text-gray-900 mb-5 tracking-tight">Choose the Right Plan</h1>
                <p class="text-gray-500 text-lg">The best packages for your business are listed below.</p>
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
                            $themeColor = '#a855f7'; // Purple
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
                                    <div class="absolute -left-1 top-1/2 -translate-y-1/2 w-2 h-2 bg-[#0073ea] rotate-45">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Price Section -->
                        <div class="mb-4">
                            <div class="flex items-start gap-1">
                                <span class="text-3xl font-bold"
                                    style="color: {{ $isSpecialMode ? $themeColor : '#111' }};">
                                    {{ $setup->currency ?? '$' }}{{ number_format($price, 0) }}
                                </span>
                                <div class="text-xs text-gray-500 font-bold pt-2 leading-tight">
                                    
                                    <span>/month</span>
                                </div>
                            </div>

                            <!-- <div class="mt-4">
                                <p class="text-gray-900 font-bold text-sm">Total
                                    {{ $setup->currency ?? '$' }}{{ number_format($price, 0) }} / Yearly</p>
                                <p class="text-gray-400 text-xs">Billed annually</p>
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
                            {{
    strtolower($plan->name) == 'starter'
        ? 'Start your business with confidence.'
        : (strtolower($plan->name) == 'growth'
            ? 'Scale faster with smarter tools.'
            : (strtolower($plan->name) == 'business'
                ? 'Powerful tools for growing teams.'
                : 'Enterprise-grade performance & support.'
            )
        )
}}
                        </p>
                        </div>

                        <!-- Features/Limits Section (Fixed at bottom) -->
                        <div class="mt-auto">
                            <hr class="border-gray-200 border-1 mb-4">

                            <div class="space-y-2">
                                
                               <div class="space-y-2">

                                @foreach ([
                                    ['User Limit', $plan->user_limit],
                                    ['Product Limit', $plan->product_limit],
                                    ['Order Limit', $plan->order_limit],
                                    ['Extra Order', $plan->extra_order_charge],
                                     
                                ] as [$label, $value])

                                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-5 h-5 rounded-full bg-[#a855f717] flex items-center justify-center">
                                                <i class="fa-solid fa-check text-[10px] text-[#a855f7]"></i>
                                            </div>

                                            <span class="text-sm text-[#a855f7] font-semibold">
                                                {{ $label }} 
                                            </span>
                                        </div>

                                        <span class="text-sm font-semibold text-[#a855f7]">
                                            {{ $label === 'Extra Order' ? '৳.' : '' }}{{ $value ?: 'Unlimited' }} {{ $label === 'Extra Order' ? '/order' : '' }}
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
                                                <div class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center">
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
        </div>
        <!-- Corporate / Custom Plan CTA Card -->
<div class="col-span-1 md:col-span-2 lg:col-span-4 mt-10">
    <div class="relative bg-gradient-to-r from-[#0f172a] to-[#1e1b4b] text-white p-10 rounded-xl shadow-lg overflow-hidden">

        <!-- Glow Effect -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-purple-500 opacity-20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-500 opacity-20 rounded-full blur-3xl"></div>

        <div class="relative flex flex-col lg:flex-row items-center justify-between gap-6">

            <!-- Left Content -->
            <div>
                <h2 class="text-2xl md:text-3xl font-bold mb-2">
                    Need a Custom / Corporate Plan?
                </h2>

                <p class="text-gray-300 text-sm md:text-base leading-relaxed max-w-xl">
                    We provide tailored ERP solutions for large businesses, enterprises, and organizations.
                    Get custom limits, dedicated support, API access, and white-label options based on your needs.
                </p>

                <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-300">
                    <span class="bg-white/10 px-3 py-1 rounded-full">Custom Users</span>
                    <span class="bg-white/10 px-3 py-1 rounded-full">Unlimited Scale</span>
                    <span class="bg-white/10 px-3 py-1 rounded-full">Dedicated Support</span>
                    <span class="bg-white/10 px-3 py-1 rounded-full">API & Integration</span>
                </div>
            </div>

            <!-- Right Button -->
            <div class="flex flex-col gap-3">
                <a href="https://dorja.io/contact"
                   class="bg-white text-[#1e1b4b] font-bold px-6 py-3 rounded-full text-sm text-center hover:bg-gray-200 transition">
                    Contact Sales
                </a>

                <a href="https://app.dorja.io/demo"
                   class="border border-white text-white font-semibold px-6 py-3 rounded-full text-sm text-center hover:bg-white/10 transition">
                    Request Demo
                </a>
            </div>

        </div>
    </div>
</div>
    </section>
@endsection
