@extends('template5.layouts.front')
@section('meta')
@include('components.meta-info.ecommerce-meta.checkout-meta', ['setup' => $setup])
@endsection
@section('content')
<section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">
    <h1 class="text-2xl font-black text-gray-900 mb-8 tracking-tight">Checkout</h1>

    <form action="{{ route('order.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- LEFT COLUMN: Customer & Payment Info -->
            <div class="lg:col-span-2 lg:order-1 space-y-6">

                <!-- 1. Customer Information Card -->
                <div class="bg-white rounded-xl  shadow-xs overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-semibold text-gray-800 leading-tight">
                            To confirm your order, enter your name, address, phone number and click confirm
                        </h2>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Your Name <span
                                        class="text-[var(--primary-color)]">*</span></label>
                                <input type="text" name="name" placeholder="Enter your full name" required
                                    value="{{ old('name', auth('customer')->user()->name ?? '') }}"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[var(--primary-color)] focus:ring-4 focus:ring-[var(--primary-color)]/20 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Phone Number <span
                                        class="text-[var(--primary-color)]">*</span></label>
                                <input type="tel" name="phone" placeholder="Your mobile number" required
                                    value="{{ old('phone', auth('customer')->user()->phone ?? '') }}"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-[var(--primary-color)] focus:ring-4 focus:ring-[var(--primary-color)]/20 transition-all">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">Email (Optional)</label>
                                <input type="email" name="email" placeholder="Your email address"
                                    value="{{ old('email', auth('customer')->user()->email ?? '') }}"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium text-gray-700">City <span
                                        class="text-[var(--primary-color)]">*</span></label>
                                <input type="text" name="district" placeholder="Your city" required
                                    value="{{ old('district', auth('customer')->user()->district ?? '') }}"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-200 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-50 transition-all">
                            </div>
                        </div>
                        <div class="space-y-2 mt-5">
                            <label class="text-sm font-medium text-gray-700">Your Address <span
                                    class="text-[var(--primary-color)]">*</span></label>
                            <textarea name="address" placeholder="Your address" rows="3" required
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 outline-none focus:border-[var(--primary-color)] focus:ring-4 focus:ring-[var(--primary-color)]/20 transition-all">{{ old('address', auth('customer')->user()->address ?? '') }}</textarea>
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
                <div class="space-y-3 p-6 bg-white rounded-xl   shadow-sm mt-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Select Payment Method</h3>

                        @foreach ($paymentMethods as $method)
                            @php $slug = strtolower(trim($method->name)); @endphp
                            <div class="payment-option border-b border-gray-50 last:border-0 pb-4">
                                <label
                                    class="flex items-center space-x-4 p-4 border-2 border-gray-100 rounded-xl cursor-pointer hover:border-[var(--primary-color)] has-[:checked]:border-[var(--primary-color)] has-[:checked]:bg-orange-50 transition-all">
                                    <input type="radio" name="payment_method" value="{{ $method->name }}"
                                        onchange="handlePaymentSelection('{{ $slug }}', '{{ $method->name }}')"
                                        class="w-5 h-5 accent-[var(--primary-color)]">
                                    <span class="text-md font-medium text-gray-700 capitalize">{{ $method->name }}</span>
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
            <div class="lg:col-span-1 lg:order-2 space-y-6">
                <div class="bg-white rounded-lg shadow-xs p-5 md:p-6 lg:sticky lg:top-24">

                    <!-- 1. Delivery Selection (Synced with Logic) -->
                    <div class="mb-8">
                        <h2 class="text-lg md:text-xl font-semibold leading-none tracking-tight mb-4">Select Delivery
                            Method</h2>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="delivery_area" value="inside"
                                    onchange="updateCheckoutShipping(this.value)"
                                    {{ $shipping_area == 'inside' ? 'checked' : '' }} class="w-4 h-4 accent-black">
                                <span class="text-sm font-medium text-gray-700 group-hover:text-black">
                                    Regular Delivery(@if(($setup->currency_position ?? 'left') == 'left'){{ $setup->currency }} {{ number_format($setup->inside_charge, 0) }}@else{{ number_format($setup->inside_charge, 0) }} {{ $setup->currency }}@endif)
                                </span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="delivery_area" value="outside"
                                    onchange="updateCheckoutShipping(this.value)"
                                    {{ $shipping_area == 'outside' ? 'checked' : '' }} class="w-4 h-4 accent-black">
                                <span class="text-sm font-medium text-gray-700 group-hover:text-black">
                                    Quick Bite(@if(($setup->currency_position ?? 'left') == 'left'){{ $setup->currency }} {{ number_format($setup->outside_charge, 0) }}@else{{ number_format($setup->outside_charge, 0) }} {{ $setup->currency }}@endif)
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Product Row in Summary -->
                    <div class="space-y-4 mb-8">
                        @foreach ($cartContent as $item)
                        <div class="bg-[#F9FAFB] rounded-xl p-4 flex items-center gap-4">
                            <!-- Actual Product Image -->
                            <div
                                class="w-16 h-16 bg-white rounded-lg overflow-hidden border border-gray-100 shrink-0">
                                <img src="{{ $item- loading="lazy" width="800" height="800">options->thumbnail ?? asset('./images/template1/frontend/default.webp') }}"
                                    alt="{{ $item->name }}"
                                    class="w-full h-full object-cover">
                            </div>

                            <div class="flex-1">
                                <p class="text-sm font-bold text-gray-800 leading-tight mb-1">
                                    {{ $item->name }}
                                </p>

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
                                    @if(($setup->currency_position ?? 'left') == 'left'){{ $setup->currency }}{{ number_format($item->price) }}@else{{ number_format($item->price) }}{{ $setup->currency }}@endif
                                </p>
                            </div>

                        <!-- Quantity Controls -->
                            <div class="flex flex-col items-end gap-2">
                                <div class="flex items-center border border-gray-200 rounded-full h-8 w-24 px-1">
                                    <button type="button" onclick="updateCheckoutQty('{{ $item->rowId }}', -1, this)"
                                        class="w-7 h-6 flex items-center justify-center text-gray-600 hover:text-[var(--primary-color)] font-bold text-lg leading-none rounded-full bg-gray-50 hover:bg-gray-100 transition-colors">-</button>
                                    <span id="qty-{{ $item->rowId }}" class="flex-1 text-center text-xs font-bold">{{ $item->qty }}</span>
                                    <button type="button" onclick="updateCheckoutQty('{{ $item->rowId }}', 1, this)"
                                        class="w-7 h-6 flex items-center justify-center text-gray-600 hover:text-[var(--primary-color)] font-bold text-lg leading-none rounded-full bg-gray-50 hover:bg-gray-100 transition-colors">+</button>
                                </div>
                                <a href="{{ route('cart.remove', $item->rowId) }}"
                                    class="text-red-700 hover:text-red-800 text-xs">
                                    <i class="far fa-trash-alt"></i> Remove
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
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

                    <!-- 3. Cost Breakdown -->
                    <div class="space-y-4 border-t border-gray-100 pt-6">
                        <div class="flex justify-between items-center text-gray-700">
                            <span class="text-md font-medium">Subtotal:</span>
                            <span class="text-md font-bold text-gray-900">@if(($setup->currency_position ?? 'left') == 'left'){{ $setup->currency }} <span id="subtotal-display">{{ number_format($subtotal) }}</span>@else<span id="subtotal-display">{{ number_format($subtotal) }}</span> {{ $setup->currency }}@endif</span>
                        </div>

                        <div id="discount-row" class="flex justify-between items-center text-green-600 {{ $discount > 0 ? '' : 'hidden' }}">
                            <span class="text-md font-medium">Discount <span id="discount-code-display">{{ session()->has('coupon') ? '(' . session('coupon')['coupon_code'] . ')' : '' }}</span>:</span>
                            <span class="text-md font-bold">- @if(($setup->currency_position ?? 'left') == 'left'){{ $setup->currency }}@endif
                                <span id="discount-display">{{ number_format($discount) }}</span>@if(($setup->currency_position ?? 'left') == 'right') {{ $setup->currency }}@endif
                            </span>
                        </div>

                        <div class="flex justify-between items-center text-gray-700">
                            <span class="text-md font-medium">Delivery Charge:</span>
                            <span class="text-md font-bold text-gray-900">
                                @if(($setup->currency_position ?? 'left') == 'left')
                                    {{ $setup->currency }} <span id="shipping-display">{{ number_format($shipping) }}</span>
                                @else
                                    <span id="shipping-display">{{ number_format($shipping) }}</span> {{ $setup->currency }}
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between items-center border-t border-gray-100 pt-4">
                            <span class="text-lg font-black text-gray-900">Total to Pay:</span>
                            <span class="text-xl font-bold text-gray-900">
                                @if(($setup->currency_position ?? 'left') == 'left')
                                    {{ $setup->currency }} <span id="total-display">{{ number_format($total) }}</span>
                                @else
                                    <span id="total-display">{{ number_format($total) }}</span> {{ $setup->currency }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- 4. Confirm Button -->
                    <button type="submit"
                        class="w-full primary-bg hover:bg-[#e65f00] text-primary font-bold py-4 text-md rounded-lg mt-8 shadow-lg shadow-orange-100 transition-all active:scale-[0.98]">
                        Confirm Order
                    </button>
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
@endsection


@push('scripts')
<script>
        let _activeDraftOrderId = @json($draftOrderId ?? null);
        let _checkoutTotal = {{ $total }};
        let _checkoutSlug = '';


    // ── Page load: auto-select first method ──
        document.addEventListener('DOMContentLoaded', function() {
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

        // Modal open করো
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
    document.addEventListener('DOMContentLoaded', function() {
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
                    // Update DOM with formatted numbers so the view matches PHP's number_format()
                    document.getElementById('shipping-display').innerText = Number(data.shipping_cost).toLocaleString();
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
            reader.onload = function(e) {
                grid.innerHTML = `<div class="relative w-16 h-16 border rounded overflow-hidden">
                <img src="${e.target.result}" alt="Selected file preview" class="w-full h-full object-cover" loading="lazy" width="800" height="800">
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
        function(e) {
            const btn = this.querySelector('button[type="submit"]');

            const name = this.querySelector('input[name="name"]').value;
            const phone = this.querySelector('input[name="phone"]').value;
            if (!name || !phone) return;

            setTimeout(() => {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            }, 50);
        });
</script>
<script>
    function updateCheckoutQty(rowId, change, btnEl) {
        const qtySpan = document.getElementById('qty-' + rowId);
        if(!qtySpan) return;
        
        const currentQty = parseInt(qtySpan.innerText);
        const newQty = currentQty + change;
        
        if (newQty < 1) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if(btnEl) btnEl.disabled = true;

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
                if(data.status === 'success') {
                    qtySpan.innerText = newQty;
                    
                    // Update totals
                    const subtotalDisplay = document.getElementById('subtotal-display');
                    const totalDisplay = document.getElementById('total-display');
                    const shippingDisplay = document.getElementById('shipping-display');
                    const discountDisplay = document.getElementById('discount-display');
                    
                    if(subtotalDisplay) subtotalDisplay.innerText = data.subtotal;
                    if(totalDisplay) totalDisplay.innerText = data.total;
                    if(shippingDisplay) shippingDisplay.innerText = data.shipping;
                    if(discountDisplay) discountDisplay.innerText = data.discount;
                    
                    // Trigger draft save if needed to sync immediately
                    if(typeof saveDraft === 'function') {
                        const phoneInput = document.querySelector('input[name="phone"]');
                        if (phoneInput && phoneInput.value && phoneInput.value.length >= 11) {
                            saveDraft();
                        }
                    }
                }
            })
            .catch(err => {
                console.error(err);
                toastr.error("Quantity update failed. Please try again.");
            })
            .finally(() => {
                if(btnEl) btnEl.disabled = false;
            });
    }
</script>
@endpush
@push('scripts')
@include('components.meta-info.pixel-events', ['event' => 'InitiateCheckout', 'data' => ['total' => $total]])
@endpush
