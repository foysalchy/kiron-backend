@props([
    'product',
    'setup',
    'isUpcoming' => false,
])

@php
    $companyId = getCurrentCompany()?->company_id;
    $paymentMethods = \Illuminate\Support\Facades\Cache::remember("payment_methods_{$companyId}", 86400, function () use ($companyId) {
        return \App\Models\CustomerPaymentMethod::where('company_id', $companyId)
            ->where('status', \App\Enums\Status::Active->value)
            ->select(['id', 'company_id', 'name', 'icon', 'account_number', 'method_details', 'status'])
            ->get();
    });
    $draftOrderId = Session::get('current_draft_order_id');
    $isProductOutOfStock = ($product->stock_status === 'out_of_stock') || ($product->manage_stock && $product->available_stock <= 0);

    $codMethod = $paymentMethods->first(function($m) {
        $slug = strtolower(trim($m->name));
        return str_contains($slug, 'cash') || str_contains($slug, 'cod') || str_contains($slug, 'delivery');
    });
    $defaultPaymentMethod = $codMethod ? $codMethod->name : 'Cash on delivery';
@endphp

<!-- Quick Order Popup Modal Component -->
<style>
    /* Scoped height & spacing optimization for Quick Order Modal */
    #quick-order-modal #quick-order-summary-container > div {
        padding: 0.75rem !important;
        border-radius: 0.75rem !important;
        background-color: #f9fafb !important;
    }
    #quick-order-modal #quick-order-summary-container .mb-8 {
        margin-bottom: 0.5rem !important;
    }
    #quick-order-modal #quick-order-summary-container .mb-5 {
        margin-bottom: 0.4rem !important;
        padding: 0.4rem 0.6rem !important;
        font-size: 0.75rem !important;
    }
    #quick-order-modal #quick-order-summary-container h2 {
        font-size: 0.85rem !important;
        margin-bottom: 0.35rem !important;
        font-weight: 700 !important;
    }
    #quick-order-modal #quick-order-summary-container .space-y-2 > :not([hidden]) ~ :not([hidden]) {
        margin-top: 0.25rem !important;
    }
    #quick-order-modal #quick-order-summary-container label {
        font-size: 0.8rem !important;
    }
    /* Cart items list: compact and scrollable */
    #quick-order-modal #quick-order-summary-container .space-y-4.mb-8 {
        max-height: 135px !important;
        overflow-y: auto !important;
        margin-bottom: 0.5rem !important;
        padding-right: 0.25rem !important;
    }
    #quick-order-modal #quick-order-summary-container .space-y-4 > :not([hidden]) ~ :not([hidden]) {
        margin-top: 0.35rem !important;
    }
    #quick-order-modal #quick-order-summary-container .bg-white.rounded-xl.p-4 {
        padding: 0.4rem 0.5rem !important;
        border-radius: 0.5rem !important;
        gap: 0.5rem !important;
        border: 1px solid #f3f4f6 !important;
    }
    #quick-order-modal #quick-order-summary-container .w-16.h-16 {
        width: 2.5rem !important;
        height: 2.5rem !important;
        border-radius: 0.375rem !important;
    }
    #quick-order-modal #quick-order-summary-container h3.text-sm {
        font-size: 0.75rem !important;
        line-height: 1rem !important;
        margin-bottom: 0.15rem !important;
    }
    #quick-order-modal #quick-order-summary-container p.text-sm {
        font-size: 0.75rem !important;
    }
    /* Cost breakdown: compact rows */
    #quick-order-modal #quick-order-summary-container .border-t.border-gray-100.pt-6 {
        padding-top: 0.4rem !important;
        margin-top: 0.4rem !important;
    }
    #quick-order-modal #quick-order-summary-container .space-y-4.border-t {
        gap: 0.25rem !important;
    }
    #quick-order-modal #quick-order-summary-container .space-y-4.border-t > div {
        font-size: 0.8rem !important;
    }
    #quick-order-modal #quick-order-summary-container .border-t.border-gray-100.pt-4 {
        padding-top: 0.35rem !important;
        margin-top: 0.35rem !important;
    }
    #quick-order-modal #quick-order-summary-container .text-lg.font-black {
        font-size: 0.875rem !important;
    }
    #quick-order-modal #quick-order-summary-container .text-xl.font-bold {
        font-size: 1rem !important;
    }
    /* Confirm order button: compact */
    #quick-order-modal #quick-order-summary-container button[type="submit"] {
        margin-top: 0.5rem !important;
        padding-top: 0.55rem !important;
        padding-bottom: 0.55rem !important;
        font-size: 0.875rem !important;
        border-radius: 0.5rem !important;
    }
