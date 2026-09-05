@extends('template5.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.product-details-meta', ['setup' => $setup])
@endsection
@section('content')

@php
    $isWishlisted = false;
    if (auth('customer')->check()) {
        $isWishlisted = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
            ->where('product_id', $product->id)
            ->exists();
    }

    $reviews = $product->reviews;
    $avgRating = $reviews->avg('rating') ?? 0;
    $totalReviews = $reviews->count();
@endphp

<!-- ============ FOOD DETAILS ============ -->
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-10 lg:py-20">

  <!-- Dynamic Breadcrumb -->
  <div class="mb-6 flex items-center gap-2 text-sm text-smoke overflow-x-auto whitespace-nowrap no-scrollbar">
    <a href="{{ url('/') }}" class="hover:text-ember transition-colors">Home</a>

    @php $mega = $product->mega_categories?->first(); @endphp
    @if ($mega)
      <span>/</span>
      <a href="{{ route('category.products', $mega->slug) }}" class="hover:text-ember transition-colors">{{ $mega->name }}</a>
    @endif

    @php $sub = $product->sub_categories?->first(); @endphp
    @if ($sub)
      <span>/</span>
      <a href="{{ route('category.products', $sub->slug) }}" class="hover:text-ember transition-colors">{{ $sub->name }}</a>
    @endif

    <span>/</span>
    <span class="text-coal font-medium truncate max-w-[200px] md:max-w-none">{{ $product->title }}</span>
  </div>

  <div class="grid lg:grid-cols-2 gap-12 lg:gap-20">
<div class="space-y-4 min-w-0">
  <div class="relative h-[400px] lg:h-[500px] w-full rounded-2xl overflow-hidden bg-white shadow-sm sear-corner">
    <img id="mainImage"
         src="{{ $product->display_image_url ?? $product->thumbnail_url }}"
         alt="{{ $product->title }}"
         class="w-full h-full object-cover">

    @if ($product->display_price_data->regular_price > $product->display_price_data->sale_price)
      <span class="absolute top-5 left-5 bg-ember text-white text-xs font-mono uppercase tracking-wide px-3 py-1.5 rounded-full">
        {{ number_format((($product->display_price_data->regular_price - $product->display_price_data->sale_price) / $product->display_price_data->regular_price) * 100) }}% Off
      </span>
    @endif
  </div>

  <!-- Thumbnail Gallery with Arrows -->
<div class="relative group/gallery">
  <!-- Left Arrow -->
  <button type="button" id="thumb-arrow-left" onclick="scrollThumbnails(-1)"
    class="{{ count($allProductImages) > 3 ? 'flex' : 'hidden' }} absolute -left-2 md:-left-3 top-1/2 -translate-y-1/2 z-10 w-6 h-6 md:w-8 md:h-8 rounded-full bg-white border border-coal/10 shadow-md items-center justify-center hover:bg-ember hover:text-white hover:border-ember transition-colors">
    <svg width="12" height="12" class="md:w-[14px] md:h-[14px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M15 18l-6-6 6-6"/></svg>
  </button>

  <div id="thumbnail-container" class="flex gap-3 md:gap-4 overflow-x-auto no-scrollbar pb-1 w-full min-w-0 scroll-smooth cursor-grab active:cursor-grabbing {{ count($allProductImages) > 3 ? 'px-8 md:px-10' : '' }}">
    @foreach ($allProductImages as $index => $imgUrl)
      <button onclick="changeImage('{{ $imgUrl }}')"
        class="h-20 w-20 md:h-24 md:w-24 shrink-0 rounded-xl overflow-hidden border-2 transition-colors {{ $index == 0 ? 'border-ember ring-offset-2 ring-2 ring-ember/20' : 'border-coal/10 hover:border-ember/50' }}">
        <img src="{{ $imgUrl }}"
             onerror="this.src='{{ asset('images/template1/frontend/default.webp') }}'"
             class="w-full h-full object-cover pointer-events-none">
      </button>
    @endforeach
  </div>

  <!-- Right Arrow -->
  <button type="button" id="thumb-arrow-right" onclick="scrollThumbnails(1)"
    class="{{ count($allProductImages) > 3 ? 'flex' : 'hidden' }} absolute -right-2 md:-right-3 top-1/2 -translate-y-1/2 z-10 w-6 h-6 md:w-8 md:h-8 rounded-full bg-white border border-coal/10 shadow-md items-center justify-center hover:bg-ember hover:text-white hover:border-ember transition-colors">
    <svg width="12" height="12" class="md:w-[14px] md:h-[14px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M9 18l6-6-6-6"/></svg>
  </button>
