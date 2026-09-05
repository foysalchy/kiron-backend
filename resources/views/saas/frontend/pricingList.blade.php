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

            <div class="text-center mb-10">
                <h1 class="move-up text-4xl md:text-5xl font-black text-gray-900 mb-4 tracking-tight">Choose the Right Plan</h1>
                <p class="text-gray-500 text-lg">The best packages for your business are listed below.</p>
            </div>

            <!-- Billing Cycle Toggle Switch -->
            <div class="flex items-center justify-center mb-14">
                <div class="inline-flex items-center p-1.5 bg-gray-100 rounded-full border border-gray-200/90 shadow-inner">
                    <button type="button" id="billing-monthly-btn" onclick="switchPricingCycle('monthly')"
                        class="pricing-cycle-btn px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-200 bg-white text-gray-900 shadow-sm cursor-pointer">
                        Monthly Billing
                    </button>
                    <button type="button" id="billing-yearly-btn" onclick="switchPricingCycle('yearly')"
                        class="pricing-cycle-btn px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-200 text-gray-600 hover:text-gray-900 flex items-center gap-2 cursor-pointer">
                        <span>Yearly Billing</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-extrabold bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-sm animate-pulse">
                            Save 20%
                        </span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">

                @foreach ($pricingPlans as $plan)
                    @php
                        $mode = strtolower($plan->mode);
                        $monthlyTier = collect($plan->tiers)->firstWhere('billing_cycle', 'monthly');
                        $yearlyTier = collect($plan->tiers)->firstWhere('billing_cycle', 'yearly');

                        $monthlyReg = floatval($monthlyTier['regular_price'] ?? 0);
                        $monthlyDisc = floatval($monthlyTier['discount_price'] ?? 0);
                        $monthlyFinal = ($monthlyDisc > 0 && $monthlyDisc < $monthlyReg) ? $monthlyDisc : ($monthlyReg > 0 ? $monthlyReg : 0);

                        // Yearly calculations (use yearlyTier if present, else fallback to 20% off annual)
                        if ($yearlyTier) {
                            $yearlyReg = floatval($yearlyTier['regular_price'] ?? ($monthlyReg * 12));
                            $yearlyDisc = floatval($yearlyTier['discount_price'] ?? 0);
                            $yearlyFinal = ($yearlyDisc > 0 && $yearlyDisc < $yearlyReg) ? $yearlyDisc : ($yearlyReg > 0 ? $yearlyReg : ($monthlyFinal * 12 * 0.8));
                        } else {
                            $yearlyReg = $monthlyReg * 12;
                            $yearlyFinal = round(($monthlyFinal > 0 ? $monthlyFinal : $monthlyReg) * 12 * 0.80);
                        }
                        $yearlySaving = max(0, ($monthlyFinal * 12) - $yearlyFinal);
                        $effectiveMonthlyFromYearly = $yearlyFinal > 0 ? round($yearlyFinal / 12) : 0;

                        $themeColor = '#1e1b4b'; // Default Navy
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
                    <div class="bg-white border border-gray-200 flex flex-col h-full p-7 transition-all duration-300 relative border-t-[6px] shadow-sm hover:shadow-xl rounded-b-lg"
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
                        <div class="mb-4 min-h-[76px] flex flex-col justify-center">
                            <!-- Monthly Display -->
                            <div class="pricing-monthly-block transition-all duration-200">
                                <div class="flex items-start gap-1">
                                    <span class="text-3xl font-extrabold"
                                        style="color: {{ $isSpecialMode ? $themeColor : '#111' }};">
                                        {{ $setup->currency ?? '৳' }} {{ number_format($monthlyFinal, 0) }}
                                        @if ($monthlyReg > $monthlyFinal)
                                            <span class="inline-block text-gray-400 text-lg font-semibold line-through ml-1">
                                                <del>{{ number_format($monthlyReg, 0) }}</del>
                                            </span>
                                        @endif
                                    </span>
                                    <div class="text-xs text-gray-500 font-bold pt-2 leading-tight">
                                        <span>/month</span>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400 mt-1 font-medium">Billed monthly</p>
                            </div>

                            <!-- Yearly Display (hidden by default) -->
                            <div class="pricing-yearly-block transition-all duration-200 hidden">
                                <div class="flex items-start gap-1">
                                    <span class="text-3xl font-extrabold"
                                        style="color: {{ $isSpecialMode ? $themeColor : '#111' }};">
                                        {{ $setup->currency ?? '৳' }} {{ number_format($effectiveMonthlyFromYearly, 0) }}
                                        @if ($monthlyFinal > $effectiveMonthlyFromYearly)
                                            <span class="inline-block text-gray-400 text-lg font-semibold line-through ml-1">
                                                <del>{{ number_format($monthlyFinal, 0) }}</del>
                                            </span>
                                        @endif
                                    </span>
                                    <div class="text-xs text-gray-500 font-bold pt-2 leading-tight">
                                        <span>/month</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-1 gap-1">
                                    <span class="text-xs font-semibold text-gray-700">
                                        {{ $setup->currency ?? '৳' }}{{ number_format($yearlyFinal, 0) }} / year
                                    </span>
                                    @if ($yearlySaving > 0)
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 whitespace-nowrap">
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
                                class="plan-cta-link block text-center border-[1.5px] py-2.5 rounded-full font-bold text-sm transition-all hover:bg-gray-50 shadow-sm"
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
                                            <div class="w-5 h-5 rounded-full bg-[#7e22ce17] flex items-center justify-center">
                                                <i class="fa-solid fa-check text-[10px] text-[#7e22ce]"></i>
                                            </div>

                                            <span class="text-sm text-[#7e22ce] font-semibold">
                                                {{ $label }}
                                            </span>
                                        </div>

                                        <span class="text-sm font-semibold text-[#7e22ce]">
                                            {{ $label === 'Extra Order' ? '৳.' : '' }}{{ $value ?: 'Unlimited' }} {{ $label === 'Extra Order' ? '/order' : '' }}
                                        </span>
                                    </div>

                                @endforeach

                            </div>

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


            </div>

        </div>
    </div>
</div>
  <div class="mt-5 bg-white px-8 py-8 rounded prose prose-slate w-full min-w-full">
            {!! $pageData->description!!}
        </div>
        </div>

    </section>

    <script>
        function switchPricingCycle(cycle) {
            const monthlyBtn = document.getElementById('billing-monthly-btn');
            const yearlyBtn = document.getElementById('billing-yearly-btn');
            const monthlyBlocks = document.querySelectorAll('.pricing-monthly-block');
            const yearlyBlocks = document.querySelectorAll('.pricing-yearly-block');
            const ctaLinks = document.querySelectorAll('.plan-cta-link');

            if (cycle === 'yearly') {
                if (monthlyBtn) {
                    monthlyBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                    monthlyBtn.classList.add('text-gray-600');
                }
                if (yearlyBtn) {
                    yearlyBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                    yearlyBtn.classList.remove('text-gray-600');
                }
                monthlyBlocks.forEach(el => el.classList.add('hidden'));
                yearlyBlocks.forEach(el => el.classList.remove('hidden'));
                ctaLinks.forEach(link => {
                    const base = link.getAttribute('data-base-url');
                    if (base) link.setAttribute('href', base + '&billing=yearly');
                });
            } else {
                if (yearlyBtn) {
                    yearlyBtn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                    yearlyBtn.classList.add('text-gray-600');
                }
                if (monthlyBtn) {
                    monthlyBtn.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                    monthlyBtn.classList.remove('text-gray-600');
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
@endsection
