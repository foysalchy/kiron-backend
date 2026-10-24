@props([
    'product',
    'setup',
    'landing' => null,
])

@php
    $hasVariations = ($product->type === 'variation') && ($product->variations && $product->variations->count() > 0);
    $pImg = $product->thumbnail_url ?? $product->display_image_url ?? asset('images/default.webp');
    $pRegularPrice = $product->regular_price ?? 0;
    $pDiscount = $product->discount ?? 0;
    $pSalePrice = $product->sale_price ?? ($pRegularPrice - $pDiscount);

    // Stock check for single product: only check available_stock if manage_stock is true
    $pIsSingleOutOfStock = ($product->type === 'single') && (
        $product->stock_status === 'out_of_stock' || 
        ($product->manage_stock && ($product->available_stock <= 0))
    );

    // Check variations stock and find first in-stock index
    $firstInStockVariationIndex = null;
    $allVariationsOutOfStock = true;
    if ($hasVariations) {
        foreach ($product->variations as $idx => $v) {
            $isVOutOfStock = (
                $v->stock_status === 'out_of_stock' ||
                ($product->manage_stock && ($v->available_stock <= 0))
            );
            if (!$isVOutOfStock) {
                $allVariationsOutOfStock = false;
                if ($firstInStockVariationIndex === null) {
                    $firstInStockVariationIndex = $idx;
                }
            }
        }

        // Use selected variation's price and image for initial card display
        $selectedVarIndex = ($firstInStockVariationIndex !== null) ? $firstInStockVariationIndex : 0;
        $selectedVar = $product->variations[$selectedVarIndex] ?? $product->variations->first();
        if ($selectedVar) {
            $pRegularPrice = $selectedVar->regular_price ?? 0;
            $pDiscount = $selectedVar->discount ?? 0;
            $pSalePrice = $pRegularPrice - $pDiscount;
            if (!empty($selectedVar->image)) {
                $pImg = \Illuminate\Support\Facades\Storage::disk('r2')->url($selectedVar->image);
            }
        }
    }
    
    // Language determination (bn or en, default bn)
    $formLang = data_get($landing->extras ?? null, 'order_form_lang') ?: ($landing->order_form_lang ?? 'bn');
    $isEn = ($formLang === 'en');

    $cardTrans = [
        'off' => $isEn ? ' OFF' : ' ছাড়',
        'variants' => $isEn ? ' Variants' : ' টি ভ্যারিয়েন্ট',
        'choose_option' => $isEn ? 'Choose an option' : 'পছন্দের অপশন বেছে নিন',
        'out_of_stock' => $isEn ? 'Out of Stock' : 'স্টক আউট',
        'add_to_cart' => $isEn ? 'Add to Cart' : 'কার্টে যোগ করুন',
        'order_now' => $isEn ? 'Order Now' : 'অর্ডার করুন',
    ];

    // Calculate discount label (Percentage or Fixed amount)
    $discountLabel = '';
    if ($pRegularPrice > $pSalePrice && $pRegularPrice > 0) {
        $diff = $pRegularPrice - $pSalePrice;
        $percentage = round(($diff / $pRegularPrice) * 100);
        if ($percentage > 0) {
            $discountLabel = $percentage . '%' . $cardTrans['off'];
        } else {
            $discountLabel = number_format($diff) . ($setup->currency ?? '৳') . $cardTrans['off'];
        }
    }

    // Overall initial out of stock status for this card
    $initialOutOfStock = $hasVariations ? $allVariationsOutOfStock : $pIsSingleOutOfStock;
@endphp