</div>
</div>

    <!-- Product Info -->
<div class="flex flex-col">
      <div class="flex items-center gap-2 mb-3">
        <div class="flex items-center">
          @for ($i = 1; $i <= 5; $i++)
            <svg width="16" height="16" viewBox="0 0 24 24"
                 fill="{{ $i <= round($avgRating) ? '#C99A45' : 'none' }}"
                 stroke="#C99A45" stroke-width="{{ $i <= round($avgRating) ? '0' : '2' }}"
                 class="{{ $i > 1 ? 'ml-1' : '' }}">
              <polygon points="12 2 15 9 22 9 16.5 13.5 18.5 21 12 17 5.5 21 7.5 13.5 2 9 9 9"/>
            </svg>
          @endfor
        </div>
        <span class="text-sm font-medium">{{ number_format($avgRating, 1) }}</span>
        <span class="text-sm text-smoke">({{ $totalReviews }} Reviews)</span>
      </div>

      <h1 class="font-display font-semibold text-4xl sm:text-5xl text-coal leading-tight mb-2">{{ $product->title }}</h1>
<p class="text-xl font-mono font-semibold text-ember mb-6">
  <span id="main-sale-price">{{ $setup->currency }} {{ number_format($product->display_price_data->sale_price) }}</span>
  <span id="main-regular-price" class="text-smoke line-through ml-1.5 text-base {{ $product->display_price_data->regular_price > $product->display_price_data->sale_price ? '' : 'hidden' }}">
    {{ $setup->currency }} {{ number_format($product->display_price_data->regular_price) }}
  </span>
</p>

      <div class="text-smoke leading-relaxed mb-8">
        {!! $product->short_description ?? 'No detailed description available for this product.' !!}
      </div>

      <!-- Dynamic Variations (replaces static Select Size / Add Extras) -->
      @if ($product->type === 'variation')
        <div id="dynamic-attributes-container" class="mb-8"></div>
      @endif

      <input type="hidden" id="selected-variation-id" value="">

<div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-5 mt-2">

  <!-- Row 1 (mobile): Qty + Wishlist একসাথে, ঠিকভাবে align করা -->
  <div class="flex items-center justify-between sm:justify-start gap-3 sm:contents">

    <!-- Qty Selector -->
    <div class="flex items-center justify-between border border-coal/15 rounded-full bg-white h-12 w-32 sm:order-1">
      <button type="button" onclick="changeQty(-1)" class="w-10 h-full flex items-center justify-center text-coal hover:text-ember transition-colors">-</button>
      <span id="main-qty" class="flex-1 text-center font-medium">1</span>
      <button type="button" onclick="changeQty(1)" class="w-10 h-full flex items-center justify-center text-coal hover:text-ember transition-colors">+</button>
    </div>

    <!-- Wishlist Button -->
    <button id="btn-wish" type="button" onclick="toggleWishlist({{ $product->id }})"
      class="w-12 h-12 rounded-full border flex items-center justify-center transition-colors shrink-0 sm:order-4
      {{ $isWishlisted ? 'border-ember text-ember bg-ember/5' : 'border-coal/15 text-coal hover:border-ember hover:text-ember' }}">
      <svg id="wish-icon-main" width="20" height="20" viewBox="0 0 24 24"
           fill="{{ $isWishlisted ? '#D6431F' : 'none' }}" stroke="currentColor" stroke-width="2">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
      </svg>
    </button>

  </div>

  <!-- Row 2 (mobile): Order Now + Add to Cart পাশাপাশি -->
  <div class="flex items-center gap-3 sm:contents">

    <button id="btn-order" onclick="handleAddToCart(true)"
      {{ ($product->manage_stock && $product->available_stock <= 0) ? 'disabled' : '' }}
      class="flex-1 sm:order-2 sm:flex-1 h-12 secondary-bg hover:bg-yellow-500 text-secondary rounded-full flex items-center justify-center gap-2 text-sm sm:text-lg font-medium transition-all disabled:opacity-40 disabled:cursor-not-allowed">
      Order Now
    </button>

    <button id="btn-cart" onclick="handleAddToCart()"
      class="flex-1 sm:order-3 sm:flex-1 h-12 bg-ember hover:bg-ember-600 transition-colors text-white rounded-full flex items-center justify-center gap-2 text-sm sm:text-lg font-medium">
      Add to Cart
    </button>

  </div>

