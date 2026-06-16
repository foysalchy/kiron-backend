@props(['product', 'company'])

@php
    // 1. Wishlist logic
    $isWishlisted = false;
    if (auth('customer')->check()) {
        $isWishlisted = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
            ->where('product_id', $product->id)
            ->exists();
    }

    // 2. Price and Percentage Discount logic
    $priceData = $product->display_price_data;
    $salePrice = $priceData->sale_price;
    $regularPrice = $priceData->regular_price;
    $isVar = $priceData->is_variation;

    $discountLabel = '';
    if ($regularPrice > $salePrice && $regularPrice > 0) {
        $diff = $regularPrice - $salePrice;
        $percentage = round(($diff / $regularPrice) * 100);
        if ($percentage > 0) {
            $discountLabel = $percentage . '%';
        }
    }

    // 3. Dynamic Rating logic
    $avgRating = $product->reviews_avg_rating ?? 0;
    $totalReviews = $product->reviews_count ?? 0;

    // 4. Stock check
    $isOutOfStock = $product->available_stock <= 0;
@endphp

<div
    class="group relative flex flex-col p-3 bg-white border border-gray-200 rounded-2xl hover:shadow-xl transition-all duration-300 h-full">

    <!-- Image Section -->
    <div class="relative w-full aspect-square overflow-hidden rounded-xl mb-3 shrink-0 bg-gray-50">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block w-full h-full">
            <img src="{{ $product->thumbnail_url ?? asset('./images/template1/frontend/cover.webp') }}" height="350"
                width="300"
                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                alt="{{ $product->title }}">
        </a>

        <!-- Wishlist Button -->
        <button type="button" onclick="toggleWishlist({{ $product->id }})" aria-label="Add to Wishlist"
            class="absolute top-2 left-2 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-sm z-20 cursor-pointer transition-all {{ $isWishlisted ? 'opacity-100' : 'opacity-0 group-hover:opacity-100' }}">
            <svg id="wish-icon-{{ $product->id }}" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                viewBox="0 0 24 24" fill="{{ $isWishlisted ? '#ef4444' : 'none' }}"
                stroke="{{ $isWishlisted ? '#ef4444' : 'currentColor' }}" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="transition-colors duration-300">
                <path
                    d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z">
                </path>
            </svg>
        </button>

        @if ($discountLabel)
            <div
                class="absolute top-1 right-1 secondary-bg text-secondary w-12 h-12 rounded-full flex flex-col items-center justify-center shadow-md transform rotate-12 group-hover:rotate-0 transition-transform duration-300 z-10">
                <span class="text-xs font-bold leading-none">{{ $discountLabel }}</span>
                <span class="text-[10px] font-medium leading-none mt-0.5 uppercase">Off</span>
            </div>
        @endif
    </div>

    <!-- Product Info Area -->
    <div class="flex flex-col flex-grow px-1">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block group/title">
            <h3
                class="hind-siliguri-medium md:text-[17px] text-[15px]  text-gray-800 line-clamp-2 mb-2 min-h-[40px] group-hover/title:text-[#BD4F00] transition-colors">
                {{ $product->title }}
            </h3>
        </a>
        @if ($company->is_review == 1)
            <!-- Dynamic Star Rating -->
            <div class="flex items-center gap-1 mb-2">
                <div class="flex text-yellow-500 text-xs">
                    @for ($i = 1; $i <= 5; $i++)
                        <i
                            class="{{ $i <= round($avgRating) ? 'fas' : 'far' }} fa-star {{ $i <= round($avgRating) ? '' : 'text-gray-200' }}"></i>
                    @endfor
                </div>
                <span class="text-[10px] text-gray-400 font-medium">({{ $totalReviews }})</span>
            </div>
        @endif
        <!-- Price Section -->
        <div class="flex items-center gap-2 mb-3 ">
            @if ($regularPrice > $salePrice)
                <span class="text-gray-500 text-[18px] line-through hind-siliguri-bold">
                    {{ $setup->currency }} {{ number_format($regularPrice) }}
                </span>
            @endif
            <span class="text-primary text-[18px]  hind-siliguri-bold">
                {{ $setup->currency }} {{ number_format($salePrice) }}{{ $isVar ? '+' : '' }}
            </span>
        </div>
    </div>

    <div class="flex items-center gap-2 mt-auto">
        <!-- Order Now Button -->
        <button {{ $isOutOfStock ? 'disabled' : '' }}
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id, true)" : "openVariationModal($product->id, true)" }}"
            class="flex-grow py-2.5 rounded-xl font-medium text-sm transition-all cursor-pointer
    {{ $isOutOfStock ? 'bg-[#df7070] text-white opacity-80 cursor-not-allowed' : 'primary-bg text-primary primary-bg-hover' }}">

            @if ($isOutOfStock)
                Stock Out
            @else
                Order Now
            @endif
        </button>

        <!-- Cart Icon Button -->
        <button {{ $isOutOfStock ? 'disabled' : '' }} aria-label="Add to Cart"
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id, false)" : "openVariationModal($product->id, false)" }}"
            class="primary-bg text-primary p-2.5 rounded-xl transition-all cursor-pointer flex items-center justify-center
            {{ $isOutOfStock ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#BD4F00]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
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
            // Global state to track if Order Now was clicked
            let isOrderNowGlobal = false;

            // --- Variation Modal Functions ---
            function updateModalTotal() {
                const selectedVariant = document.querySelector('input[name="selected_variant"]:checked');
                const qtyInput = document.getElementById('modal-qty');
                const totalDisplay = document.getElementById('modal-total-price-display');
                const unitPriceDisplay = document.getElementById('modal-unit-price');

                if (selectedVariant && qtyInput && totalDisplay) {
                    const unitPrice = parseFloat(selectedVariant.getAttribute('data-price'));
                    const qty = parseInt(qtyInput.value);
                    const currency = "{{ $setup->currency }}";
                    const total = unitPrice * qty;

                    if (unitPriceDisplay) unitPriceDisplay.innerText = currency + " " + unitPrice.toLocaleString();
                    totalDisplay.innerText = currency + " " + total.toLocaleString();
                }
            }

            function changeQty(val) {
                let qtyInput = document.getElementById('modal-qty');
                if (qtyInput) {
                    let newVal = parseInt(qtyInput.value) + val;
                    if (newVal >= 1) {
                        qtyInput.value = newVal;
                        updateModalTotal();
                    }
                }
            }

            function openVariationModal(id, isOrderNow = false) {
                isOrderNowGlobal = isOrderNow;

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

                        const btnText = document.getElementById('modal-btn-text');
                        const btnIcon = document.getElementById('modal-btn-icon');

                        if (isOrderNowGlobal) {
                            if (btnText) btnText.innerText = "Order Now";
                            if (btnIcon) btnIcon.className = "fas fa-bolt text-sm";
                        } else {
                            if (btnText) btnText.innerText = "Add to Cart";
                            if (btnIcon) btnIcon.className = "fas fa-shopping-cart text-sm";
                        }

                        updateModalTotal();
                    });
            }

            function closeModal() {
                const modal = document.getElementById('variation-modal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }

            function processAddVariation() {
                const selectedVariant = document.querySelector('input[name="selected_variant"]:checked');
                const qtyInput = document.getElementById('modal-qty');
                const token = document.querySelector('meta[name="csrf-token"]').content;

                if (!selectedVariant) {
                    toastr.warning("Please select an option.");
                    return;
                }

                fetch("{{ route('cart.add') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            variation_id: selectedVariant.value,
                            qty: qtyInput ? qtyInput.value : 1
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                            closeModal();

                            if (isOrderNowGlobal) {
                                window.location.href = "{{ route('checkout.index') }}";
                            } else {
                                toastr.success(data.message);
                            }
                        } else {
                            toastr.error(data.message || "Something went wrong.");
                        }
                    }).catch(err => toastr.error("Server error."));
            }

            // --- Single Product Function ---
            function addSingleToCart(id, isOrderNow = false) {
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
                            document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                            if (isOrderNow) {
                                window.location.href = "{{ route('checkout.index') }}";
                            } else {
                                toastr.success(data.message);
                            }
                        } else {
                            toastr.error(data.message);
                        }
                    });
            }
        </script>
    @endpush
@endonce
