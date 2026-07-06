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
    $isOutOfStock = $product->available_stock <= 0;
@endphp

<div
    class="group bg-white border border-gray-100 rounded-xl flex flex-col h-full overflow-hidden transition-all duration-300 hover:shadow-xl">

    <!-- image section -->
    <div class="relative overflow-hidden aspect-square bg-[#f9f9f9]">
        <!-- Discount Badge -->
        @if ($discountLabel)
            <span
                class="absolute top-3 left-3 bg-[#be123c] text-white text-xs md:text-xs font-bold px-2 py-1 rounded-md z-20 shadow-sm">
                -{{ $discountLabel }}
            </span>
        @endif

        <!-- Wishlist -->
        <button type="button" onclick="toggleWishlist({{ $product->id }})" aria-label="wish button"
            class="absolute top-3 right-3 z-30 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md transition-transform active:scale-90 cursor-pointer">
            <i id="wish-icon-{{ $product->id }}"
                class="wish-icon-{{ $product->id }} {{ $isWishlisted ? 'fa-solid fa-heart text-red-500' : 'fa-regular fa-heart text-gray-400' }} text-sm"></i>
        </button>

        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block w-full h-full p-4"
            aria-label="product details">
            <img src="{{ $product->thumbnail_url ?? asset('./images/template1/frontend/cover.webp') }}"
                alt="{{ $product->title }}" width="300" height="300" loading="lazy"
                class="w-full h-full object-contain transition-transform duration-700 group-hover:scale-110">
        </a>
    </div>

    <!-- info area -->
    <div class="p-4 flex flex-col flex-grow bg-white">
        {{-- <!-- categories/sku -->
        <div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1 truncate">
            {{ $product->mega_categories?->pluck('name')->implode(', ') ?: 'General' }}
        </div> --}}

        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block flex-grow"
            aria-label="product details">
            <h3
                class="text-gray-900 text-sm md:text-base font-bold leading-tight line-clamp-2 group-hover:text-[#be123c] transition-colors">
                {{ $product->title }}
            </h3>
        </a>

        <!-- Price section -->
        <div class="flex items-center gap-2">
            @if ($regularPrice > $salePrice)
                <span class="text-gray-600 text-xs md:text-sm line-through font-medium">
                    {{ $setup->currency }}{{ number_format($regularPrice) }}
                </span>
            @endif
            <span class="text-[#be123c] text-base md:text-lg font-black">
                {{ $setup->currency }}{{ number_format($salePrice) }}{{ $isVar ? '+' : '' }}
            </span>
        </div>

        <!-- size variations snippet (If it's a variation product) -->
        <div class="mt-4 mb-3 flex flex-wrap gap-2">
            @if ($product->type === 'variation')
                @php
                    $previewValues = $product->variations->flatMap->attributes
                        ->pluck('attributeValue.name')
                        ->unique()
                        ->take(5);
                @endphp
                @foreach ($previewValues as $val)
                    <span
                        class="min-w-[30px] h-7 px-2 flex items-center justify-center border border-gray-200 rounded text-xs font-bold text-gray-600 hover:border-[#66267b] transition-colors">
                        {{ $val }}
                    </span>
                @endforeach
            @endif
        </div>

        <!-- action button -->
        <button {{ $isOutOfStock ? 'disabled' : '' }}
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
            aria-label="cart button"
            class="w-full text-white text-center text-xs md:text-sm font-bold rounded-lg mt-4 py-3 transition-all cursor-pointer shadow-sm flex items-center justify-center gap-2
            {{ $isOutOfStock ? 'bg-gray-300' : 'bg-[#be123c] hover:bg-red-700 active:scale-95' }}">

            @if ($isOutOfStock)
                STOCK OUT
            @else
                {{ $product->type === 'single' ? 'ADD TO CART' : 'SELECT OPTIONS' }}
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