</div>

   
    </div>
  </div>
</section>

<!-- ============ RELATED PRODUCTS ============ -->
@if (isset($relatedProducts) && $relatedProducts->count() > 0)
<section class="max-w-7xl mx-auto px-6 lg:px-10 py-10 lg:py-20 border-t border-coal/5">
  <div class="flex items-end justify-between mb-8">
    <h2 class="font-display font-semibold text-2xl sm:text-3xl">You might also like</h2>
  </div>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
    @foreach ($relatedProducts->take(4) as $related)
      <article class="menu-card group sear-corner bg-white border border-coal/10 rounded-2xl overflow-hidden hover:shadow-[0_24px_50px_-24px_rgba(24,19,15,0.35)] hover:-translate-y-1 transition-all">
        <x-template1.product-card :product="$related" />
      </article>
    @endforeach
  </div>
</section>
@endif

@endsection

@push('scripts')
<script>
  const attributeGroups = @json($attributeGroups ?? []);
  const allVariations = @json($formattedVariations ?? []);
  const valueImages = @json($valueImages ?? []);
  const defaultGalleries = @json($defaultGalleries ?? []);
  const groupCategories = @json($groupCategories ?? []);

  let activeFilters = {};
  attributeGroups.forEach(group => activeFilters[group] = null);
  let finalSelectedVariationIds = [];

function renderAttributes() {
    const container = document.getElementById('dynamic-attributes-container');
    if (!container) return;
    container.innerHTML = '';

    let currentlyValidVariations = allVariations;

    for (let i = 0; i < attributeGroups.length; i++) {
      const groupName = attributeGroups[i];
      const isLastGroup = (i === attributeGroups.length - 1);
      const isSingleGroup = groupCategories[groupName] === 'single'; // ============ নতুন ============

      let availableValues = {};
      currentlyValidVariations.forEach(v => {
        if (v.attributes[groupName]) availableValues[v.attributes[groupName].id] = v.attributes[groupName].name;
      });

      let groupHtml = `<div class="mb-6"><h3 class="font-display font-semibold mb-3">Choose ${groupName}</h3><div class="flex flex-wrap gap-3">`;

      for (const [valId, valName] of Object.entries(availableValues)) {
        // ============ পরিবর্তিত অংশ ============
        // Single-choice attribute-এর জন্যই activeFilters চেক হবে
        // Multi-choice attribute-এর জন্য শুধু finalSelectedVariationIds-এ আছে কিনা সেটাই চেক হবে
        const isFilterActive = isSingleGroup && (activeFilters[groupName] == valId);
        const isVariationSelected = checkIsSelected(groupName, valId);

        const activeClass = (isFilterActive || isVariationSelected)
          ? 'border-2 border-ember text-ember bg-ember/5'
          : 'border border-coal/15 text-coal hover:border-coal';

        let btnContent = valueImages[valId]
          ? `<img src="${valueImages[valId]}" class="w-6 h-6 rounded-full object-cover mr-2 inline-block"> ${valName}`
          : valName;

        groupHtml += `<button type="button" onclick="handleSelection('${groupName}', ${valId}, ${isLastGroup})" class="px-5 py-2.5 rounded-full text-sm font-medium transition-colors ${activeClass}">${btnContent}</button>`;
      }
      groupHtml += `</div></div>`;
      container.innerHTML += groupHtml;

      if (!activeFilters[groupName]) break;
      currentlyValidVariations = currentlyValidVariations.filter(v => v.attributes[groupName].id == activeFilters[groupName]);
    }
    document.getElementById('selected-variation-id').value = finalSelectedVariationIds.join(',');
}
function updatePriceDisplay(salePrice, regularPrice) {
    const saleEl = document.getElementById('main-sale-price');
    const regularEl = document.getElementById('main-regular-price');
    const currency = "{{ $setup->currency }}";

    if (saleEl) {
        saleEl.innerText = currency + ' ' + Math.round(salePrice).toLocaleString();
    }

    if (regularEl) {
        if (regularPrice && regularPrice > salePrice) {
            regularEl.innerText = currency + ' ' + Math.round(regularPrice).toLocaleString();
            regularEl.classList.remove('hidden');
        } else {
            regularEl.classList.add('hidden');
        }
    }
}
function recalculateSelectedPrice() {
    if (finalSelectedVariationIds.length === 0) {
        updatePriceDisplay(
            {{ $product->display_price_data->sale_price }},
            {{ $product->display_price_data->regular_price }}
        );
        return;
    }

    let totalSale = 0;
    let totalRegular = 0;

    finalSelectedVariationIds.forEach(id => {
        const v = allVariations.find(v => v.id === id);
        if (v) {
            totalSale += parseFloat(v.price);
            totalRegular += parseFloat(v.regular_price || v.price); // regular_price না থাকলে price-ই ধরা হবে (fallback)
        }
    });

    updatePriceDisplay(totalSale, totalRegular);
}
function updateGalleryThumbnails(images) {
    const container = document.getElementById('thumbnail-container');
    const leftArrow = document.getElementById('thumb-arrow-left');
    const rightArrow = document.getElementById('thumb-arrow-right');
    if (!container) return;

    container.innerHTML = '';
    images.forEach((img, i) => {
      container.innerHTML += `
        <button onclick="changeImage('${img}')"
          class="h-20 w-20 md:h-24 md:w-24 shrink-0 rounded-xl overflow-hidden border-2 transition-colors ${i === 0 ? 'border-ember ring-offset-2 ring-2 ring-ember/20' : 'border-coal/10 hover:border-ember/50'}">
          <img src="${img}" onerror="this.src='{{ asset('images/template1/frontend/default.webp') }}'" class="w-full h-full object-cover pointer-events-none">
        </button>`;
    });

    // ============ Arrow visibility + container padding dynamically আপডেট ============
    const showArrows = images.length > 3;

    if (leftArrow) leftArrow.classList.toggle('hidden', !showArrows);
    if (leftArrow) leftArrow.classList.toggle('flex', showArrows);
    if (rightArrow) rightArrow.classList.toggle('hidden', !showArrows);
    if (rightArrow) rightArrow.classList.toggle('flex', showArrows);

    container.classList.toggle('px-8', showArrows);
    container.classList.toggle('md:px-10', showArrows);
}

