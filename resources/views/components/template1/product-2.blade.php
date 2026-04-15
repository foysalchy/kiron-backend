@props(['product'])

@php
    $isWishlisted = false;
    if (auth('customer')->check()) {
        $isWishlisted = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
            ->where('product_id', $product->id)
            ->exists();
    }
    // ডিসকাউন্ট ক্যালকুলেশন
    $sale_price = $product->display_price_data->sale_price;
    $regular_price = $product->display_price_data->regular_price;
    $discount_percentage = 0;
    if ($regular_price > $sale_price && $regular_price > 0) {
        $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
    }
@endphp

<div
    class="group relative flex flex-col p-3 bg-white border border-gray-200 rounded-2xl hover:shadow-lg transition-all duration-300 h-full">

    <!-- Image Section -->
    <div class="relative w-full aspect-square overflow-hidden rounded-xl mb-3 shrink-0">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block w-full h-full">
            <img src="{{ $product->thumbnail_url }}"
                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                alt="{{ $product->title }}">
        </a>

        <!-- Wishlist Button (Optional) -->
        <button type="button" onclick="toggleWishlist({{ $product->id }})"
            class="absolute top-2 left-2 w-7 h-7 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-sm z-20 cursor-pointer opacity-0 group-hover:opacity-100 transition-opacity">
            <i class="fa{{ $isWishlisted ? 's' : 'r' }} fa-heart text-red-500 text-xs"></i>
        </button>

        <!-- Circular Discount Badge (Like Image) -->
        @if ($discount_percentage > 0)
            <div
                class="absolute top-1 right-1 bg-[#F02D52] text-white w-14 h-14 rounded-full flex flex-col items-center justify-center shadow-md transform rotate-12 group-hover:rotate-0 transition-transform duration-300">
                <span class="text-xs font-bold leading-none">{{ $discount_percentage }}%</span>
                <span class="text-[10px] font-medium leading-none mt-1">Discount</span>
            </div>
        @endif
    </div>

    <!-- Product Info -->
    <div class="flex flex-col flex-grow px-1">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block">
            <h3
                class="text-[15px] font-semibold leading-tight text-gray-800 line-clamp-2 mb-2 min-h-[40px] hover:text-blue-600 transition-colors">
                {{ $product->title }}
            </h3>
        </a>

        <!-- Price Section -->
        <div class="flex items-center gap-2 mb-3">
            @if ($regular_price > $sale_price)
                <span class="text-blue-500 text-sm font-bold line-through">
                    {{ $setup->currency }} {{ number_format($regular_price) }}
                </span>
            @endif
            <span class="text-black text-lg font-extrabold">
                {{ $setup->currency }} {{ number_format($sale_price) }}
            </span>
        </div>
    </div>

    <!-- Action Buttons (Like Image) -->
    <div class="flex items-center gap-2 mt-auto">
        <!-- Order Now Button -->
        <button
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id, true)" : "openVariationModal($product->id)" }}"
            class="flex-grow bg-black text-white py-2.5 rounded-xl font-bold text-[15px] hover:bg-gray-800 transition-colors cursor-pointer">
            Oreder Now
        </button>

        <!-- Cart Icon Button -->
        <button
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id, false)" : "openVariationModal($product->id)" }}"
            class="bg-black text-white p-2.5 rounded-xl hover:bg-gray-800 transition-colors cursor-pointer flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </button>
    </div>
</div>

@once
    @push('scripts')
        <script>
            // এই ফাংশনে redirectToCheckout প্যারামিটার যোগ করা হয়েছে
            function addSingleToCart(id, redirectToCheckout = false) {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                fetch("{{ route('cart.add') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            id: id,
                            qty: 1
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // নেভিবার কার্ট আপডেট
                            document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);

                            if (redirectToCheckout) {
                                // যদি 'অর্ডার করুন' বাটনে ক্লিক হয়, তবে চেকআউট পেজে নিয়ে যাবে
                                window.location.href = "{{ route('checkout.index') }}";
                            } else {
                                // যদি শুধু কার্ট আইকনে ক্লিক হয়, তবে মেসেজ দেখাবে
                                toastr.success(data.message);
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        toastr.error('কিছু একটা ভুল হয়েছে।');
                    });
            }

            // মোডাল ওপেন ফাংশন আগের মতই আছে
            function openVariationModal(id) {
                const modal = document.getElementById('variation-modal');
                const contentArea = document.getElementById('modal-content-area');
                if (!modal) return;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                contentArea.innerHTML =
                    '<div class="py-10 text-center"><i class="fas fa-spinner fa-spin text-2xl text-[#FF6A00]"></i></div>';

                fetch("/product-variation/" + id)
                    .then(res => res.text())
                    .then(html => {
                        contentArea.innerHTML = html;
                    });
            }

            function toggleWishlist(productId) {
                const token = document.querySelector('meta[name="csrf-token"]').content;
                fetch("{{ route('wishlist.toggle') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            product_id: productId
                        })
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