</style>

<div id="quick-order-modal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-2 sm:p-4">
    <div class="relative bg-white w-full max-w-3xl rounded-xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col my-auto" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 bg-gray-50/80">
            <div class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">
                    <i class="fas fa-bolt"></i>
                </span>
                <h3 class="text-sm sm:text-base font-bold text-gray-800">
                    {{ isset($setup->lang) && $setup->lang == 'bn' ? 'দ্রুত অর্ডার কনফার্ম করুন' : 'Quick Order Checkout' }}
                </h3>
            </div>
            <button type="button" onclick="closeQuickOrderModal()" aria-label="Close"
                class="w-7 h-7 rounded-full bg-white border border-gray-200 hover:bg-gray-100 text-gray-500 hover:text-gray-800 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Modal Body (Form) -->
        <div class="overflow-y-auto p-3 sm:p-4 flex-1">
            <form id="quick-order-form" action="{{ route('order.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                    <!-- LEFT COLUMN: Customer & Payment Info -->
                    <div class="lg:col-span-7 space-y-3">
                        <!-- 1. Customer Information Card -->
                        <div class="bg-gray-50/60 rounded-xl p-3 sm:p-3.5 border border-gray-200 space-y-2.5">
                            <h4 class="text-xs sm:text-sm font-bold text-gray-800 flex items-center gap-1.5">
                                <i class="fas fa-user-edit text-[var(--primary-color)]"></i>
                                {{ isset($setup->lang) && $setup->lang == 'bn' ? 'আপনার তথ্য দিন' : 'Customer Information' }}
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <input type="text" name="name" placeholder="{{ isset($setup->lang) && $setup->lang == 'bn' ? 'আপনার নাম *' : 'Your Name *' }}" aria-label="Your Name" required
                                        value="{{ old('name', auth('customer')->user()->name ?? '') }}"
                                        class="w-full px-3.5 py-2 rounded-lg border border-gray-200 bg-white outline-none focus:border-[#016738] focus:ring-1 focus:ring-green-50 text-sm font-medium transition-all h-10">
                                </div>
                                <div>
                                    <input type="tel" name="phone" placeholder="{{ isset($setup->lang) && $setup->lang == 'bn' ? 'মোবাইল নাম্বার *' : 'Phone Number *' }}" aria-label="Phone Number" required
                                        value="{{ old('phone', auth('customer')->user()->phone ?? '') }}"
                                        class="w-full px-3.5 py-2 rounded-lg border border-gray-200 bg-white outline-none focus:border-[#016738] focus:ring-1 focus:ring-green-50 text-sm font-medium transition-all h-10">
                                </div>
                            </div>
                            <input type="hidden" name="district" value="none" required />
                            <div>
                                <textarea name="address" placeholder="{{ isset($setup->lang) && $setup->lang == 'bn' ? 'পূর্ণাঙ্গ ঠিকানা (বাসা/রোড, এলাকা, জেলা) *' : 'Your Address (House/Road, Area, City) *' }}" aria-label="Your Address" rows="2" required
                                    class="w-full px-3.5 py-2 rounded-lg border border-gray-200 bg-white outline-none focus:border-[#016738] focus:ring-1 focus:ring-green-50 text-sm transition-all">{{ old('address', auth('customer')->user()->address ?? '') }}</textarea>
                            </div>
                        </div>

                        <!-- Hidden Payment Method (Default: Cash on Delivery) -->
                        <input type="hidden" name="payment_method" value="{{ $defaultPaymentMethod }}">

                        <!-- 2. Delivery Method Card (Flex in one row) -->
                        <div class="bg-gray-50/60 rounded-xl p-3 sm:p-3.5 border border-gray-200">
                            <h4 class="text-xs sm:text-sm font-bold text-gray-800 flex items-center gap-1.5 mb-2">
                                <i class="fas fa-truck text-[var(--primary-color)]"></i>
                                {{ isset($setup->lang) && $setup->lang == 'bn' ? 'ডেলিভারি এলাকা নির্বাচন করুন' : 'Select Delivery Method' }}
                            </h4>
                            <div class="flex gap-2">
                                <label class="flex-1 flex items-center gap-2 p-2 sm:p-2.5 border-2 border-gray-200 rounded-lg cursor-pointer bg-white hover:border-[var(--primary-color)] has-[:checked]:border-[var(--primary-color)] has-[:checked]:bg-orange-50/50 transition-all text-xs font-semibold text-gray-700">
                                    <input type="radio" name="delivery_area" value="inside" onchange="updateCheckoutShipping(this.value)" checked class="w-3.5 h-3.5 accent-[var(--primary-color)] shrink-0">
                                    <span class="truncate">Inside Dhaka ({{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}{{ number_format($setup->inside_charge, 2) }}{{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }})</span>
                                </label>
                                <label class="flex-1 flex items-center gap-2 p-2 sm:p-2.5 border-2 border-gray-200 rounded-lg cursor-pointer bg-white hover:border-[var(--primary-color)] has-[:checked]:border-[var(--primary-color)] has-[:checked]:bg-orange-50/50 transition-all text-xs font-semibold text-gray-700">
                                    <input type="radio" name="delivery_area" value="outside" onchange="updateCheckoutShipping(this.value)" class="w-3.5 h-3.5 accent-[var(--primary-color)] shrink-0">
                                    <span class="truncate">Outside Dhaka ({{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}{{ number_format($setup->outside_charge, 2) }}{{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }})</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Delivery & Summary -->
                    <div class="lg:col-span-5 space-y-3">
                        <div id="quick-order-summary-container">
                            <div class="p-6 text-center text-gray-400">
                                <i class="fas fa-spinner fa-spin text-xl text-[var(--primary-color)] mb-1.5"></i>
                                <p class="text-xs">Loading order details...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Payment Forms Repository --}}
