@extends('template3.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.checkout-meta', ['setup' => $setup])
@endsection
@section('content')
<style>
    @media (max-width: 640px) {
        .fixed-co {
            position: fixed !important;
            left: 0;
            right: 0;
            width: 90% !important;
            margin: 0 auto !important;
            bottom: 16px !important;
            z-index: 9999 !important;
            box-sizing: border-box !important;
            animation: smoothFloatIn 0.25s ease-out;
        }

        @keyframes smoothFloatIn {
            from {
                opacity: 0.6;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    }
    h2{
        border-left: 4px solid #0b5abe;
  padding-left: 5px;
    }
    .animate-shake {
        animation: rsiShaker 4s cubic-bezier(.35, .06, .2, .98) infinite both;
    }
@keyframes rsiShaker {
  0%,
  25%,
  to {
    transform:scaleX(1)
  }
  2.5%,
  5% {
    transform:scale3d(.97,.97,.97)rotate(-1deg)
  }
  7.5%,
  12.5%,
  17.5%,
  22.5% {
    transform:scale3d(1.03,1.03,1.03)rotate(1deg)
  }
  10%,
  15%,
  20% {
    transform:scale3d(1.03,1.03,1.03)rotate(-1deg)
  }
}
</style>
    <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0 pb-24 lg:pb-6">
        <h1 class="px-6 text-2xl font-bold text-gray-900 mb-2 tracking-tight">Checkout</h1>

        <form action="{{ route('order.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">

                <!-- LEFT COLUMN: Customer & Payment Info -->
                <div class="lg:col-span-2 lg:order-1 space-y-2">
                    <!-- Mobile Order Items Card -->
                    <div id="checkout-mobile-items-container" class="block lg:hidden bg-white rounded-xl shadow-xs overflow-hidden border border-gray-200">
                        <div class="p-4">
                            <h2 class="text-[16px] font-bold text-gray-800 leading-tight">
                                Your Order Items
                            </h2>
                        </div>
                        <!-- Product Row in Summary -->
                        <div class="space-y-0 mb-0">
                            @foreach ($cartContent as $item)
                                <div class="bg-white border-b border-gray-300 p-4 py-2 flex items-start gap-3">
                                    <!-- Actual Product Image -->
                                    <div class="w-12 h-12 bg-white rounded-lg overflow-hidden border border-gray-100 shrink-0 mt-0.5">
                                        <img src="{{ $item->options->thumbnail ?? asset('./images/template1/frontend/product-thumbnail.webp') }}"
                                            alt="{{ $item->name ?? 'Product Image' }}" class="w-full h-full object-cover">
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-bold text-gray-800 leading-tight mb-1 truncate">
                                            {{ $item->name }}
                                        </h3>

                                        @if ($item->options->has('attributes') && count($item->options->attributes) > 0)
                                            <div class="flex flex-wrap gap-1 mb-1">
                                                @foreach ($item->options->attributes as $key => $value)
                                                    <span
                                                        class="bg-orange-50 text-[var(--primary-color)] text-[10px] font-bold px-1.5 py-0.5 rounded uppercase">
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
                                                {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}{{ number_format($item->price) }}{{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
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

                                    <!-- Remove Button (Top-Right as marked) -->
                                    <div class="shrink-0 self-start pl-1 pt-0.5">
                                        <button type="button" onclick="removeCheckoutItem('{{ $item->rowId }}')"
                                            aria-label="Remove item" title="Remove item"
                                            class="checkout-cart-remove text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1 bg-transparent border-0 cursor-pointer p-0.5">
                                            <i class="far fa-trash-alt text-[11px]" aria-hidden="true"></i>
                                            <span>Remove</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>

                    <!-- 1. Customer Information Card -->
                    <div class="bg-white rounded-xl shadow-xs overflow-hidden border border-gray-200">
                        <div class="p-4">
                            <h2 class="text-[16px] font-bold text-gray-800 leading-tight">
                                Delivery Address
                            </h2>
                        </div>
                        <div class="p-4 space-y-3 pt-0">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="space-y-2">
                                  
                                    <input type="text" name="name" placeholder="আপনার নাম *" required
                                        value="{{ old('name', auth('customer')->user()->name ?? '') }}"
                                        autocomplete="off
                                        class="w-full px-4 py-2 rounded-lg border border-gray-400 outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all">
                                </div>
                                <div class="space-y-2">
                                    
                                    <input type="tel" name="phone" placeholder="আপনার মোবাইল নম্বর *" required
                                    autocomplete="off
                                        value="{{ old('phone', auth('customer')->user()->phone ?? '') }}"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-400 outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all">
                                </div>
                            </div>
                            @php
                                $showEmail = $setup->checkout_show_email ?? true;
                                $showCity = $setup->checkout_show_city ?? true;
                            @endphp

                            @if($showEmail || $showCity)
                                <div class="grid grid-cols-1 {{ ($showEmail && $showCity) ? 'md:grid-cols-2' : '' }} gap-3">
                                    @if($showEmail)
                                        <div class="space-y-2">
                                            <input type="email" name="email" placeholder="ইমেইল (ঐচ্ছিক)"
                                                value="{{ old('email', auth('customer')->user()->email ?? '') }}"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-400 outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all">
                                        </div>
                                    @endif

                                    @if($showCity)
                                        <div class="space-y-2">
                                            <input type="text" name="district" placeholder="আপনার শহর / জেলা *" required
                                                value="{{ old('district', auth('customer')->user()->district ?? '') }}"
                                                class="w-full px-4 py-2 rounded-lg border border-gray-400 outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all">
                                        </div>
                                    @endif
                                </div>
                            @endif
                            <div class="space-y-2">
                               
                                <textarea name="address" placeholder="আপনার পূর্ণাঙ্গ ঠিকানা *" rows="2" required
                                autocomplete="off
                                    class="w-full px-3 py-2 rounded-lg border border-gray-400 outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all">{{ old('address', auth('customer')->user()->address ?? '') }}</textarea>
                            </div>

                            <!-- Checkboxes -->
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
                    </div>

                    <!-- 2. Payment Method Card -->
                    <div class="space-y-3 p-4 bg-white rounded-xl shadow-sm mt-0 border border-gray-200 mb-0">
                        <h3 class="text-[14px] font-bold text-gray-800 mb-2">Select Payment Method</h3>

                        @foreach ($paymentMethods as $method)
                            @php $slug = strtolower(trim($method->name)); @endphp
                            <div class="payment-option border-b border-gray-50 last:border-0 pb-0">
                                <label
                                    class="flex items-center space-x-4 p-2 border-2 border-gray-100 rounded-xl cursor-pointer hover:border-[var(--primary-color)] has-[:checked]:border-[var(--primary-color)] has-[:checked]:bg-orange-50 transition-all">
                                    <input type="radio" name="payment_method" value="{{ $method->name }}"
                                        onchange="handlePaymentSelection('{{ $slug }}', '{{ $method->name }}')"
                                        class="w-5 h-5 accent-[var(--primary-color)]">
                                    <span class="text-sm font-medium text-gray-700 group-hover:text-blacke">{{ $method->name }}</span>
                                </label>

                                <div id="checkout-anchor-{{ $slug }}" class="mt-4 hidden transition-all">
                                    <div class="bg-gray-50 p-4 rounded-xl border border-orange-100">
                                        <div class="form-injection-point"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Payment Forms Repository --}}
                    <div id="forms-repository" class="hidden">
                        <x-template1.payment-checkout :methods="$paymentMethods" :currency="$setup->currency" />
                    </div>
                </div>

                <!-- RIGHT COLUMN: Delivery & Summary -->
                <div class="lg:col-span-1 lg:order-2 space-y-6" id="checkout-summary-container">
                    <div class="bg-gray-100 rounded-lg border border-gray-200 shadow-xs p-5 md:p-4 lg:sticky lg:top-24">

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

                        <!-- 1. Delivery Selection (Synced with Logic) -->
                        @if(!isset($is_free_delivery) || !$is_free_delivery)
                            <div class="mb-3">
                                <h2 class="text-[14px] md:text-xl font-semibold leading-none tracking-tight mb-4">Select Delivery
                                    Method</h2>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="delivery_area" value="inside"
                                            onchange="updateCheckoutShipping(this.value)" {{ $shipping_area == 'inside' ? 'checked' : '' }} class="w-4 h-4 accent-black">
                                        <span class="text-sm font-medium text-gray-700 group-hover:text-black">
                                            Inside Dhaka
                                            ({{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}
                                            {{ number_format($setup->inside_charge, 2) }}
                                            {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }})
                                        </span>
                                    </label>
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="radio" name="delivery_area" value="outside"
                                            onchange="updateCheckoutShipping(this.value)" {{ $shipping_area == 'outside' ? 'checked' : '' }} class="w-4 h-4 accent-black">
                                        <span class="text-sm font-medium text-gray-700 group-hover:text-black">
                                            Outside Dhaka
                                            ({{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}
                                            {{ number_format($setup->outside_charge, 2) }}
                                            {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }})
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endif

                        <!-- Product Row in Summary (Desktop) -->
                        <div class="space-y-3 mb-6 hidden lg:block">
                            @foreach ($cartContent as $item)
                                <div class="bg-white rounded-xl p-3 flex items-center gap-3 border border-gray-200">
                                    <!-- Actual Product Image -->
                                    <div class="w-14 h-14 bg-white rounded-lg overflow-hidden border border-gray-100 shrink-0">
                                        <img src="{{ $item->options->thumbnail ?? asset('./images/template1/frontend/product-thumbnail.webp') }}"
                                            alt="{{ $item->name ?? 'Product Image' }}" class="w-full h-full object-cover">
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-bold text-gray-800 leading-tight mb-1 truncate">
                                            {{ $item->name }}
                                        </h3>

                                        @if ($item->options->has('attributes') && count($item->options->attributes) > 0)
                                            <div class="flex flex-wrap gap-1 mb-1">
                                                @foreach ($item->options->attributes as $key => $value)
                                                    <span
                                                        class="bg-orange-50 text-[var(--primary-color)] text-[10px] font-bold px-1.5 py-0.5 rounded uppercase">
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
                                            {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}{{ number_format($item->price) }}{{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                                        </p>
                                    </div>

                                    <!-- Quantity Controls (cart.blade.php rounded-lg style) -->
                                    <div class="flex flex-col items-end gap-1.5 shrink-0">
                                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white shadow-xs">
                                            <button type="button" onclick="updateCheckoutQty('{{ $item->rowId }}', -1, this)"
                                                aria-label="Decrease quantity"
                                                class="px-2.5 py-1 text-gray-600 hover:text-[var(--primary-color)] bg-transparent transition-colors cursor-pointer font-bold text-base">-</button>
                                            <span class="qty-{{ $item->rowId }} w-8 text-center text-xs sm:text-sm font-bold text-gray-800">{{ $item->qty }}</span>
                                            <button type="button" onclick="updateCheckoutQty('{{ $item->rowId }}', 1, this)"
                                                aria-label="Increase quantity"
                                                class="px-2.5 py-1 text-gray-600 hover:text-[var(--primary-color)] bg-transparent transition-colors cursor-pointer font-bold text-base">+</button>
                                        </div>
                                        <button type="button" onclick="removeCheckoutItem('{{ $item->rowId }}')"
                                            aria-label="Remove item" title="Remove item"
                                            class="checkout-cart-remove text-red-500 hover:text-red-700 text-xs font-medium flex items-center gap-1 bg-transparent border-0 cursor-pointer">
                                            <i class="far fa-trash-alt text-xs" aria-hidden="true"></i>
                                            <span>Remove</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        
                        <!-- 2.5 Coupon Section -->
                        <div class="mb-6 border-t border-gray-100 pt-6 hidden">
                            <label class="text-sm font-bold text-gray-600 block mb-2">Coupon Code</label>
                            <div class="flex gap-2">
                                <input type="text" id="coupon-code-input" placeholder="Enter Coupon Code"
                                    value="{{ session()->has('coupon') ? session('coupon')['coupon_code'] : '' }}"
                                    class="flex-1 bg-white border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-[#016738] transition-all"
                                    {{ session()->has('coupon') ? 'readonly' : '' }}>

                                <button type="button" id="remove-coupon-btn" onclick="removeCoupon()"
                                    aria-label="Remove coupon" title="Remove coupon"
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

                        <!-- 3. Cost Breakdown -->
                        <div class="space-y-1 border-t border-gray-100 pt-1">
                            <div class="flex justify-between items-center text-gray-700">
                                <span class="text-md font-medium">Subtotal:</span>
                                <span class="text-md font-bold text-gray-900">
                                    {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}
                                    {{ number_format($subtotal) }}
                                    {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                                </span>
                            </div>

                            <div id="discount-row"
                                class="flex justify-between items-center text-green-600 {{ $discount > 0 ? '' : 'hidden' }}">
                                <span class="text-md font-medium">Discount <span
                                        id="discount-code-display">{{ session()->has('coupon') ? '(' . session('coupon')['coupon_code'] . ')' : '' }}</span>:</span>
                                <span class="text-md font-bold">-
                                    {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }} <span
                                        id="discount-display">{{ number_format($discount) }}</span>
                                    {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}</span>
                            </div>

                            <div class="flex justify-between items-center text-gray-700">
                                <span class="text-md font-medium">Delivery Charge:</span>
                                <span class="text-md font-bold text-gray-900">
                                    @if(isset($is_free_delivery) && $is_free_delivery)
                                        Free
                                    @else
                                        {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }} <span
                                            id="shipping-display">{{ number_format($shipping, 2) }}</span>
                                        {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between items-center border-t border-gray-100 pt-0">
                                <span class="text-lg font-black text-gray-900">Total to Pay:</span>
                                <span class="text-xl font-bold text-[var(--primary-color)]">
                                    {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }} <span
                                        id="total-display">{{ number_format($total) }}</span>
                                    {{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- 4. Confirm Button -->
                        <div id="confirm-btn-container" class="mt-8 min-h-[58px]">
                            <button type="submit" id="confirm-order-btn"
                                class="w-full animate-shake fixed-co primary-bg hover:bg-[#e65f00] text-primary font-semibold py-4 text-md rounded-lg shadow-lg shadow-orange-100 transition-all active:scale-[0.98]">
                                Confirm Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- hidden form for shipping update --}}
        {{-- <form id="shipping-update-form" action="{{ route('cart.shipping') }}" method="POST" style="display:none;">
            @csrf
            <input type="hidden" name="area" id="shipping-area-input">
        </form> --}}
    </section>
    <x-template1.payment-checkout :methods="$paymentMethods" :currency="$setup->currency" />
@endsection


@push('scripts')
    <script>
        function refreshCartUI() {
            fetch(window.location.href)
                .then(res => res.text())
                .then(html => {
                    const temp = document.createElement('div');
                    temp.innerHTML = html;

                    const newContainer = temp.querySelector('#checkout-summary-container');
                    const currentContainer = document.getElementById('checkout-summary-container');

                    if (newContainer && currentContainer) {
                        currentContainer.innerHTML = newContainer.innerHTML;

                        // Extract new checkout total from the newly rendered HTML script block
                        const match = html.match(/let _checkoutTotal = (.*?);/);
                        if (match && match[1]) {
                            _checkoutTotal = parseFloat(match[1]);
                        }
                    } else if (currentContainer && !newContainer) {
                        window.location.href = "{{ route('shop.index') }}";
                        return;
                    }

                    const newMobile = temp.querySelector('#checkout-mobile-items-container');
                    const currentMobile = document.getElementById('checkout-mobile-items-container');
                    if (newMobile && currentMobile) {
                        currentMobile.innerHTML = newMobile.innerHTML;
                    }
                })
                .catch(err => console.error('Checkout refresh failed:', err));
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
                            el.parentElement.classList.remove('hidden');
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
            fetch(`/cart/remove/${rowId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.cart_count !== undefined) {
                    if (data.cart_count === 0) {
                        window.location.href = "{{ route('shop.index') }}";
                        return;
                    }
                    document.querySelectorAll('.cart-count-nav').forEach(el => {
                        el.innerText = data.cart_count;
                    });
                }
                refreshCartUI();
                if (typeof refreshMiniCart === 'function') refreshMiniCart();
                if (typeof toastr !== 'undefined') toastr.error(data.message || 'Item removed');
            })
            .catch(err => {
                console.error(err);
                if (typeof toastr !== 'undefined') toastr.error('Failed to remove item.');
            });
        }

        let _activeDraftOrderId = @json($draftOrderId ?? null);
        let _checkoutTotal = {{ $total }};
        let _checkoutSlug = '';


        // ── Page load: auto-select first method ──
        document.addEventListener('DOMContentLoaded', function () {
            const firstRadio = document.querySelector('input[name="payment_method"]');
            if (firstRadio) {
                firstRadio.checked = true;
                _checkoutSlug = firstRadio.value;
                handlePaymentSelection(
                    firstRadio.value,
                    firstRadio.closest('label')?.querySelector('span.text-md')?.innerText?.trim() ?? firstRadio
                        .value
                );
            }
        });

        // ── Radio label click → open modal ──
        function checkoutSelectMethod(slug, name) {
            if (!_activeDraftOrderId) {
                toastr.error('Please enter your Phone and Address first to generate Order ID!');
                document.querySelector('input[name="phone"]').focus();
                return;
            }
            _checkoutSlug = slug;

            // Sync radio
            const radio = document.querySelector(`input[name="payment_method"][value="${slug}"]`);
            if (radio) radio.checked = true;

            if (slug.includes('cash') || slug.includes('cod')) {
                highlightMethod(slug, name);
                return;
            }

            // Open modal
            openPaymentModal(_activeDraftOrderId, _checkoutTotal);


        }

        function reopenPaymentModal() {
            openPaymentModal('checkout', _checkoutTotal);
            if (_checkoutSlug) {
                setTimeout(() => selectPaymentMethod(_checkoutSlug, _checkoutSlug), 80);
            }
        }

        // ── Radio border + badge highlight ──
        function highlightMethod(slug, name) {
            document.querySelectorAll('.payment-method-label').forEach(l => {
                l.classList.remove('border-[var(--primary-color)]', 'bg-orange-50');
                l.classList.add('border-gray-200');
            });
            document.querySelectorAll('[id^="badge-"]').forEach(b => b.classList.add('hidden'));

            const lbl = document.getElementById('label-' + slug);
            if (lbl) {
                lbl.classList.remove('border-gray-200');
                lbl.classList.add('border-[var(--primary-color)]', 'bg-orange-50');
            }
            const badge = document.getElementById('badge-' + slug);
            if (badge) badge.classList.remove('hidden');

            const methodName = document.getElementById('selected-method-name');
            const methodDisplay = document.getElementById('selected-method-display');
            if (methodName) methodName.innerText = name ?? slug;
            if (methodDisplay) methodDisplay.classList.remove('hidden');
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
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
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
                .catch(err => console.error(err));
        }
    </script>
    <script>
        const checkoutCustomerFields = ['name', 'phone', 'email', 'district', 'address'];
        const checkoutFormStateKey = 'template3-checkout-customer-form';
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
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const phoneInput = document.querySelector('input[name="phone"]');
            const nameInput = document.querySelector('input[name="name"]');
            const addressInput = document.querySelector('textarea[name="address"]');

            function saveDraft() {
                const phoneInput = document.querySelector('input[name="phone"]');
                const nameInput = document.querySelector('input[name="name"]');
                const addressInput = document.querySelector('textarea[name="address"]');

                let phone = phoneInput.value;
                if (phone.length >= 11) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    fetch("{{ route('order.partial') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            phone: phone,
                            name: nameInput.value,
                            address: addressInput.value
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                _activeDraftOrderId = data.order_id;
                                console.log("Draft Updated. ID:", _activeDraftOrderId);
                            }
                        });
                } else {
                    console.log("Phone number must be at least 11 digits to save draft.");
                }
            }

            phoneInput.addEventListener('blur', saveDraft);

            @auth('customer')
                saveDraft();
            @endauth
            });
    </script>
    <script>
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

                        toastr.success(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    </script>
@endpush
@push('scripts')
    <script>
        function previewImages(input, slug) {
            const grid = document.getElementById(slug + '-preview');
            const hidden = document.getElementById(slug + '-hidden-files');
            grid.innerHTML = '';
            hidden.innerHTML = '';

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    grid.innerHTML = `<div class="relative w-16 h-16 border rounded overflow-hidden">
                    <img src="${e.target.result}" alt="Uploaded payment proof preview" class="w-full h-full object-cover" loading="lazy" width="800" height="800">
                </div>`;
                };
                reader.readAsDataURL(input.files[0]);

                const dt = new DataTransfer();
                dt.items.add(input.files[0]);
                const newInp = document.createElement('input');
                newInp.type = 'file';
                newInp.name = 'screenshots[]';
                newInp.files = dt.files;
                newInp.classList.add('hidden');
                hidden.appendChild(newInp);
            }
        }
        document.querySelector('form[action*="order/store"], form[action*="order/confirm"]').addEventListener('submit',
            function (e) {
                const btn = this.querySelector('button[type="submit"]');

                const name = this.querySelector('input[name="name"]').value;
                const phone = this.querySelector('input[name="phone"]').value;
                if (!name || !phone) return;

                setTimeout(() => {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                }, 50);
            });

        // Floating Confirm Button on Mobile: in normal flow when summary is in view; floating when above or below
        let _isFloating = true;
        function updateFloatingConfirmButton() {
            if (window.innerWidth > 640) {
                const b = document.getElementById('confirm-order-btn');
                if (b) b.classList.remove('fixed-co');
                return;
            }
            const container = document.getElementById('confirm-btn-container');
            const btn = document.getElementById('confirm-order-btn');
            if (!container || !btn) return;

            const rect = container.getBoundingClientRect();
            const triggerThreshold = window.innerHeight - 30;

            // Hysteresis buffer to eliminate stuttering/flicker around transition edge
            if (_isFloating && rect.top <= triggerThreshold && rect.bottom >= 0) {
                _isFloating = false;
                btn.classList.remove('fixed-co');
            } else if (!_isFloating && (rect.top > triggerThreshold + 30 || rect.bottom < -30)) {
                _isFloating = true;
                btn.classList.add('fixed-co');
            }
        }

        window.addEventListener('scroll', updateFloatingConfirmButton, { passive: true });
        window.addEventListener('resize', updateFloatingConfirmButton);
        document.addEventListener('DOMContentLoaded', updateFloatingConfirmButton);
    </script>
@endpush
@push('scripts')
    @include('components.meta-info.pixel-events', ['event' => 'InitiateCheckout', 'data' => ['total' => $total]])
@endpush
