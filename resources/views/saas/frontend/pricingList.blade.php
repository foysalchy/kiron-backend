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

                            <div class="mt-4">
                                <p class="text-gray-900 font-bold text-sm">Total
                                    {{ $setup->currency ?? '$' }}{{ number_format($price, 0) }} / Yearly</p>
                                <p class="text-gray-400 text-xs">Billed annually</p>
                            </div>
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
                                    ? 'Launch your business with everything you need to manage sales, inventory, and customers—all in one platform.'
                                    : (strtolower($plan->name) == 'growth'
                                        ? 'Scale your business faster with higher limits, smarter automation, and powerful business insights.'
                                        : (strtolower($plan->name) == 'business'
                                            ? 'Optimize every department with advanced tools designed for growing and multi-team businesses.'
                                            : 'Unlock the full power of Dorja with unlimited scalability, premium support, and enterprise-ready performance.'
                                        )
                                    )
                            }}
                        </p>
                        </div>

                        <!-- Features/Limits Section (Fixed at bottom) -->
                        <div class="mt-auto">
                            <hr class="border-gray-200 border-1 mb-4">

                            <div class="space-y-4">
                                
                               <div class="space-y-3">

                                @foreach ([
                                    ['User Limit', $plan->user_limit],
                                    ['Product Limit', $plan->product_limit],
                                    ['Order Limit', $plan->order_limit],
                                    ['Invoice Templates', $plan->invoice_limit],
                                ] as [$label, $value])

                                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center">
                                                <i class="fa-solid fa-check text-[10px] text-emerald-600"></i>
                                            </div>

                                            <span class="text-sm text-gray-600">
                                                {{ $label }}
                                            </span>
                                        </div>

                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $value ?: 'Unlimited' }}
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
                                        <div class="flex justify-between items-center text-gray-700 text-sm">
                                            <span>{{ $extraDetail }}</span>
                                            <i class="fa-regular fa-circle-info text-gray-300 text-xs"></i>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>
@endsection
