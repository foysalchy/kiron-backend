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
@endphp

<!-- Quick Order Popup Modal Component -->
<div id="quick-order-modal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-2 sm:p-4">
    <div class="relative bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col my-auto" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50/80">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-sm font-bold">
                    <i class="fas fa-bolt"></i>
                </span>
                <h3 class="text-lg font-bold text-gray-800">
                    {{ isset($setup->lang) && $setup->lang == 'bn' ? 'দ্রুত অর্ডার কনফার্ম করুন' : 'Quick Order Checkout' }}
                </h3>
            </div>
            <button type="button" onclick="closeQuickOrderModal()" aria-label="Close"
                class="w-8 h-8 rounded-full bg-white border border-gray-200 hover:bg-gray-100 text-gray-500 hover:text-gray-800 flex items-center justify-center transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Modal Body (Form) -->
        <div class="overflow-y-auto p-4 sm:p-6 flex-1">
            <form id="quick-order-form" action="{{ route('order.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- LEFT COLUMN: Customer & Payment Info -->
                    <div class="lg:col-span-7 space-y-5">
                        <!-- 1. Customer Information Card -->
                        <div class="bg-gray-50/60 rounded-xl p-4 sm:p-5 border border-gray-200 space-y-4">
                            <h4 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-user-edit text-[var(--primary-color)]"></i>
                                {{ isset($setup->lang) && $setup->lang == 'bn' ? 'আপনার তথ্য দিন' : 'Customer Information' }}
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-gray-700">
                                        {{ isset($setup->lang) && $setup->lang == 'bn' ? 'আপনার নাম' : 'Your Name' }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" placeholder="{{ isset($setup->lang) && $setup->lang == 'bn' ? 'পুরো নাম লিখুন' : 'Enter your full name' }}" required
                                        value="{{ old('name', auth('customer')->user()->name ?? '') }}"
                                        class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 bg-white outline-none focus:border-[#016738] focus:ring-2 focus:ring-green-50 text-sm transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-semibold text-gray-700">
                                        {{ isset($setup->lang) && $setup->lang == 'bn' ? 'মোবাইল নাম্বার' : 'Phone Number' }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="tel" name="phone" placeholder="{{ isset($setup->lang) && $setup->lang == 'bn' ? '১১ ডিজিটের মোবাইল নাম্বার' : 'Your mobile number' }}" required
                                        value="{{ old('phone', auth('customer')->user()->phone ?? '') }}"
                                        class="w-full px-3.5 py-2.5 rounded-lg border border-gray-200 bg-white outline-none focus:border-[#016738] focus:ring-2 focus:ring-green-50 text-sm transition-all">
                                </div>
                            </div>
                            <input type="hidden" name="district" value="none" required />
                            <div class="space-y-1.5">
                                <label class="text-xs font-semibold text-gray-700">
                                    {{ isset($setup->lang) && $setup->lang == 'bn' ? 'পূর্ণাঙ্গ ঠিকানা' : 'Your Address' }}
                                    <span class="text-red-500">*</span>
                                </label>
                                <textarea name="address" placeholder="{{ isset($setup->lang) && $setup->lang == 'bn' ? 'আপনার পূর্ণ ঠিকানা লিখুন (বাসা/রোড, এলাকা, থানা, জেলা)' : 'Your full address' }}" rows="2" required
                                    class="w-full px-3.5 py-2 rounded-lg border border-gray-200 bg-white outline-none focus:border-[#016738] focus:ring-2 focus:ring-green-50 text-sm transition-all">{{ old('address', auth('customer')->user()->address ?? '') }}</textarea>
                            </div>
                            @guest('customer')
                                <div class="pt-1">
                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input type="checkbox" name="create_account" class="w-4 h-4 accent-[var(--primary-color)] rounded">
                                        <span class="text-xs font-medium text-gray-600">
                                            {{ isset($setup->lang) && $setup->lang == 'bn' ? 'অ্যাকাউন্ট তৈরি করতে চান?' : 'Create an account?' }}
                                        </span>
                                    </label>
                                </div>
                            @endguest
                        </div>

                        <!-- 2. Payment Method Card -->
                        <div class="bg-gray-50/60 rounded-xl p-4 sm:p-5 border border-gray-200 space-y-3">
                            <h4 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-credit-card text-[var(--primary-color)]"></i>
                                {{ isset($setup->lang) && $setup->lang == 'bn' ? 'পেমেন্ট মেথড নির্বাচন করুন' : 'Select Payment Method' }}
                            </h4>

                            <div class="space-y-2">
                                @foreach ($paymentMethods as $method)
                                    @php $slug = strtolower(trim($method->name)); @endphp
                                    <div class="payment-option border-b border-gray-100 last:border-0 pb-2">
                                        <label
                                            class="flex items-center space-x-3 p-3 border-2 border-gray-200 rounded-lg cursor-pointer bg-white hover:border-[var(--primary-color)] has-[:checked]:border-[var(--primary-color)] has-[:checked]:bg-orange-50/50 transition-all">
                                            <input type="radio" name="payment_method" value="{{ $method->name }}"
                                                onchange="handlePaymentSelection('{{ $slug }}', '{{ $method->name }}')"
                                                class="w-4 h-4 accent-[var(--primary-color)]">
                                                <span class="text-sm font-semibold text-gray-700 capitalize">{{ $method->name }}</span>
                                        </label>

                                        <div id="checkout-anchor-{{ $slug }}" class="mt-3 hidden transition-all">
                                            <div class="bg-white p-3.5 rounded-lg border border-orange-200 shadow-xs">
                                                <div class="form-injection-point"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Delivery & Summary -->
                    <div class="lg:col-span-5 space-y-4">
                        <div id="quick-order-summary-container">
                            <div class="p-8 text-center text-gray-400">
                                <i class="fas fa-spinner fa-spin text-2xl text-[var(--primary-color)] mb-2"></i>
                                <p class="text-sm">Loading order details...</p>
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

        const firstRadio = modal.querySelector('input[name="payment_method"]');
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