<div class="product-card group bg-white rounded-2xl p-4 md:p-5 shadow-xl hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-[#35B11E] flex flex-col justify-between text-left"
    data-product-id="{{ $product->id }}">
    <div>
        {{-- Thumbnail Container --}}
        <div class="w-full aspect-square rounded-xl overflow-hidden flex items-center justify-center relative">
            <img src="{{ $pImg }}" 
                alt="{{ $product->title }}" 
                loading="lazy"
                class="card-img w-full h-full object-contain group-hover:scale-105 transition-transform duration-300">
            
            <span class="card-discount-badge absolute top-2 left-2 bg-[#b45309] text-white text-[11px] font-bold px-2 py-0.5 rounded-md shadow-sm {{ empty($discountLabel) ? 'hidden' : '' }}">
                {{ $discountLabel }}
            </span>

            @if($product->is_free_delivery)
                <span class="absolute bottom-2 left-2 z-10 bg-gray-800 text-white text-[10px] md:text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">
                    Free Shipping
                </span>
            @endif

            @if($hasVariations)
                <span class="absolute bottom-2 right-2 bg-gray-900/80 backdrop-blur-sm text-white text-[10px] font-bold px-2 py-0.5 rounded shadow-sm">
                    {{ $product->variations->count() }}{{ $cardTrans['variants'] }}
                </span>
            @endif
        </div>

        {{-- Title --}}
        <h3 class="font-bold text-gray-800 text-base md:text-lg mt-4 line-clamp-2 leading-snug group-hover:text-[#0D2601] transition-colors">
            {{ $product->title }}
        </h3>
    </div>

    {{-- Bottom Area --}}
    <div class="mt-4 pt-4 border-t border-dashed border-gray-200">
        {{-- Variant Arrow Selector (Dropdown) --}}
        @if($hasVariations)
            <div class="mb-3">
                <label for="variant-select-{{ $product->id }}" class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide mb-1">
                    {{ $cardTrans['choose_option'] }}
                </label>
                <div class="relative">
                    <select id="variant-select-{{ $product->id }}"
                        aria-label="{{ $cardTrans['choose_option'] }} - {{ $product->title }}"
                        onchange="onCardVariationChange(this, {{ $product->id }})" 
                        class="card-variant-select w-full appearance-none bg-gray-50 hover:bg-gray-100 focus:bg-white border border-gray-300 focus:border-[#35B11E] rounded-lg px-3 py-2 pr-8 text-xs font-semibold text-gray-800 outline-none transition-colors cursor-pointer shadow-sm">
                        @foreach($product->variations as $idx => $v)
                            @php
                                $vDiscount = $v->discount ?? 0;
                                $vPrice = $v->regular_price - $vDiscount;
                                $vImg = $v->image ? (\Illuminate\Support\Facades\Storage::disk('r2')->url($v->image)) : $pImg;
                                // Only check available_stock if manage_stock is true
                                $vOutOfStock = ($v->stock_status === 'out_of_stock') || ($product->manage_stock && ($v->available_stock <= 0));
                                $isSelected = ($firstInStockVariationIndex !== null) ? ($idx === $firstInStockVariationIndex) : ($idx === 0);
                                $vDiscountLabel = '';
                                if ($v->regular_price > $vPrice && $v->regular_price > 0) {
                                    $vDiff = $v->regular_price - $vPrice;
                                    $vPerc = round(($vDiff / $v->regular_price) * 100);
                                    $vDiscountLabel = $vPerc > 0 ? ($vPerc . '%' . $cardTrans['off']) : (number_format($vDiff) . ($setup->currency ?? '৳') . $cardTrans['off']);
                                }
                            @endphp
                            <option value="{{ $v->id }}" 
                                data-price="{{ $vPrice }}" 
                                data-regular="{{ $v->regular_price }}"
                                data-discount-label="{{ $vDiscountLabel }}"
                                data-img="{{ $vImg }}"
                                data-name="{{ $v->display_name }}"
                                data-out-of-stock="{{ $vOutOfStock ? '1' : '0' }}"
                                {{ $vOutOfStock ? 'disabled' : '' }}
                                {{ $isSelected ? 'selected' : '' }}>
                                {{ $v->display_name }} ({{ number_format($vPrice, 2) }}{{ $setup->currency }}) {{ $vOutOfStock ? ('- ' . $cardTrans['out_of_stock']) : '' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500 text-xs">
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
            </div>
        @endif

        {{-- Pricing --}}
        <div class="flex items-baseline justify-between gap-2 mb-3">
            <div class="flex items-baseline gap-2">
                <span class="card-sale-price text-xl md:text-2xl font-black text-[#0D2601]">
                    {{ number_format($pSalePrice, 2) }}{{ $setup->currency }}
                </span>
                @if(!empty($pDiscount) && $pDiscount > 0 && $pRegularPrice > $pSalePrice)
                    <span class="card-reg-price text-xs text-gray-600 line-through">
                        {{ number_format($pRegularPrice, 2) }}{{ $setup->currency }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Order / Select Action Buttons --}}
        @if($initialOutOfStock)
            <button type="button" 
                disabled
                class="card-add-btn w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 bg-gray-400 cursor-not-allowed opacity-75 text-white font-bold text-sm rounded-lg shadow-sm">
                <i class="fa-solid fa-ban text-xs"></i>
                <span class="btn-label">{{ $cardTrans['out_of_stock'] }}</span>
            </button>
        @else
            <div class="card-btn-group grid grid-cols-2 gap-2">
                {{-- Add to Cart (stays on same place) --}}
                <button type="button" 
                    onclick="addProductToLandingOrder({{ $product->id }}, false)"
                    class="card-add-btn w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-2 bg-green-50 hover:bg-green-100 text-[#0D2601] border-2 border-[#35B11E]/40 hover:border-[#35B11E] font-bold text-xs rounded-lg transition-all active:scale-[0.98] cursor-pointer shadow-sm">
                    <i class="fa-solid fa-cart-plus text-xs text-[#35B11E]"></i>
                    <span class="btn-label">{{ $cardTrans['add_to_cart'] }}</span>
                </button>

                {{-- Direct Order (adds & scrolls to form) --}}
                <button type="button" 
                    onclick="addProductToLandingOrder({{ $product->id }}, true)"
                    class="card-order-btn w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-2 bg-[#0D2601] hover:bg-[#1a4a03] text-white font-bold text-xs rounded-lg shadow-md hover:shadow-lg transition-all active:scale-[0.98] cursor-pointer">
                    <span class="btn-label">{{ $cardTrans['order_now'] }}</span>
                    <i class="fa-solid fa-arrow-down text-xs"></i>
                </button>
            </div>
        @endif
    </div>
</div>
