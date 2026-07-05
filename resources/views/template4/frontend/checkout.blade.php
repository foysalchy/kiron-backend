@extends('template4.layouts.front')

@section('content')
    <section class="bg-[#F9F9F9] py-2">
        <nav aria-label="Breadcrumb"
            class="container mx-auto px-4 flex flex-wrap items-center pt-2 md:pt-4 gap-1 md:gap-2 text-xs sm:text-sm md:text-base lg:text-lg mb-4 md:mb-6">
            <a href="/" class="text-[#632085] hover:text-[#52166d] transition font-medium">Home</a>
            <span class="text-gray-400">/</span>
            <span class="text-[#632085] hover:text-[#52166d] transition font-medium">Checkout</span>
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
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 md:px-4 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[#632085] transition" />
                                </div>
                                <div>
                                    <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1">Phone
                                        *</label>
                                    <input type="tel" name="phone" id="customer-phone" placeholder="Enter your Number"
                                        required value="{{ old('phone', auth('customer')->user()->phone ?? '') }}"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 md:px-4 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[#632085] transition" />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm md:text-base font-semibold text-gray-700 mb-1">Full Address
                                    *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 md:pl-4 text-gray-400">
                                        <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </span>
                                    <input type="text" name="address" placeholder="Search and enter address detail"
                                        required value="{{ old('address', auth('customer')->user()->address ?? '') }}"
                                        class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 md:pl-11 md:pr-4 md:py-3 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-[#632085] transition" />
                                </div>
                            </div>
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
                                        class="payment-option-label flex items-center justify-between p-3 md:p-4 border border-gray-200 rounded-xl cursor-pointer transition hover:border-[#632085]"
                                        onclick="handlePaymentSelection('{{ $slug }}', '{{ $method->name }}')">

                                        <div class="flex items-center gap-3">
                                            <div
                                                class="radio-ring w-4 h-4 md:w-5 md:h-5 rounded-full border-2 border-gray-300 flex items-center justify-center shrink-0">
                                                <div
                                                    class="radio-dot w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-transparent">
                                                </div>
                                            </div>
                                            <input type="radio" name="payment_method" value="{{ $method->name }}"
                                                class="hidden">
                                            <span
                                                class="text-sm md:text-base font-bold text-[#0f172a]">{{ $method->name }}</span>
                                        </div>

                                        @if ($method->icon)
                                            <img src="{{ $method->icon_url }}" alt="icon" loading="lazy" height="" width=""
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

                    <div class="flex justify-between items-center py-3 md:py-4 border-b border-gray-100">
                        <span class="text-sm md:text-base text-gray-600">Subtotal</span>
                        <span class="text-sm md:text-base font-bold text-gray-900">{{ $setup->currency }}
                            {{ number_format($subtotal) }}</span>
                    </div>

                    <!-- Delivery Area Selector -->
                    <div class="py-4 border-b border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-3">Delivery Area</p>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between cursor-pointer text-sm">
                                <span>Inside Dhaka
                                    ({{ $setup->currency }}{{ number_format($setup->inside_charge) }})</span>
                                <input type="radio" name="delivery_area" value="inside"
                                    onchange="updateCheckoutShipping(this.value)"
                                    {{ $shipping_area == 'inside' ? 'checked' : '' }} class="accent-[#632085] w-4 h-4">
                            </label>
                            <label class="flex items-center justify-between cursor-pointer text-sm">
                                <span>Outside Dhaka
                                    ({{ $setup->currency }}{{ number_format($setup->outside_charge) }})</span>
                                <input type="radio" name="delivery_area" value="outside"
                                    onchange="updateCheckoutShipping(this.value)"
                                    {{ $shipping_area == 'outside' ? 'checked' : '' }} class="accent-[#632085] w-4 h-4">
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-between items-center py-3 md:py-4 border-b border-gray-100">
                        <span class="text-sm md:text-base text-gray-600">Shipping Charge</span>
                        <span class="text-sm md:text-base font-bold text-gray-900">{{ $setup->currency }} <span
                                id="shipping-display">{{ number_format($shipping) }}</span></span>
                    </div>

                    @if ($discount > 0)
                        <div
                            class="flex justify-between items-center py-3 md:py-4 border-b border-gray-100 text-green-600 font-bold">
                            <span>Discount</span>
                            <span>- {{ $setup->currency }} {{ number_format($discount) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center py-4 md:py-5">
                        <span class="text-base md:text-lg font-medium text-gray-500">Total</span>
                        <span class="text-xl md:text-2xl font-extrabold text-[#1147aa]">{{ $setup->currency }} <span
                                id="total-display">{{ number_format($total) }}</span></span>
                    </div>

                    <div class="mt-2 md:mt-4">
                        <button type="submit"
                            class="w-full bg-[#632085] hover:bg-[#52166d] transition text-white font-bold py-3 md:py-4 px-4 md:px-6 rounded-xl flex items-center justify-center gap-2 text-sm md:text-base shadow-md">
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
                opt.classList.remove("border-[#632085]", "bg-purple-50/20");
                opt.classList.add("border-gray-200");
                const ring = opt.querySelector(".radio-ring");
                const dot = opt.querySelector(".radio-dot");
                if (ring) ring.classList.replace("border-[#632085]", "border-gray-300");
                if (dot) dot.classList.replace("bg-[#632085]", "bg-transparent");
            });

            // Highlight Active
            const activeLabel = document.getElementById('label-' + slug);
            if (activeLabel) {
                activeLabel.classList.replace("border-gray-200", "border-[#632085]");
                activeLabel.classList.add("bg-purple-50/20");
                const ring = activeLabel.querySelector(".radio-ring");
                const dot = activeLabel.querySelector(".radio-dot");
                if (ring) ring.classList.replace("border-gray-300", "border-[#632085]");
                if (dot) dot.classList.replace("bg-transparent", "bg-[#632085]");
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
                        document.getElementById('shipping-display').innerText = data.shipping_cost;
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
        document.getElementById('customer-phone').addEventListener('blur', function() {
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
    </script>
@endpush
