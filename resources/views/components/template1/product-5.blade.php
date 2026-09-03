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

    $isOutOfStock = $product->manage_stock ? ($product->available_stock <= 0) : false;

    $avgRating = $product->reviews_avg_rating ?? 0;
    $totalReviews = $product->reviews_count ?? 0;

    $productUrl = route('product.details', $product->slug ?? $product->id);
@endphp

<article
    onclick="window.location.href='{{ $productUrl }}'"
    class="menu-card group sear-corner bg-white border border-coal/10 rounded-2xl overflow-hidden hover:shadow-[0_10px_25px_-10px_rgba(24,19,15,0.3)]hover:-translate-y-1 transition-all cursor-pointer flex flex-col h-full">

  <div class="h-40 relative overflow-hidden">
    <img
      src="{{ $product->thumbnail_url ?? asset('images/template1/frontend/default.webp') }}"
      alt="{{ $product->title }}"
      class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
    >

    <div class="absolute inset-0 bg-black/10"></div>

    <button type="button"
      onclick="event.stopPropagation(); toggleWishlist({{ $product->id }})"
      aria-label="wishlist"
      class="absolute top-3 right-3 z-30 w-8 h-8 rounded-full bg-white/90 flex items-center justify-center hover:text-ember transition-colors">
      <svg id="wish-icon-{{ $product->id }}" width="15" height="15" viewBox="0 0 24 24"
        fill="{{ $isWishlisted ? '#D6431F' : 'none' }}"
        stroke="{{ $isWishlisted ? '#D6431F' : 'currentColor' }}" stroke-width="2">
        <path d="M12 21s-7-4.6-9.5-9C.7 8 2 4.5 5.3 4a4.7 4.7 0 0 1 6.7 2 4.7 4.7 0 0 1 6.7-2C21.9 4.5 23.3 8 21.5 12 19 16.4 12 21 12 21z"/>
      </svg>
    </button>
  </div>

  <div class="p-5 flex flex-col flex-1">

    <p class="font-display font-semibold text-base leading-snug line-clamp-2  group-hover:text-ember transition-colors">
      {{ $product->title }}
    </p>

    @if ($company->is_review == 1)
      <div class="flex items-center gap-1 mt-2.5">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="#C99A45" stroke="#C99A45">
          <polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 17 5.5 21 7.5 13.5 2 9 9 9"/>
        </svg>
        <span class="text-xs font-medium">{{ number_format($avgRating, 1) }}</span>
        <span class="text-xs text-smoke">({{ $totalReviews }})</span>
      </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0 mt-auto pt-1">

      <div class="flex items-center gap-1.5 flex-wrap">
        <span class="font-mono font-semibold">
          @if(($setup->currency_position ?? 'left') == 'left')
            {{ $setup->currency }} {{ number_format($salePrice) }}{{ $isVar ? '+' : '' }}
          @else
            {{ number_format($salePrice) }}{{ $isVar ? '+' : '' }} {{ $setup->currency }}
          @endif
        </span>

        @if ($regularPrice > $salePrice)
          <span class="text-smoke line-through text-xs">
            @if(($setup->currency_position ?? 'left') == 'left')
              {{ $setup->currency }} {{ number_format($regularPrice) }}
            @else
              {{ number_format($regularPrice) }} {{ $setup->currency }}
            @endif
          </span>
        @endif
      </div>

<div class="flex items-center justify-between sm:justify-end gap-2 w-full sm:w-auto">

    <!-- Order Now Button -->
    <button
        {{ $isOutOfStock ? 'disabled' : '' }}
        aria-label="{{ $isOutOfStock ? 'Stock Out' : 'Order Now' }}"
        onclick="event.stopPropagation(); {{ $product->type === 'single' ? "addSingleToCart($product->id, true)" : "openVariationModal($product->id, true)" }}"
        class="w-9 h-9 rounded-full flex items-center justify-center transition-colors shrink-0
        {{ $isOutOfStock
            ? 'bg-smoke-300 text-white cursor-not-allowed'
            : 'secondary-bg text-secondary hover:bg-yellow-500 cursor-pointer' }}"
    >
        @if ($isOutOfStock)
            <i class="fas fa-exclamation-circle text-xs"></i>
        @else
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                <path d="M13 2 4 14h6l-1 8 9-12h-6z"/>
            </svg>
        @endif
    </button>

    <!-- Add to Cart Button -->
    <button
        {{ $isOutOfStock ? 'disabled' : '' }}
        aria-label="{{ $isOutOfStock ? 'Stock Out' : 'Add to Cart' }}"
        onclick="event.stopPropagation(); {{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
        class="w-9 h-9 rounded-full flex items-center justify-center transition-colors shrink-0
        {{ $isOutOfStock
            ? 'bg-smoke-300 text-white cursor-not-allowed'
            : 'bg-ember hover:bg-ember-600 text-white cursor-pointer' }}"
    >
        @if ($isOutOfStock)
            <i class="fas fa-exclamation-circle text-xs"></i>
        @else
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                <path d="M12 5v14M5 12h14"/>
            </svg>
        @endif
    </button>

</div>
    </div>

  </div>
