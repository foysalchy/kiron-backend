@props(['product', 'company'])

@php
    $isWishlisted = false;
    if (auth('customer')->check()) {
        $isWishlisted = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
            ->where('product_id', $product->id)
            ->exists();
    }

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
@endphp

<div
    class="group relative flex flex-col p-4 bg-white border border-gray-200 rounded-lg hover:shadow-xs transition-all duration-300 h-full">

    <!-- Image Section -->
    <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3 shrink-0">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block w-full h-full">
            <img src="{{ $product->thumbnail_url ?? asset('./images/template1/frontend/cover.webp') }}"
                alt="{{ $product->title }}" height="350" width="300"
                class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500">
        </a>

        <!-- Wishlist Button -->
        <button type="button" onclick="toggleWishlist({{ $product->id }})" aria-label="wishlist"
            class="absolute top-2 right-2 w-8 h-8 bg-white/90 hover:bg-white rounded-full flex items-center justify-center shadow-sm z-20 cursor-pointer transition-all {{ $isWishlisted ? 'opacity-100' : 'opacity-0 group-hover:opacity-100' }}">
            <svg id="wish-icon-{{ $product->id }}" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                viewBox="0 0 24 24" fill="{{ $isWishlisted ? '#ef4444' : 'none' }}"
                stroke="{{ $isWishlisted ? '#ef4444' : 'currentColor' }}" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="transition-colors duration-300">
                <path
                    d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z">
                </path>
            </svg>
        </button>

        <!-- Discount Badge -->
        @if ($discountLabel)
            <div
                class="absolute top-2 left-2 secondary-bg secondary-text text-xs font-bold px-2 py-0.5 rounded-full shadow-sm z-10">
                -{{ $discountLabel }}
            </div>
        @endif
    </div>

    <!-- Info Area -->
    <div class="flex flex-col flex-grow">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block group/title">
            <p
                style="font-size: 16px;font-weight: 500;"
                class="  font-light leading-[1.4] text-gray-800 line-clamp-2  min-h-[40px] group-hover/title:text-[#BD4F00] transition-colors">
                {{ $product->title }}
            </p>
        </a>

        @php
            $avgRating = $product->reviews_avg_rating ?? 0;
            $totalReviews = $product->reviews_count ?? 0;

        @endphp
        @if ($company->is_review == 1)
            <div class="flex items-center gap-1 mb-2">
                <div class="flex text-yellow-500 text-xs">
                    @for ($i = 1; $i <= 5; $i++)
                        <i
                            class="{{ $i <= round($avgRating) ? 'fas' : 'far' }} fa-star {{ $i <= round($avgRating) ? '' : 'text-gray-200' }}"></i>
                    @endfor
                </div>
                <span class="text-xs text-gray-600 font-bold">({{ $totalReviews }})</span>
            </div>
        @endif
    </div>

    <!-- Price & Button -->
    <div class="mt-auto pt-3 flex items-center justify-between gap-1">
        <div class="flex flex-col min-w-0">
            <span class="text-lg font-bold text-brand text-primary">
                {{ $setup->currency }} {{ number_format($salePrice) }}{{ $isVar ? '+' : '' }}
            </span>
            @if ($regularPrice > $salePrice)
                <span class="text-xs text-gray-600 line-through">
                    {{ $setup->currency }} {{ number_format($regularPrice) }}
                </span>
            @endif
        </div>
        @php
            $isOutOfStock = $product->available_stock <= 0;
        @endphp

        <button {{ $isOutOfStock ? 'disabled' : '' }} aria-label="{{ $isOutOfStock ? 'Stock Out' : 'Add to Cart' }}"
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
            class="flex-1 py-2 rounded-lg text-sm font-medium transition-all shrink-0 whitespace-nowrap
    {{ $isOutOfStock ? 'bg-[#df7070] text-white opacity-80 cursor-not-allowed' : 'w-[50px] max-w-[50px] primary-bg text-primary hover:bg-[#BD4F00] cursor-pointer' }}">

            @if ($isOutOfStock)
                <i class="fas fa-exclamation-circle mr-1"></i> Stock Out
            @else
                <i class="fas fa-shopping-cart mr-1 text-xs"></i>
            @endif
        </button>

    </div>
</div>
@once
    @push('scripts')
        <script>
            const token = document.querySelector('meta[name="csrf-token"]').content;

            // product variation modal related scripts
            function updateModalTotal() {
                const selectedVariant = document.querySelector('input[name="selected_variant"]:checked');
                const qtyInput = document.getElementById('modal-qty');
                const totalDisplay = document.getElementById('modal-total-price-display');
                const unitPriceDisplay = document.getElementById('modal-unit-price');

                if (selectedVariant && qtyInput && totalDisplay) {
                    const unitPrice = parseFloat(selectedVariant.getAttribute('data-price'));
                    const qty = parseInt(qtyInput.value);
                    const currency = "{{ $setup->currency }}";

                    // calculate total
                    const total = unitPrice * qty;

                    // update display
                    unitPriceDisplay.innerText = currency + " " + unitPrice.toLocaleString();
                    totalDisplay.innerText = currency + " " + total.toLocaleString();
                }
            }

            // quantity change function for variation modal
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

            // modal open function with loading state
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

            // ১. ভ্যারিয়েশন অ্যাড করার ফাংশন (Variation Modal এর জন্য)
            function processAddVariation() {
                const selectedVariant = document.querySelector('input[name="selected_variant"]:checked');
                const qtyInput = document.getElementById('modal-qty');
                const token = document.querySelector('meta[name="csrf-token"]').content;

                if (!selectedVariant) {
                    toastr.warning("Please select an option.");
                    return;
                }

                // ডাটা 'items' অ্যারের ভেতরে পাঠাতে হবে
                const postData = {
                    items: [{
                        variation_id: selectedVariant.value,
                        qty: qtyInput ? qtyInput.value : 1
                    }]
                };

                fetch("{{ route('cart.add') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json', // এটি যোগ করা জরুরি
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify(postData)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                            closeModal();
                            if (typeof isOrderNowGlobal !== 'undefined' && isOrderNowGlobal) {
                                window.location.href = "{{ route('checkout.index') }}";
                            } else {
                                toastr.success(data.message);
                            }
                        } else {
                            toastr.error(data.message || "Something went wrong.");
                        }
                    }).catch(err => toastr.error("Server error."));
            }

            // ২. সরাসরি সিঙ্গেল প্রোডাক্ট অ্যাড করার ফাংশন
            function addSingleToCart(id, isOrderNow = false) {
                const token = document.querySelector('meta[name="csrf-token"]').content;

                // ডাটা 'items' অ্যারের ভেতরে পাঠাতে হবে
                const postData = {
                    items: [{
                        id: id,
                        qty: 1
                    }]
                };

                fetch("{{ route('cart.add') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json', // এটি যোগ করা জরুরি
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify(postData)
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
                    }).catch(err => toastr.error("Server error."));
            }
        </script>
    @endpush
@endonce
