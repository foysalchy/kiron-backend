@props(['offer', 'company'])

@php
    $priceData = $offer->display_price_data;
    $salePrice = $priceData->sale_price;
    $regularPrice = $priceData->regular_price;
    $isVar = $priceData->is_variation;

    $avgRating = $offer->reviews_avg_rating ?? 0;
    $totalReviews = $offer->reviews_count ?? 0;

    $isOutOfStock = $offer->manage_stock ? ($offer->available_stock <= 0) : false;
@endphp

<a href="{{ url($offer->slug ?? $offer->id) }}" class="group shadow bg-white border border-ash/10 rounded-2xl overflow-hidden hover:border-[var(--primary-color)]/40 -translate-y-1 flex flex-col h-full">

  <div class="h-36 sm:h-52 relative overflow-hidden">
    <img
      src="{{ $offer->thumbnail_310_url ?? $offer->thumbnail_url ?? asset('images/template1/frontend/default.webp') }}"
      alt="{{ $offer->title ?? 'Offer' }}"
      class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
    >

    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>

    @if ($regularPrice > $salePrice)
      <span class="absolute top-2 left-2 sm:top-4 sm:left-4 bg-[var(--primary-color)] text-white text-[9px] sm:text-[11px] font-mono tracking-wide px-2 py-0.5 sm:px-3 sm:py-1 rounded-full">
        @php
          $discountPercent = $regularPrice > 0 ? round((($regularPrice - $salePrice) / $regularPrice) * 100) : 0;
        @endphp
        {{ $discountPercent }}% OFF
      </span>
    @endif
  </div>

  <div class="p-3 sm:p-6 flex flex-col flex-1">

    <p class="font-display font-semibold text-sm sm:text-lg text-gray-800 line-clamp-2 min-h-[20px] sm:min-h-[28px] group-hover:text-[var(--primary-color)] transition-colors">
      {{ $offer->title }}
    </p>


      <div class="flex items-center gap-1 mt-1.5 sm:mt-2.5">
        <svg width="11" height="11" class="sm:w-[13px] sm:h-[13px]" viewBox="0 0 24 24" fill="#C99A45" stroke="#C99A45">
          <polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 17 5.5 21 7.5 13.5 2 9 9 9"/>
        </svg>
        <span class="text-[10px] sm:text-xs font-medium">{{ number_format($avgRating, 1) }}</span>
        <span class="text-[10px] sm:text-xs text-gray-700">({{ $totalReviews }})</span>
      </div>

    <div class="flex items-center justify-between gap-3 mt-auto">
      <div class="font-mono shrink-0">
        <span class="text-sm sm:text-lg font-semibold text-black">
          @if(($setup->currency_position ?? 'left') == 'left')
            {{ $setup->currency }} {{ number_format($salePrice) }}{{ $isVar ? '+' : '' }}
          @else
            {{ number_format($salePrice) }}{{ $isVar ? '+' : '' }} {{ $setup->currency }}
          @endif
        </span>

        @if ($regularPrice > $salePrice)
          <span class="text-gray-600 line-through ml-1 sm:ml-1.5 text-xs sm:text-sm">
            @if(($setup->currency_position ?? 'left') == 'left')
              {{ $setup->currency }} {{ number_format($regularPrice) }}
            @else
              {{ number_format($regularPrice) }} {{ $setup->currency }}
            @endif
          </span>
        @endif
      </div>
    </div>

    <button
      type="button"
      {{ $isOutOfStock ? 'disabled' : '' }}
      onclick="event.preventDefault(); event.stopPropagation(); {{ $offer->type === 'single' ? "addSingleToCart($offer->id, true)" : "openVariationModal($offer->id, true)" }}"
      class="block bg-[var(--primary-color)] group-hover:bg-[var(--primary-color)]/90 transition-colors text-white text-xs sm:text-sm font-medium px-3 py-2 sm:px-4 sm:py-3 rounded-full w-full mt-2 sm:mt-3 text-center {{ $isOutOfStock ? 'opacity-60 cursor-not-allowed' : '' }}"
    >
      {{ $isOutOfStock ? 'Stock Out' : 'Order Now' }}
    </button>

  </div>
</a>

@once
    @push('scripts')
        <script>
            // Global state to track if Order Now was clicked
            let isOrderNowGlobal = false;

            let modalSelectedIds = [];

            function handleModalSelection(btn, varId, price, type) {
                const activeClasses = ['border-[var(--primary-color)]', 'bg-orange-50', 'ring-1', 'ring-[var(--primary-color)]'];

                const isMultiple = (type.toLowerCase() === 'multiple');

                if (isMultiple) {
                    if (modalSelectedIds.includes(varId)) {
                        modalSelectedIds = modalSelectedIds.filter(id => id !== varId);
                        btn.classList.remove(...activeClasses);
                    } else {
                        modalSelectedIds.push(varId);
                        btn.classList.add(...activeClasses);
                    }
                } else {
                    modalSelectedIds = [varId];
                    document.querySelectorAll('.modal-var-btn').forEach(el => el.classList.remove(...activeClasses));
                    btn.classList.add(...activeClasses);
                }

                document.getElementById('modal-selected-ids').value = modalSelectedIds.join(',');
                updateModalTotal();
            }

            function updateModalTotal() {
                const qty = parseInt(document.getElementById('modal-qty').value) || 1;
                const totalDisplay = document.getElementById('modal-total-price-display');
                const currency = "{{ $setup->currency }}";
                const pos = "{{ $setup->currency_position ?? 'left' }}";

                let totalSum = 0;
                document.querySelectorAll('.modal-var-btn.bg-orange-50').forEach(el => {
                    totalSum += parseFloat(el.getAttribute('data-price') || 0);
                });

                const finalTotal = totalSum * qty;

                if (totalDisplay) {
                    if (totalSum > 0) {
                        totalDisplay.innerText = (pos === 'left') ? currency + " " + finalTotal.toLocaleString() : finalTotal.toLocaleString() + " " + currency;
                    } else {
                        totalDisplay.innerText = (pos === 'left') ? currency + " 0" : "0 " + currency;
                    }
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
                modalSelectedIds = [];
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
                            document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                            closeModal();
                            modalSelectedIds = [];

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