</article>
@once
    @push('scripts')
        <script>
            const token = document.querySelector('meta[name="csrf-token"]').content;

            let modalSelectedIds = [];

            // ১. ভ্যারিয়েশন সিলেকশন লজিক (সংশোধিত)
            function handleModalSelection(btn, varId, price, type) {
                const activeClasses = ['border-[var(--primary-color)]', 'bg-orange-50', 'ring-1', 'ring-[var(--primary-color)]'];

                // টাইপটি ছোট হাতের অক্ষরে চেক করা হচ্ছে যাতে ভুল না হয়
                const isMultiple = (type.toLowerCase() === 'multiple');

                if (isMultiple) {
                    // মাল্টিপল টাইপ: টগল (Add/Remove) লজিক
                    if (modalSelectedIds.includes(varId)) {
                        modalSelectedIds = modalSelectedIds.filter(id => id !== varId);
                        btn.classList.remove(...activeClasses);
                    } else {
                        modalSelectedIds.push(varId);
                        btn.classList.add(...activeClasses);
                    }
                } else {
                    // সিঙ্গেল টাইপ: রেডিও লজিক
                    modalSelectedIds = [varId];
                    document.querySelectorAll('.modal-var-btn').forEach(el => el.classList.remove(...activeClasses));
                    btn.classList.add(...activeClasses);
                }

                // আইডিগুলো হিডেন ইনপুটে রাখা
                document.getElementById('modal-selected-ids').value = modalSelectedIds.join(',');
                updateModalTotal();
            }
            function updateModalTotal() {
                const selectedVariant = document.querySelector('input[name="selected_variant"]:checked');
                const qtyInput = document.getElementById('modal-qty');
                const totalDisplay = document.getElementById('modal-total-price-display');
                const unitPriceDisplay = document.getElementById('modal-unit-price');

                if (selectedVariant && qtyInput && totalDisplay) {
                    const unitPrice = parseFloat(selectedVariant.getAttribute('data-price'));
                    const qty = parseInt(qtyInput.value);

                    const currency = "{{ $setup->currency }}";
                    const pos = "{{ $setup->currency_position ?? 'left' }}";

                    const total = unitPrice * qty;

                    let unitText = (pos === 'left') ? currency + " " + unitPrice.toLocaleString() : unitPrice.toLocaleString() + " " + currency;
                    let totalText = (pos === 'left') ? currency + " " + total.toLocaleString() : total.toLocaleString() + " " + currency;

                    if(unitPriceDisplay) unitPriceDisplay.innerText = unitText;
                    if(totalDisplay) totalDisplay.innerText = totalText;
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

    function openVariationModal(id) {
                modalSelectedIds = []; // শুরুতে ক্লিয়ার
                const modal = document.getElementById('variation-modal');
                const contentArea = document.getElementById('modal-content-area');
                if (!modal) return;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                contentArea.innerHTML = '<div class="py-10 text-center"><i class="fas fa-spinner fa-spin text-2xl text-[var(--primary-color)]"></i></div>';

                fetch("/product-variation/" + id)
                    .then(res => res.text())
                    .then(html => {
                        contentArea.innerHTML = html;

                        const firstBtn = contentArea.querySelector('.modal-var-btn');
                        if (firstBtn) {
                            const firstId = parseInt(firstBtn.getAttribute('data-var-id'));
                            if (!modalSelectedIds.includes(firstId)) {
                                modalSelectedIds.push(firstId);
                            }
                            document.getElementById('modal-selected-ids').value = modalSelectedIds.join(',');
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
                if (modalSelectedIds.length === 0) {
                    toastr.warning("Please select at least one option."); return;
                }

                const qty = document.getElementById('modal-qty').value;
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const items = modalSelectedIds.map(id => ({ variation_id: id, qty: qty }));

                fetch("{{ route('cart.add') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ items: items })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // আপনার বিদ্যমান পিক্সেল ট্র্যাকিং লজিক
                            if (typeof fbq === 'function') {
                                fbq('track', 'AddToCart', { content_ids: ['{{ $product->id }}'], content_type: 'product', value: {{ $product->sale_price ?? 0 }} * qty, currency: '{{ $setup->currency ?? "BDT" }}' });
                            }
                            if (typeof ttq === 'function') {
                                ttq.track('AddToCart', { content_id: '{{ $product->id }}', content_type: 'product', value: {{ $product->sale_price ?? 0 }} * qty, currency: '{{ $setup->currency ?? "BDT" }}' });
                            }

                            document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                            closeModal();
                            modalSelectedIds = []; // রিসেট

                            if (typeof isOrderNowGlobal !== 'undefined' && isOrderNowGlobal) {
                                window.location.href = "{{ route('checkout.index') }}";
                            } else {
                                toastr.success(data.message);
                            }
                        }
                    });
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
                            if (typeof fbq === 'function') {
                                fbq('track', 'AddToCart', {
                                    content_ids: ['{{ $product->id }}'],
                                    content_type: 'product',
                                    value: {{ $product->sale_price ?? 0 }},
                                    currency: '{{ $setup->currency ?? "BDT" }}'
                                });
                            }
                            if (typeof ttq === 'function') {
                                ttq.track('AddToCart', {
                                    content_id: '{{ $product->id }}',
                                    content_type: 'product',
                                    value: {{ $product->sale_price ?? 0 }},
                                    currency: '{{ $setup->currency ?? "BDT" }}'
                                });
                            }
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