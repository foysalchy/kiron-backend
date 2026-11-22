@extends('template4.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.checkout-meta', ['setup' => $setup])
@endsection
@section('content')
@php
    $setup = $setup ?? \App\Models\SiteSetting::where('company_id', getCurrentCompany()?->company_id)->first();
    $isVerify = (bool) ($setup->checkout_sms_verify ?? false);
@endphp
    <section class="bg-[#F9F9F9] py-2">
        <nav aria-label="Breadcrumb"
            class="container mx-auto px-4 flex   items-center pt-2 md:pt-4 gap-1 md:gap-2 text-xs sm:text-sm md:text-base lg:text-lg mb-4 md:mb-6">
            <a href="/" class="text-[var(--primary-color)] hover:text-[#52166d] transition font-medium">Home</a>
            <span class="text-gray-400">/</span>
            <span class="text-[var(--primary-color)] hover:text-[#52166d] transition font-medium">Checkout</span>
        </nav>
    </section>

    <div class="container mx-auto px-4 py-4">
        <form action="{{ route('order.store') }}" method="POST" enctype="multipart/form-data" id="checkout-main-form" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
            @csrf
            {{-- Anti-Spam Honeypot Field --}}
            <div style="display:none !important;" aria-hidden="true">
                <input type="text" name="_hp_security_check" value="" tabindex="-1" autocomplete="off" />
            </div>
            {{-- Decoy fields to trap browser address autofill --}}
            <div style="opacity: 0; position: absolute; top: 0; left: -9999px; height: 0; width: 0; z-index: -1; overflow: hidden;" aria-hidden="true">
                <input type="text" name="fake_autofill_name" tabindex="-1" autocomplete="name" />
                <input type="text" name="fake_autofill_address" tabindex="-1" autocomplete="street-address" />
                <input type="tel" name="fake_autofill_phone" tabindex="-1" autocomplete="tel" />
                <input type="email" name="fake_autofill_email" tabindex="-1" autocomplete="email" />
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-start">

                <!-- ════════ LEFT COLUMN (Shipping & Payment) ════════ -->
                <div class="lg:col-span-8 flex flex-col gap-2">

                    <!-- Mobile Order Items Card -->
                    <div id="checkout-mobile-items-container"
                        class="block lg:hidden bg-white p-4 sm:p-5 border border-gray-200 rounded-lg shadow-sm">
                        <h2
                            class="text-base md:text-lg font-bold text-[#0f172a] mb-3 border-l-4 border-[var(--primary-color)] pl-2">
                            Your Order Items
                        </h2>
                        <div class="space-y-3">
                            @foreach ($cartContent as $item)
                                <div class="bg-[#F9FAFB] rounded-xl p-3 sm:p-4 flex items-start gap-3 border border-gray-100">
                                    <div class="w-14 h-14 bg-white rounded-lg overflow-hidden border border-gray-100 shrink-0 mt-0.5">
                                        <img src="{{ $item->options->thumbnail ?? asset('./images/template1/frontend/product-thumbnail.webp') }}"
                                            alt="{{ $item->name }}" class="w-full h-full object-cover">
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-bold text-gray-800 leading-tight mb-1 truncate">
                                            {{ $item->name }}
                                        </h4>

                                        @if ($item->options->has('attributes') && count($item->options->attributes) > 0)
                                            <div class="flex flex-wrap gap-1 mb-1">
                                                @foreach ($item->options->attributes as $key => $value)
                                                    <span
                                                        class="bg-purple-50 text-[var(--primary-color)] text-[10px] font-bold px-1.5 py-0.5 rounded uppercase">
                                                        {{ $value }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @elseif($item->options->has('variant'))
                                            <p class="text-[11px] text-gray-500 font-medium mb-1">
                                                {{ $item->options->variant }}
                                            </p>
                                        @endif

                                        <div class="flex items-center gap-3 mt-1.5 flex-wrap">
                                            <p class="text-[var(--primary-color)] font-semibold text-sm">
                                                {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . number_format($item->price) : number_format($item->price) . $setup->currency }}
                                            </p>

                                            <!-- Quantity Controls (Beside Price) -->
                                            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white shadow-xs">
                                                <button type="button" onclick="updateCheckoutQty('{{ $item->rowId }}', -1, this)"
                                                    aria-label="Decrease quantity"
                                                    class="px-2 py-0.5 text-gray-600 hover:text-[var(--primary-color)] bg-transparent transition-colors cursor-pointer font-bold text-sm">-</button>
                                                <span class="qty-{{ $item->rowId }} w-6 text-center text-xs font-bold text-gray-800">{{ $item->qty }}</span>
                                                <button type="button" onclick="updateCheckoutQty('{{ $item->rowId }}', 1, this)"
                                                    aria-label="Increase quantity"
                                                    class="px-2 py-0.5 text-gray-600 hover:text-[var(--primary-color)] bg-transparent transition-colors cursor-pointer font-bold text-sm">+</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Remove Button (Top-Right) -->
                                    <div class="shrink-0 self-start pl-1 pt-0.5">
                                        <button type="button" onclick="removeCheckoutItem('{{ $item->rowId }}')"
                                            aria-label="Remove {{ $item->name }} from cart" title="Remove item"
                                            class="checkout-cart-remove text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1 bg-transparent border-0 cursor-pointer p-0.5">
                                            <i class="far fa-trash-alt text-[11px]" aria-hidden="true"></i>
                                            <span>Remove</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Card 1: Shipping Address -->
                    <section class="bg-white p-4 border border-gray-200 rounded-lg shadow-sm">
                        <h2 class="text-[16px] font-bold text-gray-800 mb-3">Shipping Address</h2>
                        <div class="space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <input type="text" name="name" placeholder="আপনার নাম *" required
                                        value="{{ old('name', auth('customer')->user()->name ?? '') }}"
                                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');" onmousedown="this.removeAttribute('readonly');" ontouchstart="this.removeAttribute('readonly');"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] transition" />
                                </div>
                                <div>
                                    <input type="tel" name="phone" id="customer-phone" placeholder="আপনার মোবাইল নম্বর *"
                                        required value="{{ old('phone', auth('customer')->user()->phone ?? '') }}"
                                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');" onmousedown="this.removeAttribute('readonly');" ontouchstart="this.removeAttribute('readonly');"
                                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] transition" />
                                </div>
                            </div>
                            @php
                                $showEmail = $setup->checkout_show_email ?? true;
                                $showCity = $setup->checkout_show_city ?? true;
                            @endphp

                            @if($showEmail || $showCity)
                                <div
                                    class="grid grid-cols-1 {{ ($showEmail && $showCity) ? 'md:grid-cols-2' : '' }} gap-3">
                                    @if($showEmail)
                                        <div>
                                            <input type="email" name="email" placeholder="ইমেইল (ঐচ্ছিক)"
                                                value="{{ old('email', auth('customer')->user()->email ?? '') }}"
                                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');" onmousedown="this.removeAttribute('readonly');" ontouchstart="this.removeAttribute('readonly');"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 outline-none focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] transition" />
                                        </div>
                                    @endif

                                    @if($showCity)
                                        <div>
                                            <input type="text" name="district" placeholder="আপনার শহর / জেলা *" required
                                                value="{{ old('district', auth('customer')->user()->district ?? '') }}"
                                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                                readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');" onmousedown="this.removeAttribute('readonly');" ontouchstart="this.removeAttribute('readonly');"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-300 outline-none focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] transition" />
                                        </div>
                                    @endif
                                </div>
                            @endif
                            <div>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <input type="text" name="address" placeholder="আপনার পূর্ণাঙ্গ ঠিকানা *" required
                                        value="{{ old('address', auth('customer')->user()->address ?? '') }}"
                                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');" onmousedown="this.removeAttribute('readonly');" ontouchstart="this.removeAttribute('readonly');"
                                        class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] transition" />
                                </div>
                            </div>
                            @guest('customer')
                                <div class="space-y-3 pt-2">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" name="create_account"
                                            class="w-4 h-4 accent-[var(--primary-color)]">
                                        <span class="text-sm font-medium text-gray-600 group-hover:text-gray-900">Create an
                                            account?</span>
                                    </label>
                                </div>
                            @endguest
                        </div>
                    </section>

                    <!-- Card 2: Select Payment Method -->
                    <section class="bg-white p-4 border border-gray-200 rounded-lg shadow-sm">
                        <h3 class="text-[14px] font-bold text-gray-800 mb-2">Select Payment Method</h3>
                        <div class="space-y-2">
                            @foreach ($paymentMethods as $method)
                                @php $slug = strtolower(trim($method->name)); @endphp
                                <div class="payment-option-wrapper">
                                    <label id="label-{{ $slug }}"
                                        class="payment-option-label flex items-center justify-between p-2 border border-gray-200 rounded-xl cursor-pointer transition hover:border-[var(--primary-color)]"
                                        onclick="handlePaymentSelection('{{ $slug }}', '{{ $method->name }}')">

                                        <div class="flex items-center gap-3">
                                            <div
                                                class="radio-ring w-4 h-4 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0">
                                                <div class="radio-dot w-2 h-2 rounded-full bg-transparent">
                                                </div>
                                            </div>
                                            <input type="radio" name="payment_method" value="{{ $method->name }}"
                                                class="hidden">
                                            <span
                                                class="text-sm font-medium text-gray-700 capitalize">{{ $method->name }}</span>
                                        </div>

                                        @if ($method->icon)
                                            <img src="{{ $method->icon_url ?? '' }}" alt="icon" loading="lazy" height="" width=""
                                                class="w-6 h-6 md:w-8 md:h-8 object-contain rounded shrink-0" />
                                        @endif
                                    </label>

                                    <!-- Injection Point for Master Form -->
                                    <div id="checkout-anchor-{{ $slug }}" class="mt-4 hidden transition-all">
                                        <div class="bg-gray-50 p-4 rounded-xl border border-orange-100">
                                            <div class="form-injection-point"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <!-- ════════ RIGHT COLUMN (Order Summary) ════════ -->
                <aside id="checkout-order-summary"
                    style="background: #f0f0f0; border: 1px solid gainsboro;"
                    class="lg:col-span-4 p-4 sm:p-5 md:p-6 rounded-lg shadow-sm lg:sticky lg:top-24">
                    <h3 class="text-lg md:text-xl font-bold text-[#0f172a] mb-4 md:mb-6">Order Summary</h3>

                    @php
                        $freeProductText = ($setup->lang ?? 'bn') === 'bn' ? 'এই পণ্যে ফ্রি ডেলিভারি সুবিধা রয়েছে!' : 'Free Delivery on this product!';
                    @endphp
                    @if(!empty($setup->free_shipping_text))
                        @if(!empty($is_product_free_delivery))
                            <div
                                class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-2.5 text-emerald-800 text-sm font-semibold shadow-xs">
                                <i class="fa-solid fa-truck-fast text-emerald-600 text-base"></i>
                                <span>{{ $freeProductText }}</span>
                            </div>
                        @elseif(!empty($is_rule_free_shipping))
                            <div
                                class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-2.5 text-emerald-800 text-sm font-semibold shadow-xs">
                                <i class="fa-solid fa-truck-fast text-emerald-600 text-base"></i>
                                <span>{{ $setup->free_shipping_text }}</span>
                            </div>
                        @elseif(!empty($setup->active_free_shipping_rule))
                            <div
                                class="mb-5 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2.5 text-amber-800 text-xs sm:text-lg font-medium">
                                <i class="fa-solid fa-gift text-amber-600 text-sm"></i>
                                <span>{{ $setup->free_shipping_text }}</span>
                            </div>
                        @endif
                    @endif

                    <!-- Product Row in Summary (Desktop) -->
                    <div class="space-y-3 mb-6 hidden lg:block">
                        @foreach ($cartContent as $item)
                            <div class="bg-white rounded-xl p-4 flex items-center gap-4 border border-gray-200">
                                <!-- Actual Product Image -->
                                <div class="w-14 h-14 bg-white rounded-lg overflow-hidden border border-gray-100 shrink-0">
                                    <img src="{{ $item->options->thumbnail ?? asset('./images/template1/frontend/product-thumbnail.webp') }}"
                                        alt="{{ $item->name }}" class="w-full h-full object-cover">
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-bold text-gray-800 leading-tight mb-1 truncate">
                                        {{ $item->name }}
                                    </h4>

                                    @if ($item->options->has('attributes') && count($item->options->attributes) > 0)
                                        <div class="flex flex-wrap gap-1 mb-1">
                                            @foreach ($item->options->attributes as $key => $value)
                                                <span
                                                    class="bg-purple-50 text-[var(--primary-color)] text-[10px] font-bold px-1.5 py-0.5 rounded uppercase">
                                                    {{ $value }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($item->options->has('variant'))
                                        <p class="text-[11px] text-gray-500 font-medium mb-1">
                                            {{ $item->options->variant }}
                                        </p>
                                    @endif

                                    <p class="text-[var(--primary-color)] font-semibold text-sm">
                                        {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . number_format($item->price) : number_format($item->price) . $setup->currency }}
                                    </p>
                                </div>

                                <!-- Quantity Controls (cart.blade.php rounded-lg style) -->
                                <div class="flex flex-col items-end gap-1.5 shrink-0">
                                    <div
                                        class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white shadow-xs">
                                        <button type="button" onclick="updateCheckoutQty('{{ $item->rowId }}', -1, this)"
                                            aria-label="Decrease quantity"
                                            class="px-2.5 py-1 text-gray-600 hover:text-[var(--primary-color)] bg-transparent transition-colors cursor-pointer font-bold text-base">-</button>
                                        <span
                                            class="qty-{{ $item->rowId }} w-8 text-center text-xs sm:text-sm font-bold text-gray-800">{{ $item->qty }}</span>
                                        <button type="button" onclick="updateCheckoutQty('{{ $item->rowId }}', 1, this)"
                                            aria-label="Increase quantity"
                                            class="px-2.5 py-1 text-gray-600 hover:text-[var(--primary-color)] bg-transparent transition-colors cursor-pointer font-bold text-base">+</button>
                                    </div>
                                    <button type="button" onclick="removeCheckoutItem('{{ $item->rowId }}')"
                                        aria-label="Remove {{ $item->name }} from cart" title="Remove item"
                                        class="checkout-cart-remove text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1 bg-transparent border-0 cursor-pointer">
                                        <i class="far fa-trash-alt text-xs" aria-hidden="true"></i>
                                        <span>Remove</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @php $isL = ($setup->currency_position ?? 'left') == 'left'; @endphp
                    <!-- 2.5 Coupon Section -->
                    <div class="mb-6 border-t border-gray-100 pt-6 hidden">
                        <label class="text-sm font-bold text-gray-600 block mb-2">Coupon Code</label>
                        <div class="flex gap-2">
                            <input type="text" id="coupon-code-input" placeholder="Enter Coupon Code"
                                value="{{ session()->has('coupon') ? session('coupon')['coupon_code'] : '' }}"
                                class="flex-1 border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-[#016738] transition-all"
                                {{ session()->has('coupon') ? 'readonly' : '' }}>

                            <button type="button" id="remove-coupon-btn" onclick="removeCoupon()" aria-label="Remove coupon"
                                title="Remove coupon"
                                class="bg-red-500 text-white rounded-lg px-4 py-2.5 hover:bg-red-600 flex items-center {{ session()->has('coupon') ? '' : 'hidden' }}">
                                <i class="fas fa-times text-white" aria-hidden="true"></i>
                                <span class="sr-only">Remove coupon</span>
                            </button>

                            <button type="button" id="apply-coupon-btn" onclick="applyCoupon()"
                                class="bg-white border border-gray-200 rounded-lg px-4 py-2.5 hover:bg-gray-50 transition-all font-bold {{ session()->has('coupon') ? 'hidden' : '' }}">
                                Apply Now
                            </button>
                        </div>
                    </div>

                    <!-- Delivery Area Selector -->
                    @if(!isset($is_free_delivery) || !$is_free_delivery)
                        <div class="py-4 border-b border-gray-100">
                            <p class="text-xs font-bold text-gray-500 uppercase mb-3">Delivery Area</p>
                            <div class="space-y-3">
                                <label class="flex items-center justify-between cursor-pointer text-sm">
                                    <span>Inside Dhaka
                                        ({{ $setup->currency }}{{ number_format($setup->inside_charge, 2) }})</span>
                                    <input type="radio" name="delivery_area" value="inside"
                                        onchange="updateCheckoutShipping(this.value)" {{ $shipping_area == 'inside' ? 'checked' : '' }} class="accent-[var(--primary-color)] w-4 h-4">
                                </label>
                                <label class="flex items-center justify-between cursor-pointer text-sm">
                                    <span>Outside Dhaka
                                        ({{ $setup->currency }}{{ number_format($setup->outside_charge, 2) }})</span>
                                    <input type="radio" name="delivery_area" value="outside"
                                        onchange="updateCheckoutShipping(this.value)" {{ $shipping_area == 'outside' ? 'checked' : '' }} class="accent-[var(--primary-color)] w-4 h-4">
                                </label>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-1.5 border-t border-gray-100 pt-2">

                        <!-- ১. সাবটোটাল (প্রোডাক্টের মোট দাম) -->
                        <div class="flex justify-between items-center text-gray-700">
                            <span class="text-sm md:text-base font-medium">Subtotal</span>
                            <span class="text-sm md:text-base font-bold text-gray-900">
                                {{ $isL ? $setup->currency : '' }} {{ number_format($subtotal) }}
                                {{ !$isL ? $setup->currency : '' }}
                            </span>
                        </div>

                        <!-- ২. শিপিং চার্জ -->
                        <div class="flex justify-between items-center text-gray-700">
                            <span class="text-sm md:text-base font-medium">Shipping Charge</span>
                            <span class="text-sm md:text-base font-bold text-gray-900">
                                @if(isset($is_free_delivery) && $is_free_delivery)
                                    Free
                                @else
                                    {{ $isL ? $setup->currency : '' }} <span
                                        id="shipping-display">{{ number_format($shipping, 2) }}</span>
                                    {{ !$isL ? $setup->currency : '' }}
                                @endif
                            </span>
                        </div>

                        <!-- ৩. ডিসকাউন্ট (যদি থাকে) -->
                        <div id="discount-row"
                            class="flex justify-between items-center text-green-700 font-bold {{ $discount > 0 ? '' : 'hidden' }}">
                            <span>Discount <span
                                    id="discount-code-display">{{ session()->has('coupon') ? '(' . session('coupon')['coupon_code'] . ')' : '' }}</span></span>
                            <span>- {{ $isL ? $setup->currency : '' }} <span
                                    id="discount-display">{{ number_format($discount) }}</span>
                                {{ !$isL ? $setup->currency : '' }}</span>
                        </div>

                        <!-- ৪. সর্বমোট (Total) -->
                        <div class="flex justify-between items-center py-4 border-t border-gray-100 mt-2">
                            <span class="text-base md:text-lg font-black text-gray-800">Total Payable</span>
                            <span class="text-xl md:text-2xl font-extrabold text-[#1147aa]">
                                {{ $isL ? $setup->currency : '' }} <span
                                    id="total-display">{{ number_format($total) }}</span>
                                {{ !$isL ? $setup->currency : '' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-2 md:mt-4">
                        <button type="submit"
                            class="w-full primary-bg hover:primary-bg transition text-white font-bold py-3 md:py-4 px-4 md:px-6 rounded-xl flex items-center justify-center gap-2 text-sm md:text-base shadow-md">
                            <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" stroke-width="2.2"
                                viewBox="0 0 24 24">
                                <path
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Place Order
                        </button>
                    </div>

                    <p class="text-center text-xs md:text-sm text-gray-500 mt-4 leading-normal">Your payment information is
                        encrypted and secure.</p>
                </aside>
            </div>
        </form>
    </div>

    <!-- Repository for Forms -->
    <div id="forms-repository" class="hidden">
        <x-template1.payment-checkout :methods="$paymentMethods" :currency="$setup->currency" />
    </div>
    <x-frontend.checkout-otp-modal />
@endsection

@push('scripts')
    <script>
        let _activeDraftOrderId = @json($draftOrderId ?? null);
        let _checkoutTotal = {{ $total }};

        /** 1. Payment Selection Handler */
        function handlePaymentSelection(slug, name) {
            // UI Reset
            document.querySelectorAll(".payment-option-label").forEach(opt => {
                opt.classList.remove("border-[var(--primary-color)]", "bg-purple-50/20");
                opt.classList.add("border-gray-200");
                const ring = opt.querySelector(".radio-ring");
                const dot = opt.querySelector(".radio-dot");
                if (ring) ring.classList.replace("border-[var(--primary-color)]", "border-gray-300");
                if (dot) dot.classList.replace("bg-[var(--primary-color)]", "bg-transparent");
            });

            // Highlight Active
            const activeLabel = document.getElementById('label-' + slug);
            if (activeLabel) {
                activeLabel.classList.replace("border-gray-200", "border-[var(--primary-color)]");
                activeLabel.classList.add("bg-purple-50/20");
                const ring = activeLabel.querySelector(".radio-ring");
                const dot = activeLabel.querySelector(".radio-dot");
                if (ring) ring.classList.replace("border-gray-300", "border-[var(--primary-color)]");
                if (dot) dot.classList.replace("bg-transparent", "bg-[var(--primary-color)]");
                activeLabel.querySelector('input[type="radio"]').checked = true;
            }

            // Reposition Forms to Repository and Disable inputs
            const repo = document.getElementById('payment-forms-area');
            document.querySelectorAll('.payment-form').forEach(form => {
                form.classList.add('hidden');
                form.querySelectorAll('input, select, textarea').forEach(i => i.disabled = true);
                if (repo) repo.appendChild(form);
            });

            // Hide all anchors
            document.querySelectorAll('[id^="checkout-anchor-"]').forEach(a => a.classList.add('hidden'));

            // Inject Targeted Form
            const targetForm = document.getElementById('form-' + slug);
            const targetAnchor = document.getElementById('checkout-anchor-' + slug);

            if (targetForm && targetAnchor) {
                const injectionPoint = targetAnchor.querySelector('.form-injection-point');
                if (injectionPoint) {
                    injectionPoint.appendChild(targetForm);
                    targetAnchor.classList.remove('hidden');
                    targetForm.classList.remove('hidden');

                    // Enable inputs for the selected method
                    targetForm.querySelectorAll('input, select, textarea').forEach(input => {
                        input.disabled = false;
                    });

                    // Auto-fill Amount
                    const amountInput = targetForm.querySelector('[name="amount"]');
                    if (amountInput) amountInput.value = Math.round(_checkoutTotal);
                }
            }
        }
        function applyCoupon() {
            const codeInput = document.getElementById('coupon-code-input');
            const code = codeInput.value;
            if (!code) return toastr.warning('Please enter a coupon code');

            const token = document.querySelector('meta[name="csrf-token"]').content;

            const btn = event.target.tagName === 'BUTTON' ? event.target : event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerText = '...';
            btn.disabled = true;

            fetch("{{ route('coupon.apply') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    coupon_code: code
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('discount-display').innerText = data.discount_amount.toLocaleString();
                        document.getElementById('total-display').innerText = data.grand_total.toLocaleString();
                        document.getElementById('discount-code-display').innerText = '(' + data.coupon_code + ')';
                        document.getElementById('discount-row').classList.remove('hidden');

                        codeInput.setAttribute('readonly', 'readonly');
                        document.getElementById('apply-coupon-btn').classList.add('hidden');
                        document.getElementById('remove-coupon-btn').classList.remove('hidden');

                        toastr.success(data.message);

                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    } else {
                        toastr.error(data.message || "Invalid coupon");
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    toastr.error("Server error occurred. Please try again.");
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        function removeCoupon() {
            const token = document.querySelector('meta[name="csrf-token"]').content;

            fetch("{{ route('coupon.remove') }}", {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('total-display').innerText = data.grand_total.toLocaleString();
                        document.getElementById('discount-row').classList.add('hidden');

                        const codeInput = document.getElementById('coupon-code-input');
                        codeInput.removeAttribute('readonly');
                        codeInput.value = '';

                        document.getElementById('apply-coupon-btn').classList.remove('hidden');
                        document.getElementById('remove-coupon-btn').classList.add('hidden');

                        toastr.success(data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                });
        }

        function updateCheckoutShipping(value) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ route('cart.shipping') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    area: value
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('shipping-display').innerText = Number(data.shipping_cost).toFixed(2);
                        document.getElementById('total-display').innerText = data.grand_total;

                        _checkoutTotal = data.grand_total_raw ? data.grand_total_raw : parseFloat(data.grand_total
                            .replace(/[^0-9.]/g, ''));

                        const activePaymentForm = document.querySelector('.payment-form:not(.hidden)');
                        if (activePaymentForm) {
                            const amountInput = activePaymentForm.querySelector('[name="amount"]');
                            if (amountInput) {
                                amountInput.value = Math.round(_checkoutTotal);
                            }
                        }

                        toastr.success(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error("Shipping update failed!");
                });
        }

        /** 3. Draft Order Logic (Blur on Phone) */
        document.getElementById('customer-phone').addEventListener('blur', function () {
            let phone = this.value;
            if (phone.length >= 11) {
                fetch("{{ route('order.partial') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        phone: phone,
                        name: document.querySelector('input[name="name"]').value,
                        address: document.querySelector('input[name="address"]').value
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) _activeDraftOrderId = data.order_id;
                    });
            }
        });

        // Page Load: Auto-select first method
        document.addEventListener('DOMContentLoaded', () => {
            const firstRadio = document.querySelector('input[name="payment_method"]');
            if (firstRadio) {
                const firstSlug = firstRadio.closest('.payment-option-label').id.replace('label-', '');
                const firstName = firstRadio.value;
                handlePaymentSelection(firstSlug, firstName);
            }
        });

        function refreshCartUI() {
            fetch(window.location.href)
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newSummary = doc.getElementById('checkout-order-summary');
                    const currentSummary = document.getElementById('checkout-order-summary');
                    if (newSummary && currentSummary) {
                        currentSummary.innerHTML = newSummary.innerHTML;
                    }
                    const newMobile = doc.getElementById('checkout-mobile-items-container');
                    const currentMobile = document.getElementById('checkout-mobile-items-container');
                    if (newMobile && currentMobile) {
                        currentMobile.innerHTML = newMobile.innerHTML;
                    }
                    const newTotal = doc.getElementById('total-display');
                    if (newTotal) {
                        const rawVal = parseFloat(newTotal.innerText.replace(/[^0-9.]/g, ''));
                        if (!isNaN(rawVal)) {
                            _checkoutTotal = rawVal;
                            const activePaymentForm = document.querySelector('.payment-form:not(.hidden)');
                            if (activePaymentForm) {
                                const amountInput = activePaymentForm.querySelector('[name="amount"]');
                                if (amountInput) amountInput.value = Math.round(_checkoutTotal);
                            }
                        }
                    }
                })
                .catch(err => console.error('Cart refresh failed:', err));
        }

        function updateCheckoutQty(rowId, change, btnEl) {
            const qtySpans = document.querySelectorAll('.qty-' + rowId);
            if (!qtySpans || qtySpans.length === 0) return;

            const currentQty = parseInt(qtySpans[0].innerText);
            const newQty = currentQty + change;

            if (newQty < 1) return;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (btnEl) btnEl.disabled = true;

            fetch("{{ route('cart.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    rowId: rowId,
                    qty: newQty
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.cart_count !== undefined) {
                            document.querySelectorAll('.cart-count-nav').forEach(el => {
                                el.innerText = data.cart_count;
                            });
                        }
                        refreshCartUI();
                        if (typeof refreshMiniCart === 'function') refreshMiniCart();
                        if (typeof toastr !== 'undefined') toastr.success('Quantity updated');
                    } else {
                        if (typeof toastr !== 'undefined') toastr.error(data.message || 'Failed to update quantity');
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (typeof toastr !== 'undefined') toastr.error('Failed to update quantity');
                })
                .finally(() => {
                    if (btnEl) btnEl.disabled = false;
                });
        }

        function removeCheckoutItem(rowId) {
            fetch(`{{ url('cart/remove') }}/${rowId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (data.cart_count === 0) {
                            window.location.href = "{{ route('cart.index') }}";
                            return;
                        }
                        document.querySelectorAll('.cart-count-nav').forEach(b => b.innerText = data.cart_count);
                        refreshCartUI();
                        if (typeof toastr !== 'undefined') toastr.error(data.message || 'Item removed from cart');
                    } else if (typeof toastr !== 'undefined') {
                        toastr.error(data.message || 'Error removing item');
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (typeof toastr !== 'undefined') toastr.error('Failed to remove item.');
                });
        }

        const checkoutCustomerFields = ['name', 'phone', 'email', 'district', 'address'];
        const checkoutFormStateKey = 'template4-checkout-customer-form';
        const checkoutForm = document.querySelector('form[enctype="multipart/form-data"]');

        document.querySelectorAll('.checkout-cart-remove').forEach(link => {
            link.addEventListener('click', () => {
                const formState = {};
                checkoutCustomerFields.forEach(name => {
                    const field = checkoutForm?.elements.namedItem(name);
                    if (field) formState[name] = field.value;
                });
                const createAccountField = checkoutForm?.elements.namedItem('create_account');
                if (createAccountField) formState.create_account = createAccountField.checked;
                sessionStorage.setItem(checkoutFormStateKey, JSON.stringify(formState));
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const savedFormState = sessionStorage.getItem(checkoutFormStateKey);
            if (!savedFormState || !checkoutForm) return;

            const formState = JSON.parse(savedFormState);
            checkoutCustomerFields.forEach(name => {
                const field = checkoutForm.elements.namedItem(name);
                if (field && formState[name] !== undefined) field.value = formState[name];
            });
            const createAccountField = checkoutForm.elements.namedItem('create_account');
            if (createAccountField && formState.create_account !== undefined) {
                createAccountField.checked = formState.create_account;
            }
            sessionStorage.removeItem(checkoutFormStateKey);
        });

        const isVerify = @json($isVerify);
        const orderForm = document.querySelector('form[action*="order/store"], form[action*="order/confirm"]');

        if (orderForm) {
            orderForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const btn = this.querySelector('button[type="submit"]');
                const origHtml = btn ? btn.innerHTML : '';

                const name = this.querySelector('input[name="name"]')?.value;
                const phone = this.querySelector('input[name="phone"]')?.value;
                const address = this.querySelector('textarea[name="address"]')?.value || this.querySelector('input[name="address"]')?.value;

                if (!name || !phone) {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning('অনুগ্রহ করে নাম এবং মোবাইল নম্বর লিখুন।');
                    }
                    return;
                }

                if (!address) {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning('অনুগ্রহ করে সম্পূর্ণ ঠিকানা লিখুন।');
                    }
                    return;
                }

                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                }

                const formData = new FormData(this);

                fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: formData
                })
                .then(async res => {
                    let data = {};
                    try { data = await res.json(); } catch(err) { data = {}; }

                    if (res.ok && data.success) {
                        if (data.requires_otp) {
                            openCheckoutOtpModal(data.order_id, data.phone, function(otpSuccessData) {
                                window.location.href = otpSuccessData.redirect_url || ('/thank-you/' + data.order_id);
                            });
                        } else if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            window.location.href = '/thank-you/' + (data.order_id || '');
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(data.message || 'অর্ডার প্রসেস করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
                        } else {
                            alert(data.message || 'অর্ডার প্রসেস করতে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
                        }
                    }
                })
                .catch(err => {
                    console.error('Checkout OTP Error:', err);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('সার্ভারে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
                    }
                })
                .finally(() => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = origHtml;
                    }
                });
            });
        }

        // Enforce browser autocomplete / address autofill disable
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[action*="order/store"], form[action*="order/confirm"]');
            if (!form) return;

            form.setAttribute('autocomplete', 'off');
            const inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="radio"]):not([type="checkbox"]):not([type="submit"]), textarea');
            inputs.forEach(el => {
                el.setAttribute('autocomplete', 'off');
                el.setAttribute('autocorrect', 'off');
                el.setAttribute('autocapitalize', 'off');
                el.setAttribute('spellcheck', 'false');
                el.setAttribute('data-lpignore', 'true');
                el.setAttribute('data-form-type', 'other');

                const unlock = () => { el.removeAttribute('readonly'); };
                el.addEventListener('focus', unlock);
                el.addEventListener('pointerdown', unlock);
                el.addEventListener('mousedown', unlock);
                el.addEventListener('touchstart', unlock);
            });

            form.addEventListener('submit', function() {
                inputs.forEach(el => el.removeAttribute('readonly'));
            });
        });
    </script>
@endpush
@push('scripts')
    @include('components.meta-info.pixel-events', ['event' => 'InitiateCheckout', 'data' => ['total' => $total]])
@endpush