function handleSelection(group, valId, isLastGroup) {
    if (!isLastGroup) {
      activeFilters[group] = (activeFilters[group] == valId) ? null : valId;
      let idx = attributeGroups.indexOf(group);
      for (let i = idx + 1; i < attributeGroups.length; i++) activeFilters[attributeGroups[i]] = null;
      if (finalSelectedVariationIds.length === 0) updateGalleryThumbnails(defaultGalleries);
    } else {
      activeFilters[group] = valId;
      let matched = allVariations.find(v => attributeGroups.every(g => v.attributes[g].id == activeFilters[g]));

      if (matched) {
        let isSingleChoice = false;
        for (let gName in matched.attributes) {
          if (groupCategories[gName] === 'single') { isSingleChoice = true; break; }
        }

        if (isSingleChoice) {
          finalSelectedVariationIds = [matched.id];
          let combined = [...(matched.galleries || []), ...defaultGalleries];
          updateGalleryThumbnails([...new Set(combined)]);
          if (matched.main_image) changeImage(matched.main_image);
        } else {
          const index = finalSelectedVariationIds.indexOf(matched.id);
          if (index > -1) {
            finalSelectedVariationIds.splice(index, 1);
            if (finalSelectedVariationIds.length === 0) updateGalleryThumbnails(defaultGalleries);
          } else {
            finalSelectedVariationIds.push(matched.id);
            let combined = [...(matched.galleries || []), ...defaultGalleries];
            updateGalleryThumbnails([...new Set(combined)]);
            if (matched.main_image) changeImage(matched.main_image);
          }
        }

        recalculateSelectedPrice();
      }
    }
    renderAttributes();
}
  function checkIsSelected(groupName, valId) {
    return allVariations.some(v => finalSelectedVariationIds.includes(v.id) && v.attributes[groupName].id == valId);
  }

  function changeImage(src) {
    document.getElementById('mainImage').src = src;
  }

  function changeQty(val) {
    let q = document.getElementById('main-qty');
    let newVal = parseInt(q.innerText) + val;
    if (newVal >= 1) q.innerText = newVal;
  }
