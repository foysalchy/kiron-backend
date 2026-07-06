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
        if ($percentage > 0) { $discountLabel = $percentage . '%'; }
    }

    $isOutOfStock = $product->available_stock <= 0;
@endphp

<div class="max-w-[348px] group cursor-pointer bg-white border border-[#ddd] rounded-2xl flex flex-col h-full overflow-hidden transition-all duration-300 hover:shadow-md">

    <div class="relative">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}"
           class="hover:border-2 hover:border-[#d6bbdf] product-card-notch relative block aspect-[1/1.1] border-b border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)] bg-[#f9f9f9]">
            <div class="w-full h-full p-6 flex items-center justify-center">
                <img
                    src="{{ $product->thumbnail_url ?? asset('./images/template1/frontend/cover.webp') }}"
                    alt="{{ $product->title }}" height="300" width="300" loading="lazy"
                    class="max-w-full max-h-full object-contain transition-transform duration-700 group-hover:scale-110"
                />
            </div>
        </a>

        <div class="absolute -top-1 -right-1 z-20">
            <div class="bg-white p-1 rounded-full">
                <button
                    type="button"
                    onclick="toggleWishlist({{ $product->id }})"
                    class="w-10 h-10 md:w-11 md:h-11 rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all cursor-pointer {{ $isWishlisted ? 'bg-red-500 text-white' : 'bg-[#66267b] text-white' }}"
                    aria-label="Add to Wishlist">
                    <i class="{{ $isWishlisted ? 'fa-solid' : 'fa-regular' }} fa-heart text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="p-4 flex flex-col flex-grow">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block flex-grow">
            <h3 class="text-[#0f172a] text-base md:text-lg font-bold leading-tight tracking-tight line-clamp-2 min-h-[44px] group-hover:text-[#66267b] transition-colors">
                {{ $product->title }}
            </h3>
        </a>

        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 pt-3 font-manrope">
            <div class="flex items-center gap-2">
                <span class="text-[#005c7a] text-lg md:text-xl font-black">
                    {{ $setup->currency ?? '৳' }}{{ number_format($salePrice) }}{{ $isVar ? '+' : '' }}
                </span>
                @if ($regularPrice > $salePrice)
                    <span class="text-[#52525b] text-xs md:text-sm line-through font-medium">
                        {{ $setup->currency ?? '৳' }}{{ number_format($regularPrice) }}
                    </span>
                @endif
            </div>

            <div class="basis-full h-0 sm:hidden"></div>

            @if ($discountLabel)
                <span class="bg-[#facc15] text-[#0f172a] text-[10px] md:text-xs font-black px-2 py-0.5 rounded-full uppercase">
                    -{{ $discountLabel }} OFF
                </span>
            @endif
        </div>

        <button
            {{ $isOutOfStock ? 'disabled' : '' }}
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
            class="w-full text-white text-center text-sm md:text-base rounded-full border-0 mt-4 py-3 transition-all cursor-pointer px-4 font-bold shadow-sm flex items-center justify-center gap-2
            {{ $isOutOfStock ? 'bg-gray-400 cursor-not-allowed' : 'bg-[#66267b] hover:bg-[#521d63] active:scale-95' }}">

            @if ($isOutOfStock)
                <i class="fas fa-exclamation-circle"></i> Stock Out
            @else
                <i class="fas fa-shopping-cart text-xs"></i> Add To Cart
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

            function addSingleToCart(id, isOrderNow = false) {
                const token = document.querySelector('meta[name="csrf-token"]').content;

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
                            'Accept': 'application/json', 
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
