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
                <span class="text-xs font-bold text-[var(--primary-color)]">{{ $item->qty }} × {{ number_format($item->price, 0) }}৳</span>
                <button onclick="removeCartItem('{{ $item->rowId }}')" class="text-gray-400 hover:text-red-500"><i class="fa-regular fa-circle-xmark"></i></button>
            </div>
        </div>
    </div>
@empty
    <div class="py-10 text-center text-gray-400 text-sm">আপনার কার্ট খালি</div>
@endforelse

{{-- সাবটোটাল ডাটা (লুকানো থাকবে, শুধু JS এর জন্য) --}}
<div id="new-cart-subtotal" class="hidden">{{ \Gloudemans\Shoppingcart\Facades\Cart::subtotal() }}</div>
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

            // নতুন সাবটোটাল আপডেট (কম্পোনেন্টের ভেতর থেকে ডাটা নিচ্ছে)
            const newSubtotal = document.getElementById('new-cart-subtotal')?.innerText;
            const subtotalValEl = document.getElementById('mini-cart-subtotal-val');
            if (newSubtotal && subtotalValEl) {
                subtotalValEl.innerText = newSubtotal + '৳';
            }
        }).catch(err => console.error('Cart Refresh Error:', err));
}

    function removeCartItem(rowId) {
    // সাবডোমেইন এবং রাউট অনুযায়ী সঠিক পাথ (/cart/remove/ID)
    fetch(`/cart/remove/${rowId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        // রিমুভ সফল হলে ড্রয়ার রিফ্রেশ করবে
        refreshMiniCart();

        // কার্ট কাউন্টার ডাইনামিকালি আপডেট
        document.querySelectorAll('.cart-count-nav').forEach(el => {
            let currentCount = parseInt(el.innerText);
            if (currentCount > 0) {
                el.innerText = currentCount - 1;
            }
        });

        toastr.success('পণ্যটি কার্ট থেকে সরানো হয়েছে');
    })
    .catch(err => console.error('Error removing item:', err));
}

    function toggleCartDrawer() {
        const drawer = document.getElementById('cart-drawer');
        const overlay = document.getElementById('cart-overlay');

        if (drawer.classList.contains('translate-x-full')) {
            refreshMiniCart(); // ড্রয়ার খোলার সময় আপডেট ডাটা আনবে
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

