@extends('saas.layouts.layout')
@php
    $pageData = \App\Services\Saas\SystemPageService::get(
        \App\Enums\SystemPageType::PRICING_FAQ,
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

            @php
                $sortedPlans = $pricingPlans->sortBy(function ($plan) {
                    $m = strtolower($plan->mode);
                    if ($m === 'regular') {
                        return 1;
                    }
                    if ($m === 'popular') {
                        return 2;
                    }
                    return 3;
                });
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">

                @foreach ($sortedPlans as $plan)
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
                        <div class="mb-8">
                            <div class="flex items-start gap-1">
                                <span class="text-3xl font-bold"
                                    style="color: {{ $isSpecialMode ? $themeColor : '#111' }};">
                                    {{ $setup->currency ?? '$' }}{{ number_format($price, 0) }}
                                </span>
                                <div class="text-xs text-gray-500 font-bold pt-2 leading-tight">
                                    <span>seat /</span><br>
                                    <span>month</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <p class="text-gray-900 font-bold text-sm">Total
                                    {{ $setup->currency ?? '$' }}{{ number_format($price, 0) }} / month</p>
                                <p class="text-gray-400 text-xs">Billed annually</p>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <div class="mb-8">
                            <a href=""
                                class="block text-center border-[1.5px] py-2.5 rounded-full font-bold text-sm transition-all hover:bg-gray-50"
                                style="border-color: {{ $themeColor }}; color: {{ $themeColor }};">
                                {{ $mode === 'regular' || $mode === 'popular' ? 'Try for free' : 'Contact Us' }}
                            </a>
                        </div>

                        <!-- Description -->
                        <div class="mb-10">
                            <p class="text-gray-600 text-sm leading-relaxed">
                                {{ $plan->description ?? 'Manage all your work in one place with our smart system.' }}
                            </p>
                        </div>

                        <!-- Features/Limits Section (Fixed at bottom) -->
                        <div class="mt-auto">
                            <hr class="border-gray-200 border-1 mb-8">

                            <div class="space-y-4">
                                <p class="font-bold text-gray-900 text-sm">{{ $plan->name }} includes:</p>

                                <div class="flex justify-between items-center text-gray-700 text-sm">
                                    <span>User Limit: {{ $plan->user_limit ?: 'Unlimited' }}</span>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-xs"></i>
                                </div>

                                <div class="flex justify-between items-center text-gray-700 text-sm">
                                    <span>Product Limit: {{ $plan->product_limit ?: 'Unlimited' }}</span>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-xs"></i>
                                </div>

                                <div class="flex justify-between items-center text-gray-700 text-sm">
                                    <span>Order Limit: {{ $plan->order_limit ?: 'Unlimited' }}</span>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-xs"></i>
                                </div>

                                <div class="flex justify-between items-center text-gray-700 text-sm">
                                    <span>Invoice Limit: {{ $plan->invoice_limit ?: 'Unlimited' }}</span>
                                    <i class="fa-regular fa-circle-info text-gray-300 text-xs"></i>
                                </div>

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
