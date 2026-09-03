@props(['removeMessage' => 'The item has been removed from the cart.'])

@php $cartItems = \Gloudemans\Shoppingcart\Facades\Cart::content(); @endphp

@forelse($cartItems as $item)
    <div class="flex gap-3 mb-4 pb-4 border-b border-gray-100 last:border-0">
        <div class="w-16 h-16 flex-shrink-0 bg-gray-50 rounded overflow-hidden">
            <img src="{{ $item->options->thumbnail ?? asset('images/no-image.png') }}" class="w-full h-full object-contain">
        </div>
        <div class="flex-1">
            <h3 class="text-xs font-bold text-gray-800 leading-tight">{{ $item->name }}</h3>
            <p class="text-[10px] text-gray-500 mt-0.5">{{ $item->options->variant ?? '' }}</p>
            <div class="flex justify-between items-center mt-1">
                <span class="text-xs font-bold text-[var(--primary-color)]">{{ $item->qty }} ×
    {{ ($setup->currency_position ?? 'left') == 'left' ? $setup->currency : '' }}{{ number_format($item->price, 0) }}{{ ($setup->currency_position ?? 'left') == 'right' ? $setup->currency : '' }}</span>
                <button onclick="removeCartItem('{{ $item->rowId }}', '{{ $removeMessage }}')" class="text-gray-400 hover:text-red-500"><i class="fa-regular fa-circle-xmark"></i></button>
            </div>
        </div>
    </div>
@empty
    <div class="py-10 text-center text-gray-400 text-sm">Your cart is empty</div>
@endforelse

<div id="new-cart-subtotal" class="hidden">{{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal() }}</div>
<div id="new-cart-count" class="hidden">{{ \Gloudemans\Shoppingcart\Facades\Cart::count() }}</div>

<script>
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

        const newSubtotal = document.getElementById('new-cart-subtotal')?.innerText;
        const subtotalValEl = document.getElementById('mini-cart-subtotal-val');

        if (newSubtotal && subtotalValEl) {
            const pos = "{{ $setup->currency_position ?? 'left' }}";
            const currency = "{{ $setup->currency }}";
            subtotalValEl.innerText = (pos === 'left') ? (currency + newSubtotal) : (newSubtotal + currency);
        }

        const newCount = parseInt(document.getElementById('new-cart-count')?.innerText) || 0;
        const footerWrapper = document.getElementById('mini-cart-footer');
        if (footerWrapper) {
            footerWrapper.style.display = newCount > 0 ? '' : 'none';
        }

                }).catch(err => console.error('Cart Refresh Error:', err));
        }

    function removeCartItem(rowId, customMessage) {
    fetch(`/cart/remove/${rowId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        refreshMiniCart();

        document.querySelectorAll('.cart-count-nav').forEach(el => {
            let currentCount = parseInt(el.innerText);
            if (currentCount > 0) {
                el.innerText = currentCount - 1;
            }
        });

        toastr.success(customMessage || 'The item has been removed from the cart.');
    })
    .catch(err => console.error('Error removing item:', err));
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