function scrollThumbnails(direction) {
    const container = document.getElementById('thumbnail-container');
    if (!container) return;

    const scrollAmount = 110;
    container.scrollBy({
        left: direction * scrollAmount,
        behavior: 'smooth'
    });
}
function initThumbnailDragScroll() {
    const container = document.getElementById('thumbnail-container');
    if (!container) return;

    let isDown = false;
    let startX;
    let scrollLeftStart;
    let hasDragged = false;

    container.addEventListener('mousedown', (e) => {
        isDown = true;
        hasDragged = false;
        startX = e.pageX - container.offsetLeft;
        scrollLeftStart = container.scrollLeft;
    });

    container.addEventListener('mouseleave', () => {
        isDown = false;
    });

    container.addEventListener('mouseup', () => {
        isDown = false;
    });

    container.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - container.offsetLeft;
        const walk = (x - startX) * 1.5; // drag speed multiplier
        if (Math.abs(walk) > 5) hasDragged = true; // সামান্য নড়াচড়াকে drag হিসেবে ধরা হবে না
        container.scrollLeft = scrollLeftStart - walk;
    });

    // Drag করার পর accidental click (thumbnail change) আটকানো
    container.addEventListener('click', (e) => {
        if (hasDragged) {
            e.stopPropagation();
            e.preventDefault();
        }
    }, true);
}
document.addEventListener("DOMContentLoaded", () => {
    if (attributeGroups.length > 0) renderAttributes();
    initThumbnailDragScroll();

    // scroll reveal
    const reveals = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.12 });
    reveals.forEach(el => io.observe(el));
});

  function handleAddToCart(isOrderNow = false) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const qty = document.getElementById('main-qty').innerText;
    let items = [];

    let varIds = document.getElementById('selected-variation-id').value;
    if ('{{ $product->type }}' === 'variation') {
      let missingAttribute = attributeGroups.find(group => !activeFilters[group]);
      if (missingAttribute) {
        toastr.warning(`Please select ${missingAttribute}`);
        return;
      }
      varIds.split(',').forEach(id => items.push({ variation_id: id, qty }));
    } else {
      items.push({ id: {{ $product->id }}, qty });
    }

    fetch("{{ route('cart.add') }}", {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token
      },
      body: JSON.stringify({ items })
    })
      .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (typeof fbq === 'function') {
                            fbq('track', 'AddToCart', {
                                content_ids: ['{{ $product->id }}'],
                                content_type: 'product',
                                value: {{ $product->sale_price ?? 0 }} * qty,
                                currency: '{{ $setup->currency ?? "BDT" }}'
                            });
                        }
                        if (typeof ttq === 'function') {
                            ttq.track('AddToCart', {
                                content_id: '{{ $product->id }}',
                                content_type: 'product',
                                value: {{ $product->sale_price ?? 0 }} * qty,
                                currency: '{{ $setup->currency ?? "BDT" }}'
                            });
                        }
                        
                        document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                        if (isOrderNow) window.location.href = "{{ route('checkout.index') }}";
                        else toastr.success(data.message);
                    } else toastr.error(data.message);
                });
  }

  function toggleWishlist(productId) {
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const btnWish = document.getElementById('btn-wish');
    const wishIcon = document.getElementById('wish-icon-main');

    fetch("{{ route('wishlist.toggle') }}", {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
      body: JSON.stringify({ product_id: productId })
    })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'unauthorized') {
          toastr.warning(data.message);
        } else if (data.status === 'added') {
          btnWish.classList.add('border-ember', 'text-ember', 'bg-ember/5');
          btnWish.classList.remove('border-coal/15', 'text-coal');
          wishIcon.setAttribute('fill', '#D6431F');
          toastr.success(data.message);
        } else {
          btnWish.classList.remove('border-ember', 'text-ember', 'bg-ember/5');
          btnWish.classList.add('border-coal/15', 'text-coal');
          wishIcon.setAttribute('fill', 'none');
          toastr.info(data.message);
        }
      });
  }

  document.addEventListener("DOMContentLoaded", () => {
    if (attributeGroups.length > 0) renderAttributes();

    // scroll reveal
    const reveals = document.querySelectorAll('.reveal');
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.12 });
    reveals.forEach(el => io.observe(el));
  });
</script>
@endpush

@push('scripts')
    @include('components.meta-info.pixel-events', ['event' => 'ViewContent', 'data' => ['product' => $product]])
@endpush