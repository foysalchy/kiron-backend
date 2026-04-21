@props(['product'])

@php
    $isWishlisted = false;
    if (auth('customer')->check()) {
        $isWishlisted = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
            ->where('product_id', $product->id)
            ->exists();
    }

    // dicount percentage calculation
    $priceData = $product->display_price_data;
    $salePrice = $priceData->sale_price;
    $regularPrice = $priceData->regular_price;
    $isVar = $priceData->is_variation;

    // Single vs Variation
    $discountLabel = '';
    if ($regularPrice > $salePrice) {
        // check if product has variations and get discount from first variation if exists
        if ($isVar) {
            $firstVar = $product->variations->first();
            $d_type = $firstVar->discount_type ?? null;
            $d_val = $firstVar->discount ?? 0;
        } else {
            $d_type = $product->discount_type;
            $d_val = $product->discount;
        }

        if ($d_type == 'percent' && $d_val > 0) {
            $discountLabel = round($d_val) . '%';
        } elseif ($d_val > 0) {
            $discountLabel = $setup->currency . number_format($regularPrice - $salePrice, 0);
        }
    }
@endphp

<div
    class="group relative flex flex-col p-4 bg-white border border-gray-200 rounded-lg hover:shadow-xs transition-all duration-300 h-full">

    <!-- Image Section -->
    <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3 shrink-0">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block w-full h-full">
            <img src="{{ $product->thumbnail_url }}" alt="{{ $product->title }}"
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
                class="absolute top-2 left-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm z-10">
                -{{ $discountLabel }}
            </div>
        @endif
    </div>

    <!-- Info Area -->
    <div class="flex flex-col flex-grow">
        <a href="{{ route('product.details', $product->slug ?? $product->id) }}" class="block group/title">
            <h3
                class="text-lg font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[40px] group-hover/title:text-[#BD4F00] transition-colors">
                {{ $product->title }}
            </h3>
        </a>

        @php
            $avgRating = $product->reviews_avg_rating ?? 0;
            $totalReviews = $product->reviews_count ?? 0;

        @endphp
        <div class="flex items-center gap-1 mb-2">
            <div class="flex text-yellow-500 text-xs">
                @for ($i = 1; $i <= 5; $i++)
                    <i
                        class="{{ $i <= round($avgRating) ? 'fas' : 'far' }} fa-star {{ $i <= round($avgRating) ? '' : 'text-gray-200' }}"></i>
                @endfor
            </div>
            <span class="text-xs text-gray-600 font-bold">({{ $totalReviews }})</span>
        </div>
    </div>

    <!-- Price & Button -->
    <div class="mt-auto pt-3 flex items-center justify-between gap-1">
        <div class="flex flex-col min-w-0">
            <span class="text-md font-bold text-[#BD4F00] truncate">
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

        <button {{ $isOutOfStock ? 'disabled' : '' }} aria-label="out of stock"
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
            class="flex-1 bg-[#1D2128] text-white px-3 py-2 rounded-lg text-sm font-semibold transition-all shrink-0 whitespace-nowrap
    {{ $isOutOfStock ? 'opacity-40 cursor-not-allowed' : 'hover:bg-[#BD4F00] cursor-pointer' }}">

            @if ($isOutOfStock)
                <i class="fas fa-exclamation-circle mr-1"></i> Stock Out
            @else
                Add to Cart
            @endif
        </button>

        {{-- <button
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
            class="bg-[#1D2128] text-white px-3 py-2 rounded-lg text-sm font-semibold hover:bg-[#FF6A00] transition-all shrink-0 cursor-pointer whitespace-nowrap">
            Add to Cart
        </button> --}}
    </div>
</div>
