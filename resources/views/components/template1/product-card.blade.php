@props(['product'])

@php
    $isWishlisted = false;
    if (auth('customer')->check()) {
        $isWishlisted = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
            ->where('product_id', $product->id)
            ->exists();
    }
@endphp

{{-- --}}
<div class="group relative flex flex-col p-4 bg-white border border-gray-200 rounded-lg hover:shadow-xl transition-all duration-300 h-full">

    <!-- Image Section -->
    <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3 shrink-0">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block w-full h-full">
            <img src="{{ $product->thumbnail_url }}"
                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500">
        </a>

        @if (request()->routeIs('user.dashboard'))
            <button type="button" onclick="toggleWishlist({{ $product->id }})"
                class="absolute top-2 right-2 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-sm z-20 cursor-pointer">
                <svg id="wish-icon-{{ $product->id }}" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                    viewBox="0 0 24 24" fill="#ef4444" stroke="#ef4444" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                </svg>
            </button>
        @endif

        @if ($product->discount > 0)
            <div class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">
                -{{ $product->discount_type == 'percent' ? (int) $product->discount . '%' : '৳' . (int) $product->discount }}
            </div>
        @endif
    </div>

    <!-- Product Info Area: flex-grow  -->
    <div class="flex flex-col flex-grow">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block group/title">
            <h3 class="text-lg font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[40px] group-hover/title:text-[#FF6A00] transition-colors">
                {{ $product->title }}
            </h3>
        </a>

        <div class="flex items-center gap-1 mb-2">
            <div class="flex text-yellow-400 text-xs">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star text-gray-200"></i>
            </div>
            <span class="text-xs text-gray-400 font-bold">(25)</span>
        </div>
    </div>

    <!-- Price & Button Section -->
    <div class="mt-auto pt-3  flex items-center justify-between gap-1">
        <div class="flex flex-col min-w-0">
            @if ($product->display_price_data->sale_price > 0)
                <span class="text-md font-bold text-[#FF6A00] truncate">
                    ৳{{ number_format($product->display_price_data->sale_price) }}{{ $product->display_price_data->is_variation ? '+' : '' }}
                </span>
                @if ($product->display_price_data->regular_price > $product->display_price_data->sale_price)
                    <span class="text-xs text-gray-400 line-through">
                        ৳{{ number_format($product->display_price_data->regular_price) }}
                    </span>
                @endif
            @endif
        </div>

        <button
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
            class="bg-[#1D2128] text-white px-2.5 py-1.5 rounded-lg text-sm font-semibold hover:bg-[#FF6A00] transition-all shrink-0 cursor-pointer whitespace-nowrap">
            Add to Cart
        </button>
    </div>
</div>

@once
    @push('scripts')
    <script>
        function addSingleToCart(id) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            fetch("{{ route('cart.add') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ id: id, qty: 1 })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                        toastr.success(data.message);
                    }
                });
        }

        function openVariationModal(id) {
            const modal = document.getElementById('variation-modal');
            const contentArea = document.getElementById('modal-content-area');
            if(!modal) return;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            contentArea.innerHTML = '<div class="py-10 text-center"><i class="fas fa-spinner fa-spin text-2xl text-[#FF6A00]"></i></div>';

            fetch("/product-variation/" + id)
                .then(res => res.text())
                .then(html => { contentArea.innerHTML = html; });
        }

        function closeModal() {
            const modal = document.getElementById('variation-modal');
            if(modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function processAddVariation() {
            const selectedVariant = document.querySelector('input[name="selected_variant"]:checked');
            const qtyInput = document.getElementById('modal-qty');
            const token = document.querySelector('meta[name="csrf-token"]').content;

            if (!selectedVariant) {
                toastr.warning("দয়া করে একটি অপশন সিলেক্ট করুন।");
                return;
            }

            fetch("{{ route('cart.add') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({
                        variation_id: selectedVariant.value,
                        qty: qtyInput ? qtyInput.value : 1
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                        closeModal(); // মোডাল বন্ধ হবে
                        toastr.success(data.message);
                    }
                });
        }

        function changeQty(val) {
            let qtyInput = document.getElementById('modal-qty');
            if (qtyInput) {
                let newVal = parseInt(qtyInput.value) + val;
                if (newVal >= 1) qtyInput.value = newVal;
            }
        }

        function toggleWishlist(productId) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            fetch("{{ route('wishlist.toggle') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ product_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                toastr.info(data.message);
                if (window.location.pathname.includes('wishlist')) location.reload();
            });
        }
    </script>
    @endpush
@endonce
