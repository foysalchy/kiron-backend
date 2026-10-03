@props(['removeMessage' => 'The item has been removed from the cart.'])

@php $cartItems = \Gloudemans\Shoppingcart\Facades\Cart::content(); @endphp

@forelse($cartItems as $item)
    <div class="flex gap-3 mb-4 pb-4 border-b border-gray-100 last:border-0" data-row-id="{{ $item->rowId }}">
        <div class="w-16 h-16 flex-shrink-0 bg-gray-50 rounded overflow-hidden">
            <img src="{{ $item->options->thumbnail ?? asset('images/no-image.png') }}" class="w-full h-full object-contain">
        </div>
        <div class="flex-1">
            <h3 class="text-[13px] font-bold text-gray-800 leading-tight">{{ $item->name }}</h3>
            <p class="text-[13px] text-gray-500 mt-0.5">{{ $item->options->variant ?? '' }}</p>

            <div class="flex justify-between items-center mt-2">
                <span class="text-[13px] font-bold text-[var(--primary-color)]" data-unit-price="{{ $item->price }}">
                    {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}{{ number_format($item->price, 0) }}{{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                </span>
                  <div class="flex items-center justify-between mt-2">
                <div class="flex items-center border border-gray-200 rounded-full h-7 w-20">
                    <button type="button" onclick="changeItemQty(this, '{{ $item->rowId }}', -1)"
                        class="w-6 flex items-center justify-center text-gray-500 hover:text-[var(--primary-color)] text-xs cursor-pointer">-</button>
                    <span class="flex-1 text-center text-[11px] font-bold" data-qty-display>{{ $item->qty }}</span>
                    <button type="button" onclick="changeItemQty(this, '{{ $item->rowId }}', 1)"
                        class="w-6 flex items-center justify-center text-gray-500 hover:text-[var(--primary-color)] text-xs cursor-pointer">+</button>
                </div>
                <span class="text-[13px] font-bold text-gray-700"
                      data-item-subtotal
                      data-item-subtotal-raw="{{ $item->subtotal }}">
                    {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}{{ number_format($item->subtotal, 0) }}{{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}
                </span>
            </div>
                <button onclick="removeCartItem('{{ $item->rowId }}', '{{ $removeMessage }}')" class="text-gray-400 hover:text-red-500"><i class="fa-regular fa-circle-xmark"></i></button>
            </div>

          
        </div>
    </div>
@empty
    <div class="py-10 text-center text-gray-400 text-sm">Your cart is empty</div>
@endforelse

<div id="new-cart-count" class="hidden">{{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}</div>

<script>
    const currencySymbol = "{{ $setup->currency }}";
    const currencyPos = "{{ $setup->currency_position ?? 'left' }}";

    function formatMoney(amount) {
        // Math.round + manual comma formatting, locale নির্ভরতা ছাড়াই (toLocaleString() locale অনুযায়ী space/comma ভিন্ন হতে পারে)
        const rounded = Math.round(amount);
        const withCommas = rounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return currencyPos === 'left' ? currencySymbol + withCommas : withCommas + currencySymbol;
    }

    function refreshMiniCart() {
        fetch("{{ route('cart.drawer.items') }}")
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.text();
            })
            .then(html => {
                const listContainer = document.getElementById('mini-cart-list');
                if (listContainer) {
                    listContainer.innerHTML = html;
                }

                recalculateOverallSubtotal();

                const newCount = parseInt(document.getElementById('new-cart-count')?.innerText) || 0;
                const footerWrapper = document.getElementById('mini-cart-footer');
                if (footerWrapper) {
                    footerWrapper.style.display = newCount > 0 ? '' : 'none';
                }

            }).catch(err => console.error('Cart Refresh Error:', err));
    }

    function recalculateOverallSubtotal() {
        const subtotalValEl = document.getElementById('mini-cart-subtotal-val');
        const floatingSubtotalEl = document.getElementById('floating-cart-subtotal');
        
        let total = 0;
        document.querySelectorAll('[data-item-subtotal-raw]').forEach(el => {
            total += parseFloat(el.getAttribute('data-item-subtotal-raw')) || 0;
        });

        if (subtotalValEl) subtotalValEl.innerText = formatMoney(total);
        if (floatingSubtotalEl) floatingSubtotalEl.innerText = formatMoney(total);
    }

    // ============ OPTIMISTIC UI UPDATE ============
    function changeItemQty(btnEl, rowId, delta) {
        const card = btnEl.closest('[data-row-id]');
        const qtyDisplay = card.querySelector('[data-qty-display]');
        const subtotalEl = card.querySelector('[data-item-subtotal]');
        const unitPrice = parseFloat(card.querySelector('[data-unit-price]').getAttribute('data-unit-price')) || 0;

        let currentQty = parseInt(qtyDisplay.innerText) || 1;
        let newQty = currentQty + delta;

        if (newQty < 1) return;

        const newItemSubtotal = unitPrice * newQty;

        // ১. Item-level UI সাথে সাথেই আপডেট
        qtyDisplay.innerText = newQty;
        subtotalEl.innerText = formatMoney(newItemSubtotal);
        subtotalEl.setAttribute('data-item-subtotal-raw', newItemSubtotal);

        // ২. পুরো cart-এর সব item-subtotal যোগ করে overall subtotal recalculate
        recalculateOverallSubtotal();

        // ৩. Background server request
        const token = document.querySelector('meta[name="csrf-token"]').content;

        fetch("{{ route('cart.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    rowId: rowId,
                    qty: newQty
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') {
                    toastr.error('Failed to update quantity. Reverting.');
                    refreshMiniCart();
                } else {
                    document.querySelectorAll('.cart-count-nav').forEach(el => {
                        el.innerText = data.cart_count;
                    });
                    // Server confirm করার পরও recalculate করে নেওয়া (guaranteed accuracy)
                    recalculateOverallSubtotal();
                }
            })
            .catch(err => {
                console.error('Error updating qty:', err);
                toastr.error('Something went wrong. Reverting changes.');
                refreshMiniCart();
            });
    }

function removeCartItem(rowId, customMessage) {
    const card = document.querySelector(`[data-row-id="${rowId}"]`);
    if (card) card.remove();

    recalculateOverallSubtotal();

    fetch(`/cart/remove/${rowId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            refreshMiniCart();

            // ============ পরিবর্তন: ম্যানুয়াল decrement বাদ দিয়ে server থেকে আসল cart_count ব্যবহার ============
            if (data && data.cart_count !== undefined) {
                document.querySelectorAll('.cart-count-nav').forEach(el => {
                    el.innerText = data.cart_count;
                });
            }

            toastr.success((data && data.message) || customMessage || 'The item has been removed from the cart.');
        })
        .catch(err => {
            console.error('Error removing item:', err);
            toastr.error('Failed to remove item.');
            refreshMiniCart();
        });
}

    function toggleCartDrawer() {
        const drawer = document.getElementById('cart-drawer');
        const overlay = document.getElementById('cart-overlay');

        if (drawer.classList.contains('translate-x-full')) {
            refreshMiniCart();
            drawer.classList.remove('translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            drawer.classList.add('translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
</script>