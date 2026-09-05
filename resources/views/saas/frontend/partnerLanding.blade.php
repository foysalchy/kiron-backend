@extends('saas.layouts.layout')

@section('content')
<!-- HERO SECTION -->
<section class="relative pt-16 pb-20 md:pt-24 md:pb-28 overflow-hidden bg-gradient-to-b from-teal-50/50 via-white to-white">
    <div class="container mx-auto px-4 md:px-10 relative z-10 max-w-7xl">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#13565e]/10 border border-[#13565e]/20 text-[#00555c] font-bold text-sm mb-6 shadow-sm">
                <i class="fa-solid fa-handshake-angle text-[#078e9a]"></i> Official Dorja Partner & Affiliate Program
            </div>
            <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 tracking-tight leading-[1.15] mb-6">
                Grow Your Income with <br class="hidden sm:inline">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#00555c] via-[#078e9a] to-emerald-600">
                    Up to 35% Commission
                </span>
            </h1>
            <p class="text-gray-600 text-lg md:text-xl font-medium leading-relaxed mb-8">
                Partner with Bangladesh's leading cloud-based POS, ERP, Inventory & Ecommerce platform. Refer businesses, give them exclusive discounts, and earn monthly recurring revenue with milestone rank bonuses.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('partner.register') }}"
                    class="w-full sm:w-auto bg-[#00555c] hover:bg-[#078e9a] text-white px-8 py-4 rounded-xl font-bold text-lg transition duration-200 shadow-lg shadow-[#00555c]/25 flex items-center justify-center gap-2">
                    <span>Become a Partner</span>
                    <i class="fa-solid fa-arrow-right text-sm"></i>
                </a>
                <a href="{{ route('partner.login') }}"
                    class="w-full sm:w-auto bg-white border border-gray-300 hover:border-gray-400 text-gray-800 px-8 py-4 rounded-xl font-bold text-lg transition duration-200 shadow-sm flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket text-gray-500"></i>
                    <span>Partner Portal Login</span>
                </a>
            </div>

            <!-- Fast Trust Badges -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-14 pt-10 border-t border-gray-200/70 text-left">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">20% – 35%</h4>
                        <p class="text-xs text-gray-500 font-medium">Commission Rate</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fa-solid fa-cookie-bite"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">30 Days</h4>
                        <p class="text-xs text-gray-500 font-medium">Cookie Window</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fa-solid fa-tags"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">5% – 10% Off</h4>
                        <p class="text-xs text-gray-500 font-medium">Discount for Buyers</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-[#00555c] flex items-center justify-center text-lg flex-shrink-0">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-gray-900">Fast Payout</h4>
                        <p class="text-xs text-gray-500 font-medium">bKash, Nagad, Bank</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="py-20 bg-white border-y border-gray-100">
    <div class="container mx-auto px-4 md:px-10 max-w-7xl">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">How the Program Works</h2>
            <div class="w-16 h-1.5 bg-[#00555c] rounded-full mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium text-lg">Start earning in 3 simple steps without any upfront fee or complex approval.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
            <!-- Step 1 -->
            <div class="relative bg-slate-50 border border-slate-200/80 rounded-2xl p-8 hover:shadow-lg transition duration-200">
                <div class="w-12 h-12 rounded-xl bg-[#00555c] text-white flex items-center justify-center text-xl font-bold mb-6 shadow-md shadow-[#00555c]/20">
                    1
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Join in 60 Seconds</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">
                    Create your partner profile for free. Instantly receive your personalized referral link, tracking code, and exclusive buyer coupon.
                </p>
                <span class="inline-flex items-center text-xs font-semibold text-[#00555c] bg-teal-50 px-2.5 py-1 rounded-md border border-teal-100">
                    Instant Approval
                </span>
            </div>

            <!-- Step 2 -->
            <div class="relative bg-slate-50 border border-slate-200/80 rounded-2xl p-8 hover:shadow-lg transition duration-200">
                <div class="w-12 h-12 rounded-xl bg-[#078e9a] text-white flex items-center justify-center text-xl font-bold mb-6 shadow-md shadow-[#078e9a]/20">
                    2
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Share With Businesses</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">
                    Recommend Dorja to retail stores, wholesalers, accounting clients, ecommerce brands, or your social network followers.
                </p>
                <span class="inline-flex items-center text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-100">
                    30-Day Auto Cookie Tracking
                </span>
            </div>

            <!-- Step 3 -->
            <div class="relative bg-slate-50 border border-slate-200/80 rounded-2xl p-8 hover:shadow-lg transition duration-200">
                <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xl font-bold mb-6 shadow-md shadow-emerald-600/20">
                    3
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Earn & Withdraw</h3>
                <p class="text-gray-600 text-sm leading-relaxed mb-4">
                    Earn up to 35% commission on every paid subscription. Level up through volume milestone ranks and withdraw earnings directly.
                </p>
                <span class="inline-flex items-center text-xs font-semibold text-purple-700 bg-purple-50 px-2.5 py-1 rounded-md border border-purple-100">
                    Transparent Payouts
                </span>
            </div>
        </div>
    </div>
</section>

<!-- PARTNER TIERS & GROUPS -->
<section class="py-20 bg-slate-50">
    <div class="container mx-auto px-4 md:px-10 max-w-7xl">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Partner Categories & Earning Rates</h2>
            <div class="w-16 h-1.5 bg-[#00555c] rounded-full mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium text-lg">We offer tailored partner groups for standard affiliates, accounting consultants, and sales professionals.</p>
        </div>

        @if(isset($groups) && count($groups) > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($groups as $grp)
            <div class="bg-white border border-gray-200 rounded-2xl p-8 flex flex-col justify-between hover:shadow-xl transition-all duration-200 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-bl from-teal-500/10 to-transparent rounded-bl-full pointer-events-none"></div>

                <div>
                    <div class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-[#00555c] bg-teal-50 px-3 py-1 rounded-full mb-4 border border-teal-100">
                        {{ $grp->name }}
                    </div>

                    <div class="mb-6">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-gray-900">{{ number_format($grp->default_commission_rate, 0) }}%</span>
                            <span class="text-sm font-semibold text-gray-500">Base Commission</span>
                        </div>
                        <p class="text-xs text-emerald-600 font-semibold mt-1">
                            + Referred Buyers Get {{ number_format($grp->buyer_discount_rate ?? 0, 0) }}% Signup Discount
                        </p>
                    </div>

                    <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                        {{ $grp->description ?? 'Ideal partner group for driving high-intent business referrals and long term revenue.' }}
                    </p>

                    <!-- Milestone Tiers -->
                    @if($grp->tiers && count($grp->tiers) > 0)
                    <div class="space-y-2.5 mb-8 border-t border-gray-100 pt-5">
                        <p class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Volume Milestone Ranks:</p>
                        @foreach($grp->tiers as $tier)
                        <div class="flex items-center justify-between text-xs py-1.5 px-3 rounded-lg bg-gray-50 border border-gray-100">
                            <span class="font-semibold text-gray-700">{{ $tier->name ?? 'Tier Rank' }} ({{ $tier->min_sales }}{{ $tier->max_sales ? '-'.$tier->max_sales : '+' }} sales)</span>
                            <span class="font-bold text-[#00555c]">{{ number_format($tier->commission_rate, 0) }}%</span>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <a href="{{ route('partner.register') }}"
                    class="w-full text-center bg-gray-900 hover:bg-[#00555c] text-white py-3 rounded-xl font-bold text-sm transition duration-150 shadow-sm">
                    Join this Program
                </a>
            </div>
            @endforeach
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Default Fallback Cards if DB empty -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 hover:shadow-xl transition-all">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Standard Affiliate</h3>
                <div class="text-4xl font-extrabold text-[#00555c] mb-2">20% <span class="text-sm font-medium text-gray-500">Base</span></div>
                <p class="text-sm text-gray-600 mb-6">For influencers, bloggers, and tech content creators.</p>
                <ul class="text-xs space-y-2 text-gray-600 mb-8">
                    <li>✓ 5% Special Buyer Discount</li>
                    <li>✓ Bronze, Silver & Gold Ranks (up to 35%)</li>
                    <li>✓ 30 Days Cookie Tracking</li>
                </ul>
                <a href="{{ route('partner.register') }}" class="block text-center bg-gray-900 hover:bg-[#00555c] text-white py-3 rounded-xl font-bold text-sm">Join Now</a>
            </div>

            <div class="bg-white border-2 border-[#00555c] rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all relative">
                <span class="absolute -top-3.5 right-6 bg-[#00555c] text-white text-[11px] font-bold px-3 py-0.5 rounded-full uppercase tracking-wider">Most Popular</span>
                <h3 class="text-xl font-bold text-gray-900 mb-2">CA & Consultants</h3>
                <div class="text-4xl font-extrabold text-[#00555c] mb-2">30% <span class="text-sm font-medium text-gray-500">Base</span></div>
                <p class="text-sm text-gray-600 mb-6">For chartered accountants, business auditors & consultants.</p>
                <ul class="text-xs space-y-2 text-gray-600 mb-8">
                    <li>✓ 10% Client Discount Code</li>
                    <li>✓ Priority Dedicated Account Manager</li>
                    <li>✓ Multi-tier volume incentives</li>
                </ul>
                <a href="{{ route('partner.register') }}" class="block text-center bg-[#00555c] hover:bg-[#078e9a] text-white py-3 rounded-xl font-bold text-sm shadow-md">Join as Consultant</a>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-8 hover:shadow-xl transition-all">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Agency & Sales Team</h3>
                <div class="text-4xl font-extrabold text-[#00555c] mb-2">15% - 35%</div>
                <p class="text-sm text-gray-600 mb-6">For digital agencies, ERP integrators & field marketing teams.</p>
                <ul class="text-xs space-y-2 text-gray-600 mb-8">
                    <li>✓ Custom Co-Branded Collateral</li>
                    <li>✓ Tailored Bulk Enterprise Pricing</li>
                    <li>✓ Dedicated Payout Schedule</li>
                </ul>
                <a href="{{ route('partner.register') }}" class="block text-center bg-gray-900 hover:bg-[#00555c] text-white py-3 rounded-xl font-bold text-sm">Join as Agency</a>
            </div>
        </div>
        @endif
    </div>
</section>

<!-- WHY PARTNER BENEFITS -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4 md:px-10 max-w-7xl">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Everything You Need to Succeed</h2>
            <div class="w-16 h-1.5 bg-[#00555c] rounded-full mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium text-lg">We provide world-class software, high conversion rates, and partner support so you can maximize revenue.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="p-6 rounded-2xl border border-gray-100 bg-slate-50/70 hover:bg-white hover:border-gray-200 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-teal-100 text-[#00555c] flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Real-Time Analytics Portal</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Track your referral clicks, trial activations, paid subscriber conversions, and wallet balance with 100% transparent live reporting.
                </p>
            </div>

            <div class="p-6 rounded-2xl border border-gray-100 bg-slate-50/70 hover:bg-white hover:border-gray-200 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Special Discounts For Your Audience</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Give your followers and clients an irresistible 5% to 10% instant discount on their Dorja subscriptions when they join with your link.
                </p>
            </div>

            <div class="p-6 rounded-2xl border border-gray-100 bg-slate-50/70 hover:bg-white hover:border-gray-200 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-trophy"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Automated Milestone Rank Upgrades</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    As your total sales volume increases, unlock Bronze, Silver, and Gold badges with permanently increased commission percentages.
                </p>
            </div>

            <div class="p-6 rounded-2xl border border-gray-100 bg-slate-50/70 hover:bg-white hover:border-gray-200 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Multiple Withdrawal Options</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Easily withdraw your accumulated earnings to bKash, Nagad, Rocket, or direct Bank transfer with minimum payout threshold.
                </p>
            </div>

            <div class="p-6 rounded-2xl border border-gray-100 bg-slate-50/70 hover:bg-white hover:border-gray-200 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-bullhorn"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">High Converting Product</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Dorja is trusted by retail stores, wholesalers, pharmacies, clothing brands, and fast-growing businesses across Bangladesh.
                </p>
            </div>

            <div class="p-6 rounded-2xl border border-gray-100 bg-slate-50/70 hover:bg-white hover:border-gray-200 hover:shadow-lg transition">
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <h4 class="text-lg font-bold text-gray-900 mb-2">Dedicated Partner Support</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Get assistance from our partner success managers for customized campaigns, live demo training, and co-marketing materials.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- FAQ SECTION -->
<section class="py-20 bg-slate-50 border-t border-gray-100">
    <div class="container mx-auto px-4 md:px-10 max-w-4xl">
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Frequently Asked Questions</h2>
            <div class="w-16 h-1.5 bg-[#00555c] rounded-full mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium">Everything you need to know about joining and earning as a partner.</p>
        </div>

        <div class="space-y-4">
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h4 class="text-base font-bold text-gray-900 mb-2">Is there any fee or cost to join the Dorja Partner Program?</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    No, the partner program is 100% free to join. You can register in under 1 minute and start sharing your link immediately.
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h4 class="text-base font-bold text-gray-900 mb-2">How does referral tracking work?</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    When someone clicks your referral link, a secure 30-day tracking cookie is placed on their browser. If they register or subscribe at any point within 30 days, you will receive full credit and commission for the sale.
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h4 class="text-base font-bold text-gray-900 mb-2">How and when do I receive payouts?</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Whenever your wallet reaches the minimum payout threshold, you can submit a withdrawal request directly from your Partner Portal. Payouts are processed via bKash, Nagad, or Bank Transfer within 2 to 3 business days.
                </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h4 class="text-base font-bold text-gray-900 mb-2">Do my referred buyers get a discount?</h4>
                <p class="text-sm text-gray-600 leading-relaxed">
                    Yes! Depending on your partner group, your referred clients receive an instant 5% to 10% discount on their subscription plans, making it very attractive for them to register through your link.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- FINAL CTA BANNER -->
<section class="py-16 md:py-20 bg-[#0a061e] text-white text-center relative overflow-hidden">
    <div class="container mx-auto px-4 md:px-10 max-w-4xl relative z-10">
        <h2 class="text-3xl md:text-5xl font-black mb-4 tracking-tight">Ready to Become a Dorja Partner?</h2>
        <p class="text-gray-300 text-lg md:text-xl max-w-2xl mx-auto mb-8">
            Join hundreds of consultants, content creators, and agencies already earning recurring monthly commissions.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('partner.register') }}"
                class="w-full sm:w-auto bg-[#00555c] hover:bg-[#078e9a] text-white px-8 py-4 rounded-xl font-bold text-lg transition duration-200 shadow-xl shadow-[#00555c]/30">
                Register as Partner (Free)
            </a>
            <a href="{{ route('partner.login') }}"
                class="w-full sm:w-auto bg-white/10 hover:bg-white/20 text-white px-8 py-4 rounded-xl font-bold text-lg transition duration-200 border border-white/20">
                Partner Portal Login
            </a>
        </div>
    </div>
</section>
@endsection
