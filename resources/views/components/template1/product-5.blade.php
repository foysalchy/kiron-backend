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
@endphp

<article class="menu-card group sear-corner bg-white border border-coal/10 rounded-2xl overflow-hidden hover:shadow-[0_24px_50px_-24px_rgba(24,19,15,0.35)] hover:-translate-y-1 transition-all">

  <div class="h-40 relative overflow-hidden">
    <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block w-full h-full">
      <img
        src="{{ $product->thumbnail_url ?? asset('images/template1/frontend/default.webp') }}"
        alt="{{ $product->title }}"
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
      >
    </a>

    <div class="absolute inset-0 bg-black/10"></div>

    <button type="button" onclick="toggleWishlist({{ $product->id }})" aria-label="wishlist"
      class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 flex items-center justify-center hover:text-ember transition-colors">
      <svg id="wish-icon-{{ $product->id }}" width="15" height="15" viewBox="0 0 24 24"
        fill="{{ $isWishlisted ? '#D6431F' : 'none' }}"
        stroke="{{ $isWishlisted ? '#D6431F' : 'currentColor' }}" stroke-width="2">
        <path d="M12 21s-7-4.6-9.5-9C.7 8 2 4.5 5.3 4a4.7 4.7 0 0 1 6.7 2 4.7 4.7 0 0 1 6.7-2C21.9 4.5 23.3 8 21.5 12 19 16.4 12 21 12 21z"/>
      </svg>
    </button>
  </div>

  <div class="p-5">

    <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block group/title">
      <h3 class="font-display font-semibold text-base leading-snug line-clamp-1 group-hover/title:text-ember transition-colors">
        {{ $product->title }}
      </h3>
    </a>

    @if (!empty($product->short_description))
      <p class="text-smoke text-xs mt-1 leading-relaxed line-clamp-2">
        {{ Str::limit(strip_tags($product->short_description), 80) }}
      </p>
    @endif

    @if ($company->is_review == 1)
      <div class="flex items-center gap-1 mt-2.5">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="#C99A45" stroke="#C99A45">
          <polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 17 5.5 21 7.5 13.5 2 9 9 9"/>
        </svg>
        <span class="text-xs font-medium">{{ number_format($avgRating, 1) }}</span>
        <span class="text-xs text-smoke">({{ $totalReviews }})</span>
      </div>
    @endif

    <div class="flex items-center justify-between mt-3">
      <div class="flex flex-col">
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

      <button {{ $isOutOfStock ? 'disabled' : '' }}
        aria-label="{{ $isOutOfStock ? 'Stock Out' : 'Add to Cart' }}"
        onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
        class="w-9 h-9 rounded-full flex items-center justify-center transition-colors shrink-0
        {{ $isOutOfStock ? 'bg-smoke-300 text-white cursor-not-allowed' : 'bg-ember hover:bg-ember-600 text-white cursor-pointer' }}">
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
</article>

@once
  @push('scripts')
    <script>
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
          unitPriceDisplay.innerText = currency + " " + unitPrice.toLocaleString();
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

      function openVariationModal(id) {
        const modal = document.getElementById('variation-modal');
        const contentArea = document.getElementById('modal-content-area');
        if (!modal) return;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        contentArea.innerHTML = '<div class="py-10 text-center"><i class="fas fa-spinner fa-spin text-2xl text-ember"></i></div>';

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

      function processAddVariation() {
        const selectedVariant = document.querySelector('input[name="selected_variant"]:checked');
        const qtyInput = document.getElementById('modal-qty');
        const token = document.querySelector('meta[name="csrf-token"]').content;

        if (!selectedVariant) {
          toastr.warning("Please select an option.");
          return;
        }

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
            'Accept': 'application/json',
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
        const postData = { items: [{ id: id, qty: 1 }] };

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