<div id="forms-repository" class="hidden">
    <x-template1.payment-checkout :methods="$paymentMethods" :currency="$setup->currency" />
</div>

<script>
    let _activeDraftOrderId = @json($draftOrderId ?? null);
    let _checkoutTotal = 0;
    let _checkoutSlug = '';

    function handleQuickOrder() {
        if (@json($isUpcoming) || @json($isProductOutOfStock)) {
            return;
        }
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const qty = document.getElementById('main-qty').value;
        let items = [];

        let varIds = document.getElementById('selected-variation-id').value;
        if ('{{ $product->type }}' === 'variation') {
            let missingAttribute = attributeGroups.find(group => !activeFilters[group]);

            if (missingAttribute) {
                toastr.warning(`Please select ${missingAttribute}`);
                return;
            }
            varIds.split(',').forEach(id => items.push({
                variation_id: id,
                qty: qty
            }));
        } else {
            items.push({
                id: {{ $product->id }},
                qty: qty
            });
        }

        const quickBtns = [document.getElementById('btn-quick-order'), document.getElementById('btn-quick-order-mobile')];
        quickBtns.forEach(btn => {
            if (btn) {
                btn.disabled = true;
                btn.dataset.origHtml = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Loading...';
            }
        });

        fetch("{{ route('cart.add') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                items: items
            })
        })
        .then(res => res.json())
        .then(data => {
            quickBtns.forEach(btn => {
                if (btn) {
                    btn.disabled = false;
                    if (btn.dataset.origHtml) btn.innerHTML = btn.dataset.origHtml;
                }
            });

            if (data.status === 'success') {
                if (typeof fbq === 'function') {
                    fbq('track', 'AddToCart', {
                        content_ids: ['{{ $product->id }}'],
                        content_type: 'product',
                        value: {{ $product->sale_price ?? 0 }} * qty,
                        currency: '{{ $setup->currency ?? "BDT" }}'
                    });
                }
                if (typeof ttq === 'function') {
                    ttq.track('AddToCart', {
                        content_id: '{{ $product->id }}',
                        content_type: 'product',
                        value: {{ $product->sale_price ?? 0 }} * qty,
                        currency: '{{ $setup->currency ?? "BDT" }}'
                    });
                }

                document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                openQuickOrderModal();
            } else {
                toastr.error(data.message);
            }
        })
        .catch(err => {
            quickBtns.forEach(btn => {
                if (btn) {
                    btn.disabled = false;
                    if (btn.dataset.origHtml) btn.innerHTML = btn.dataset.origHtml;
                }
            });
            console.error(err);
            toastr.error('Something went wrong. Please try again.');
        });
    }

    function openQuickOrderModal() {
        const modal = document.getElementById('quick-order-modal');
        if (!modal) return;

        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        const summaryContainer = document.getElementById('quick-order-summary-container');
        if (summaryContainer) {
            summaryContainer.innerHTML = '<div class="p-8 text-center text-gray-500"><i class="fas fa-spinner fa-spin text-2xl text-[var(--primary-color)] mb-2"></i><p class="text-sm">Loading checkout details...</p></div>';
        }

        const firstRadio = modal.querySelector('input[name="payment_method"][type="radio"]');
        if (firstRadio) {
            firstRadio.checked = true;
            _checkoutSlug = firstRadio.value;
            if (typeof handlePaymentSelection === 'function') {
                handlePaymentSelection(firstRadio.value, firstRadio.value);
            }
        }

        refreshQuickOrderSummary();

        @auth('customer')
            saveQuickDraft();
        @endauth
    }

    function closeQuickOrderModal() {
        const modal = document.getElementById('quick-order-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function refreshQuickOrderSummary() {
        fetch("{{ route('checkout.index', request()->query()) }}", {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            const temp = document.createElement('div');
            temp.innerHTML = html;

            const newSummary = temp.querySelector('#checkout-summary-container');
            const summaryContainer = document.getElementById('quick-order-summary-container');

            if (newSummary && summaryContainer) {
                // Remove duplicate delivery block from right summary (as it's in the unified card on the left)
                const deliveryBlock = newSummary.querySelector('input[name="delivery_area"]')?.closest('.mb-8');
                if (deliveryBlock) {
                    deliveryBlock.remove();
                }

                summaryContainer.innerHTML = newSummary.innerHTML;

                const match = html.match(/let _checkoutTotal = (.*?);/);
                if (match && match[1]) {
                    _checkoutTotal = parseFloat(match[1]);
                }

                const checkedRadio = document.querySelector('#quick-order-modal input[name="payment_method"]:checked');
                if (checkedRadio && typeof handlePaymentSelection === 'function') {
                    handlePaymentSelection(checkedRadio.value, checkedRadio.value);
                }
            }
        })
        .catch(err => {
            console.error('Failed to load checkout summary:', err);
            const summaryContainer = document.getElementById('quick-order-summary-container');
            if (summaryContainer) {
                summaryContainer.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Failed to load order summary. Please refresh.</div>';
            }
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
                document.querySelectorAll('.cart-count-nav').forEach(el => {
                    el.innerText = data.cart_count;
                });
                if (data.cart_count === 0) {
                    closeQuickOrderModal();
                    if (typeof toastr !== 'undefined') toastr.info('Cart is now empty.');
                    return;
                }
            }
            refreshQuickOrderSummary();
            if (typeof refreshMiniCart === 'function') refreshMiniCart();
            if (typeof toastr !== 'undefined') toastr.success(data.message || 'Item removed');
        })
        .catch(err => {
            console.error(err);
            if (typeof toastr !== 'undefined') toastr.error('Failed to remove item.');
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
                const shipEl = document.getElementById('shipping-display');
                const totalEl = document.getElementById('total-display');
                if (shipEl) shipEl.innerText = Number(data.shipping_cost).toFixed(2);
                if (totalEl) totalEl.innerText = data.grand_total;
                if (typeof _checkoutTotal !== 'undefined') {
                    _checkoutTotal = parseFloat(String(data.grand_total).replace(/,/g, ''));
                }

                const checkedRadio = document.querySelector('#quick-order-modal input[name="payment_method"]:checked');
                if (checkedRadio && typeof handlePaymentSelection === 'function') {
                    handlePaymentSelection(checkedRadio.value, checkedRadio.value);
                }

                toastr.success(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    function applyCoupon() {
        const codeInput = document.getElementById('coupon-code-input');
        if (!codeInput) return;
        const code = codeInput.value;
        if (!code) return toastr.warning('Please enter a coupon code');

        const token = document.querySelector('meta[name="csrf-token"]').content;
        const btn = event?.target ? (event.target.tagName === 'BUTTON' ? event.target : event.target.closest('button')) : document.getElementById('apply-coupon-btn');
        const originalText = btn ? btn.innerHTML : 'Apply';
        if (btn) {
            btn.innerText = '...';
            btn.disabled = true;
        }

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
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
            if (data.success) {
                const discountDisplay = document.getElementById('discount-display');
                const totalDisplay = document.getElementById('total-display');
                const discountCodeDisplay = document.getElementById('discount-code-display');
                const discountRow = document.getElementById('discount-row');
                const applyBtn = document.getElementById('apply-coupon-btn');
                const removeBtn = document.getElementById('remove-coupon-btn');

                if (discountDisplay) discountDisplay.innerText = Number(data.discount_amount).toLocaleString();
                if (totalDisplay) totalDisplay.innerText = Number(data.grand_total).toLocaleString();
                if (discountCodeDisplay) discountCodeDisplay.innerText = '(' + data.coupon_code + ')';
                if (discountRow) discountRow.classList.remove('hidden');

                codeInput.setAttribute('readonly', 'readonly');
                if (applyBtn) applyBtn.classList.add('hidden');
                if (removeBtn) removeBtn.classList.remove('hidden');

                if (typeof _checkoutTotal !== 'undefined') {
                    _checkoutTotal = parseFloat(data.grand_total);
                }

                toastr.success(data.message);
            } else {
                toastr.error(data.message || "Invalid coupon");
            }
        })
        .catch(err => {
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
            console.error(err);
            toastr.error("Server error occurred. Please try again.");
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
                const totalDisplay = document.getElementById('total-display');
                const discountRow = document.getElementById('discount-row');
                const codeInput = document.getElementById('coupon-code-input');
                const applyBtn = document.getElementById('apply-coupon-btn');
                const removeBtn = document.getElementById('remove-coupon-btn');

                if (totalDisplay) totalDisplay.innerText = Number(data.grand_total).toLocaleString();
                if (discountRow) discountRow.classList.add('hidden');

                if (codeInput) {
                    codeInput.removeAttribute('readonly');
                    codeInput.value = '';
                }

                if (applyBtn) applyBtn.classList.remove('hidden');
                if (removeBtn) removeBtn.classList.add('hidden');

                if (typeof _checkoutTotal !== 'undefined') {
                    _checkoutTotal = parseFloat(data.grand_total);
                }

                toastr.success(data.message);
            }
        })
        .catch(err => console.error(err));
    }

    function saveQuickDraft() {
        const modal = document.getElementById('quick-order-modal');
        if (!modal) return;
        const phoneInput = modal.querySelector('input[name="phone"]');
        const nameInput = modal.querySelector('input[name="name"]');
        const addressInput = modal.querySelector('textarea[name="address"]');

        if (!phoneInput) return;
        let phone = phoneInput.value;
        if (phone && phone.length >= 11) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch("{{ route('order.partial') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    phone: phone,
                    name: nameInput ? nameInput.value : '',
                    address: addressInput ? addressInput.value : ''
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    _activeDraftOrderId = data.order_id;
                }
            })
            .catch(err => console.error(err));
        }
    }

    function previewImages(input, slug) {
        const grid = document.getElementById(slug + '-preview');
        const hidden = document.getElementById(slug + '-hidden-files');
        if (!grid || !hidden) return;
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

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('quick-order-modal');
        if (!modal) return;

        const phoneInput = modal.querySelector('input[name="phone"]');
        if (phoneInput) {
            phoneInput.addEventListener('blur', saveQuickDraft);
        }

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeQuickOrderModal();
            }
        });

        const quickForm = document.getElementById('quick-order-form');
        if (quickForm) {
            quickForm.addEventListener('submit', function (e) {
                const btn = this.querySelector('button[type="submit"]');
                const name = this.querySelector('input[name="name"]')?.value;
                const phone = this.querySelector('input[name="phone"]')?.value;
                const address = this.querySelector('textarea[name="address"]')?.value;

                if (!name || !phone || !address) return;

                setTimeout(() => {
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                    }
                }, 50);
            });
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeQuickOrderModal();
        }
    });
</script>

