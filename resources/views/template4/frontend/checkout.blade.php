@extends('template4.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.checkout-meta', ['setup' => $setup])
@endsection
@section('content')
    <section class="bg-[#F9F9F9] py-2">
        <nav aria-label="Breadcrumb"
            class="container mx-auto px-4 flex   items-center pt-2 md:pt-4 gap-1 md:gap-2 text-xs sm:text-sm md:text-base lg:text-lg mb-4 md:mb-6">
            <a href="/" class="text-[var(--primary-color)] hover:text-[#52166d] transition font-medium">Home</a>
            <span class="text-gray-400">/</span>
            <span class="text-[var(--primary-color)] hover:text-[#52166d] transition font-medium">Checkout</span>
        </nav>
    </section>

    <div class="container mx-auto px-4 py-4 md:py-10">
        <form action="{{ route('order.store') }}" method="POST" enctype="multipart/form-data" id="checkout-main-form">
            @csrf
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-start">

                <!-- ════════ LEFT COLUMN (Shipping & Payment) ════════ -->
                <div class="lg:col-span-8 flex flex-col gap-6">

                    <!-- Card 1: Shipping Address -->
                    <section class="bg-white p-4 sm:p-5 md:p-6 border border-gray-100 rounded-lg shadow-sm">
                        <h2 class="text-lg md:text-xl font-bold text-[#0f172a] mb-4 md:mb-6">Shipping Address</h2>
                        <div class="space-y-4 md:space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1">Full Name
                                        *</label>
                                    <input type="text" name="name" placeholder="Enter your full name" required
                                        value="{{ old('name', auth('customer')->user()->name ?? '') }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 md:px-4 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] transition" />
                                </div>
                                <div>
                                    <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1">Phone
                                        *</label>
                                    <input type="tel" name="phone" id="customer-phone" placeholder="Enter your Number"
                                        required value="{{ old('phone', auth('customer')->user()->phone ?? '') }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 md:px-4 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] transition" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">Email (Optional)</label>
                                    <input type="email" name="email" placeholder="Your email address"
                                        value="{{ old('email', auth('customer')->user()->email ?? '') }}"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-gray-700">City <span
                                            class="text-red-500">*</span></label>
                                    <input type="text" name="district" placeholder="Your city" required
                                        value="{{ old('district', auth('customer')->user()->district ?? '') }}"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[#016738] focus:ring-4 focus:ring-green-50 transition-all">
                                </div>
                            </div>
                            <div class="space-y-2 mt-5">
                                <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1">Full Address
                                    *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 md:pl-4 text-gray-400">
                                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <input type="text" name="address" placeholder="Search and enter address detail" required
                                        value="{{ old('address', auth('customer')->user()->address ?? '') }}"
                                        class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 md:pl-11 md:pr-4 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] transition" />
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

                    <!-- Card 2: Select Payment Method (Design Preserved) -->
                    <section class="bg-white p-4 sm:p-5 md:p-6 border border-gray-100 rounded-lg shadow-sm">
                        <h2 class="text-lg md:text-xl font-bold text-[#0f172a] mb-4 md:mb-6">Select Payment method</h2>
                        <div class="space-y-3 md:space-y-4">
                            @foreach ($paymentMethods as $method)
                                @php $slug = strtolower(trim($method->name)); @endphp
                                <div class="payment-option-wrapper">
                                    <label id="label-{{ $slug }}"
                                        class="payment-option-label flex items-center justify-between p-3 md:p-4 border border-gray-200 rounded-xl cursor-pointer transition hover:border-[var(--primary-color)]"
                                        onclick="handlePaymentSelection('{{ $slug }}', '{{ $method->name }}')">

                                        <div class="flex items-center gap-3">
                                            <div
                                                class="radio-ring w-4 h-4 md:w-5 md:h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0">
                                                <div class="radio-dot w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-transparent">
                                                </div>
                                            </div>
                                            <input type="radio" name="payment_method" value="{{ $method->name }}"
                                                class="hidden">
                                            <span
                                                class="text-sm md:text-base font-bold text-[#0f172a] capitalize">{{ $method->name }}</span>
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
                <aside
                    class="lg:col-span-4 bg-white p-4 sm:p-5 md:p-6 border border-gray-100 rounded-lg shadow-sm lg:sticky lg:top-24">
                    <h3 class="text-lg md:text-xl font-bold text-[#0f172a] mb-4 md:mb-6">Order Summary</h3>

                    <div class="space-y-4 mb-8">
                        @foreach ($cartContent as $item)
                            <div class="bg-[#F9FAFB] rounded-xl p-4 flex items-center gap-4">
                                <!-- Actual Product Image -->
                                <div class="w-16 h-16 bg-white rounded-lg overflow-hidden border border-gray-100 shrink-0">
                                    <img src="{{ $item->options->thumbnail ?? asset('./images/template1/frontend/default.webp') }}"
                                        alt="{{ $item->name }}"
                                        class="w-full h-full object-cover">
                                </div>

                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-gray-800 leading-tight mb-1">
                                        {{ $item->name }}
                                    </h4>

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
                                        {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency . number_format($item->price) : number_format($item->price) . $setup->currency }}
                                    </p>
                                </div>

                                <!-- Quantity Display -->
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-gray-500">Qty: {{ $item->qty }}</span>
                                    <a href="{{ route('cart.remove', $item->rowId) }}"
                                        aria-label="Remove {{ $item->name }} from cart"
                                        class="checkout-cart-remove text-red-400 hover:text-red-600">
                                        <i class="far fa-trash-alt text-xs" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @php $isL = ($setup->currency_position ?? 'left') == 'left'; @endphp
                    <!-- 2.5 Coupon Section -->
                    <div class="mb-6 border-t border-gray-100 pt-6">
                        <label class="text-sm font-bold text-gray-600 block mb-2">Coupon Code</label>
                        <div class="flex gap-2">
                            <input type="text" id="coupon-code-input" placeholder="Enter Coupon Code"
                                value="{{ session()->has('coupon') ? session('coupon')['coupon_code'] : '' }}"
                                class="flex-1 border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-[#016738] transition-all"
                                {{ session()->has('coupon') ? 'readonly' : '' }}>

                            <button type="button" id="remove-coupon-btn" onclick="removeCoupon()" aria-label="Remove coupon" title="Remove coupon"
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

                    <div class="space-y-3 border-t border-gray-100 pt-4">

                        <!-- ১. সাবটোটাল (প্রোডাক্টের মোট দাম) -->
                        <div class="flex justify-between items-center text-gray-700">
                            <span class="text-sm md:text-base font-medium">Subtotal</span>
                            <span class="text-sm md:text-base font-bold text-gray-900">
                                {{ $isL ? $setup->currency : '' }} {{ number_format($subtotal) }} {{ !$isL ? $setup->currency : '' }}
                            </span>
                        </div>

                        <!-- ২. শিপিং চার্জ -->
                        <div class="flex justify-between items-center text-gray-700">
                            <span class="text-sm md:text-base font-medium">Shipping Charge</span>
                            <span class="text-sm md:text-base font-bold text-gray-900">
                                @if(isset($is_free_delivery) && $is_free_delivery)
                                    Free
                                @else
                                    {{ $isL ? $setup->currency : '' }} <span id="shipping-display">{{ number_format($shipping, 2) }}</span> {{ !$isL ? $setup->currency : '' }}
                                @endif
                            </span>
                        </div>

                        <!-- ৩. ডিসকাউন্ট (যদি থাকে) -->
                        <div id="discount-row" class="flex justify-between items-center text-green-700 font-bold {{ $discount > 0 ? '' : 'hidden' }}">
                            <span>Discount <span id="discount-code-display">{{ session()->has('coupon') ? '(' . session('coupon')['coupon_code'] . ')' : '' }}</span></span>
                            <span>- {{ $isL ? $setup->currency : '' }} <span id="discount-display">{{ number_format($discount) }}</span> {{ !$isL ? $setup->currency : '' }}</span>
                        </div>

                        <!-- ৪. সর্বমোট (Total) -->
                        <div class="flex justify-between items-center py-4 border-t border-gray-100 mt-2">
                            <span class="text-base md:text-lg font-black text-gray-800">Total Payable</span>
                            <span class="text-xl md:text-2xl font-extrabold text-[#1147aa]">
                                {{ $isL ? $setup->currency : '' }} <span id="total-display">{{ number_format($total) }}</span> {{ !$isL ? $setup->currency : '' }}
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
    </script>
@endpush
@push('scripts')
    @include('components.meta-info.pixel-events', ['event' => 'InitiateCheckout', 'data' => ['total' => $total]])
@endpush
