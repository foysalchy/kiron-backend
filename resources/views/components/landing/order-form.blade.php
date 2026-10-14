@props(['landing', 'products' => null, 'isPreview' => null])
@php
    $allProducts = ($products && $products->isNotEmpty()) ? $products : collect([$landing->product]);
    $isMultiProductMode = $allProducts->count() > 1;
    $mainProduct = $landing->product ?? $allProducts->first();
    $defaultInsideCharge = $setup->inside_charge ?? 60;
    $defaultOutsideCharge = $setup->outside_charge ?? 100;
    $isFree = \App\Models\Product::where('id', $mainProduct->id)->value('is_free_delivery');
    $insideCharge = $isFree == 1 ? 0 : $defaultInsideCharge;
    $outsideCharge = $isFree == 1 ? 0 : $defaultOutsideCharge;
    $isPreviewMode = !empty($isPreview) || request()->is('*landing/preview*') || !empty($landing->is_preview);

    $isSingleProduct = $mainProduct->type === 'single';
    // stock check only if manage_stock is true or stock_status is out_of_stock
    $isSingleOutOfStock = $isSingleProduct && (
        $mainProduct->stock_status === 'out_of_stock' ||
        ($mainProduct->manage_stock && ($mainProduct->available_stock <= 0))
    );

    $firstAvailableVariationIndex = null;
    $allVariationsOutOfStock = true;
    $isMultipleAttribute = false;
    if ($mainProduct->type === 'variation') {
        foreach ($mainProduct->variations as $idx => $v) {
            $vOutOfStock = (
                $v->stock_status === 'out_of_stock' ||
                ($mainProduct->manage_stock && ($v->available_stock <= 0))
            );
            if (!$vOutOfStock) {
                $allVariationsOutOfStock = false;
                if ($firstAvailableVariationIndex === null) {
                    $firstAvailableVariationIndex = $idx;
                }
            }
        }

        // Check if any attribute group associated with these variations is 'Multiple'
        $variationIds = $mainProduct->variations->pluck('id')->filter()->toArray();
        if (!empty($variationIds)) {
            $isMultipleAttribute = \App\Models\AttributeGroup::withoutGlobalScopes()
                ->whereIn('id', function($q) use ($variationIds) {
                    $q->select('attribute_group_id')
                      ->from('product_variation_attributes')
                      ->whereIn('product_variation_id', $variationIds);
                })
                ->whereRaw('LOWER(category) = ?', ['multiple'])
                ->exists();
        }
    }
    $isEntirelyOutOfStock = $isSingleProduct ? $isSingleOutOfStock : $allVariationsOutOfStock;
@endphp

<div class="max-w-7xl mx-auto bg-white p-6 md:p-12 rounded-xl shadow-sm">
    <form @if($isPreviewMode) action="javascript:void(0);" onsubmit="event.preventDefault(); showPreviewAlert(); return false;" @else action="{{ route('landing.order.store') }}" method="POST" @endif id="landing-order-form">


        @csrf
        <input type="hidden" name="product_id" value="{{ $landing->product_id }}">
        <input type="hidden" name="landing_page_id" value="{{ $landing->id }}">
        <input type="hidden" name="draft_order_id" id="draft-order-id" value="">
        <input type="hidden" name="qty" id="input-qty" value="1">

        @if ($mainProduct->type === 'variation')
            <input type="hidden" name="variation_id" id="selected-variation-id">
        @endif

        {{-- Header --}}
        <div class="text-center mb-10">
            <h2 class="text-xl sm:text-2xl md:text-4xl font-bold mb-5 text-black leading-normal md:leading-relaxed">অর্ডার করতে সঠিক তথ্য দিয়ে</h2>
            <div
                class="inline-block bg-[#1f8a54] text-white px-10 py-3 rounded-lg text-xl md:text-2xl shadow-md font-bold">
                নিচের ফর্মটি পূরণ করুন</div>
        </div>

        {{-- Product Selection Area --}}
        <div class="space-y-4 mb-10">
            <div class="flex items-center justify-between">
                <label class="block text-lg font-bold text-gray-800">
                    পণ্যটি সিলেক্ট করুন <span class="text-red-500">*</span>
                </label>
                @if ($mainProduct->type === 'variation' && $isMultipleAttribute)
                    <span class="text-xs font-bold text-[#14532d] bg-[#dcfce7] px-3 py-1 rounded-full border border-[#86efac]">
                        একাধিক পণ্য সিলেক্ট করতে পারেন
                    </span>
                @endif
            </div>

            @if ($isMultiProductMode)
                {{-- Empty state prompt if nothing is added yet --}}
                <div id="multi-product-empty-notice" class="p-6 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl">
                    <div class="text-center mb-4">
                        <p class="text-base font-bold text-gray-800 mb-1">কোনো পণ্য এখনো কার্টে যোগ করা হয়নি</p>
                        <p class="text-sm text-gray-500 font-medium">উপরের প্রোডাক্ট কার্ড থেকে পছন্দের পণ্য বেছে নিয়ে কার্টে যোগ করুন।</p>
                    </div>
                    <div class="flex justify-center">
                        <a href="#landing-products-section"
                            onclick="const el = document.getElementById('landing-products-section') || document.querySelector('.product-card'); if (el) { el.scrollIntoView({ behavior: 'smooth' }); return false; }"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#15803d] hover:bg-[#166534] text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition-all active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-cart-plus text-sm"></i>
                            <span>পণ্য পছন্দ করুন</span>
                            <i class="fa-solid fa-arrow-up text-xs"></i>
                        </a>
                    </div>
                </div>

                {{-- MULTI-PRODUCT SELECTION LIST --}}
                <div class="space-y-6" id="multi-product-list-wrapper">
                    @foreach ($allProducts as $p)
                        @php
                            $pHasVars = ($p->type === 'variation') && ($p->variations && $p->variations->count() > 0);
                            $pIsSingleOutOfStock = ($p->type === 'single') && (
                                $p->stock_status === 'out_of_stock' ||
                                ($p->manage_stock && ($p->available_stock <= 0))
                            );
                            $pImg = $p->thumbnail_url ?? $p->display_image_url ?? asset('images/default.webp');
                            $pPrice = $p->sale_price ?? ($p->regular_price - ($p->discount ?? 0));
                        @endphp

                        <div class="order-product-item-wrapper border-2 border-gray-200 rounded-2xl p-4 bg-white shadow-sm space-y-3 hidden" data-order-product-id="{{ $p->id }}">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-gray-50 border border-gray-100 overflow-hidden flex-shrink-0">
                                        <img src="{{ $pImg }}" class="w-full h-full object-contain" alt="{{ $p->title }}">
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800 text-sm md:text-base leading-snug">{{ $p->title }}</h4>
                                        <span class="text-xs text-gray-500 font-semibold">মূল্য: {{ number_format($pPrice, 2) }}{{ $setup->currency }}</span>
                                    </div>
                                </div>
                            </div>

                            @if ($pHasVars)
                                {{-- Variations of this product --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    @foreach ($p->variations as $variation)
                                        @php
                                            $vSalePrice = $variation->regular_price - ($variation->discount ?? 0);
                                            $vImage = $variation->image ? (\Illuminate\Support\Facades\Storage::disk('r2')->url($variation->image)) : $pImg;
                                            $vOutOfStock = (
                                                $variation->stock_status === 'out_of_stock' ||
                                                ($p->manage_stock && ($variation->available_stock <= 0))
                                            );
                                            $maxStock = $p->manage_stock ? ($variation->available_stock ?? 0) : 999999;
                                        @endphp
                                        <label class="relative {{ $vOutOfStock ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }} block">
                                            <input type="checkbox" name="variation_ids[]" value="{{ $variation->id }}"
                                                data-price="{{ $vSalePrice }}"
                                                data-name="{{ $variation->display_name }}"
                                                data-title="{{ $p->title }}"
                                                data-img="{{ $vImage }}"
                                                data-is-free="{{ $p->is_free_delivery ? '1' : '0' }}"
                                                data-out-of-stock="{{ $vOutOfStock ? '1' : '0' }}"
                                                data-max-stock="{{ $maxStock }}"
                                                {{ $vOutOfStock ? 'disabled' : '' }}
                                                onchange="onVariationCheckboxChange()"
                                                class="hidden peer variant-checkbox">

                                            <div class="variant-card-box flex items-center justify-between gap-3 p-3 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 bg-white">
                                                <div class="flex items-center gap-3">
                                                    <div class="checkmark-box w-5 h-5 flex-shrink-0 rounded border-2 border-gray-300 bg-white flex items-center justify-center transition-colors">
                                                        <svg class="w-3.5 h-3.5 text-white hidden checkmark-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                        </svg>
                                                    </div>
                                                    <div class="w-10 h-10 rounded border border-gray-100 overflow-hidden flex-shrink-0 bg-white">
                                                        <img src="{{ $vImage }}" class="w-full h-full object-contain" alt="VAR">
                                                    </div>
                                                    <div class="text-left">
                                                        <p class="text-xs font-bold text-gray-800">{{ $variation->display_name }}</p>
                                                        @if($vOutOfStock)
                                                            <span class="text-[10px] text-red-600 font-bold">স্টক আউট</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="text-xs font-bold text-gray-900">{{ number_format($vSalePrice, 2) }}{{ $setup->currency }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                {{-- Single product item selection --}}
                                <label class="relative {{ $pIsSingleOutOfStock ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }} block">
                                    <input type="checkbox" name="product_ids[]" value="{{ $p->id }}"
                                        data-price="{{ $pPrice }}"
                                        data-name="Regular"
                                        data-title="{{ $p->title }}"
                                        data-img="{{ $pImg }}"
                                        data-is-product="1"
                                        data-is-free="{{ $p->is_free_delivery ? '1' : '0' }}"
                                        data-out-of-stock="{{ $pIsSingleOutOfStock ? '1' : '0' }}"
                                        data-max-stock="{{ $p->manage_stock ? ($p->available_stock ?? 0) : 999999 }}"
                                        {{ $pIsSingleOutOfStock ? 'disabled' : '' }}
                                        onchange="onVariationCheckboxChange()"
                                        class="hidden peer variant-checkbox">

                                    <div class="variant-card-box flex items-center justify-between gap-3 p-3 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 bg-white">
                                        <div class="flex items-center gap-3">
                                            <div class="checkmark-box w-5 h-5 flex-shrink-0 rounded border-2 border-gray-300 bg-white flex items-center justify-center transition-colors">
                                                <svg class="w-3.5 h-3.5 text-white hidden checkmark-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            </div>
                                            <div class="text-left">
                                                <p class="text-xs font-bold text-gray-800">এই প্রোডাক্টটি কার্টে যোগ করুন</p>
                                                @if($pIsSingleOutOfStock)
                                                    <span class="text-[10px] text-red-600 font-bold">স্টক আউট</span>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold text-gray-900">{{ number_format($pPrice, 2) }}{{ $setup->currency }}</span>
                                    </div>
                                </label>
                            @endif
                        </div>
                    @endforeach
                </div>
            @elseif ($mainProduct->type === 'variation')
                @foreach ($mainProduct->variations as $index => $variation)
                    @php
                        $vSalePrice = $variation->regular_price - $variation->discount;
                        $vImage = $variation->image_url ?? $mainProduct->thumbnail_url ?? asset('images/template1/frontend/default.webp');
                        $vOutOfStock = (
                            $variation->stock_status === 'out_of_stock' ||
                            ($mainProduct->manage_stock && ($variation->available_stock <= 0))
                        );
                        $isChecked = ($firstAvailableVariationIndex !== null)
                            ? ($index === $firstAvailableVariationIndex)
                            : ($index === 0);
                        $maxStock = $mainProduct->manage_stock
                            ? ($variation->available_stock ?? 0)
                            : 999999;
                    @endphp
                    <label class="relative {{ $vOutOfStock ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }} block">
                        @if ($isMultipleAttribute)
                            <input type="checkbox" name="variation_ids[]" value="{{ $variation->id }}" {{ $isChecked ? 'checked' : '' }}
                                data-price="{{ $vSalePrice }}"
                                data-name="{{ $variation->display_name }}"
                                data-img="{{ $vImage }}"
                                data-is-free="{{ $mainProduct->is_free_delivery ? '1' : '0' }}"
                                data-out-of-stock="{{ $vOutOfStock ? '1' : '0' }}"
                                data-max-stock="{{ $maxStock }}"
                                {{ $vOutOfStock ? 'disabled' : '' }}
                                onchange="onVariationCheckboxChange()"
                                class="hidden peer variant-checkbox">
                        @else
                            <input type="radio" name="variant_radio" value="{{ $variation->id }}" {{ $isChecked ? 'checked' : '' }}
                                data-price="{{ $vSalePrice }}"
                                data-name="{{ $variation->display_name }}"
                                data-img="{{ $vImage }}"
                                data-is-free="{{ $mainProduct->is_free_delivery ? '1' : '0' }}"
                                data-out-of-stock="{{ $vOutOfStock ? '1' : '0' }}"
                                data-max-stock="{{ $maxStock }}"
                                {{ $vOutOfStock ? 'disabled' : '' }}
                                onchange="updateVariation('{{ $variation->id }}', {{ $vSalePrice }}, '{{ $variation->display_name }}', '{{ $vImage }}', {{ $vOutOfStock ? 'true' : 'false' }}, {{ $maxStock }})"
                                class="hidden peer">
                        @endif

                        <div
                            class="variant-card-box flex flex-col md:flex-row items-center justify-between gap-4 p-4 border-2 border-gray-200 rounded-xl transition-all {{ !$vOutOfStock ? 'hover:border-gray-300' : 'bg-gray-50 border-gray-200' }} bg-white">
                            <div class="flex items-center gap-4 w-full">
                                @if ($isMultipleAttribute)
                                    <div class="checkmark-box w-5 h-5 flex-shrink-0 rounded border-2 border-gray-300 bg-white flex items-center justify-center transition-colors">
                                        <svg class="w-3.5 h-3.5 text-white {{ $isChecked ? '' : 'hidden' }} checkmark-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="w-16 h-16 bg-white rounded-lg overflow-hidden flex-shrink-0 border border-gray-100">
                                    <img src="{{ $vImage }}" width="64" height="64" class="w-full h-full object-contain"
                                        alt="PRODUCT">
                                </div>
                                <div class="flex-grow text-left">
                                    <p class="text-sm md:text-md font-bold text-gray-800">{{ $mainProduct->title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <p class="text-xs text-[#145a32] font-bold uppercase">Option:
                                            {{ $variation->display_name }}</p>
                                        @if($vOutOfStock)
                                            <span class="bg-red-100 text-red-700 text-[11px] font-bold px-2 py-0.5 rounded-full border border-red-200">স্টক আউট</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 flex-shrink-0">
                                <span
                                    class="text-lg font-bold text-gray-900">{{ number_format($vSalePrice, 2) }}{{ $setup->currency }}</span>
                            </div>
                        </div>
                    </label>
                @endforeach
            @else
                <div
                    class="border-2 {{ $isSingleOutOfStock ? 'border-red-300 bg-red-50/50' : 'border-[#1f8a54] bg-green-50' }} rounded-xl p-4 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4 w-full">
                        <div class="w-16 h-16 bg-white rounded-lg overflow-hidden flex-shrink-0 border border-gray-100">
                            <img src="{{ $landing->product->thumbnail_url ?? asset('images/default.webp') }}" width="64"
                                height="64" class="w-full h-full object-contain" alt="THUMBNAIL">
                        </div>
                        <div class="text-left">
                            <p class="text-md font-bold text-gray-800">{{ $landing->product->title }}</p>
                            @if($isSingleOutOfStock)
                                <span class="inline-block mt-1 bg-red-100 text-red-700 text-xs font-bold px-2.5 py-0.5 rounded-full border border-red-200">স্টক আউট (বর্তমানে পণ্যটি স্টকে নেই)</span>
                            @endif
                        </div>
                    </div>
                    <span
                        class="text-lg font-bold text-gray-900">{{ number_format($mainProduct->sale_price, 2) }}{{ $setup->currency }}</span>
                </div>
            @endif
        </div>

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

            {{-- LEFT: Billing Info --}}
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <label for="name" class="block text-md font-bold mb-2 text-gray-800">আপনার নাম লিখুন <span
                            class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" required
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 bg-white focus:border-[#1f8a54] outline-none">
                </div>
                <div>
                    <label for="phone" class="block text-md font-bold mb-2 text-gray-800">মোবাইল নাম্বার লিখুন <span
                            class="text-red-500">*</span></label>
                    <input type="tel" id="phone" name="phone" required
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 bg-white focus:border-[#1f8a54] outline-none">
                </div>
                <div>
                    <label for="address" class="block text-md font-bold mb-2 text-gray-800">সম্পূর্ণ ঠিকানা লিখুন <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="address" name="address" required
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 bg-white focus:border-[#1f8a54] outline-none">
                </div>
            </div>

            {{-- RIGHT: Order Summary with Shipping Area --}}
            <div class="lg:col-span-5">
                <div class="bg-white p-2 sticky top-8 ">

                    {{-- Table Header --}}
                    <div
                        class="flex justify-between text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 pb-3 border-b border-dashed border-gray-200">
                        <span>Product</span><span class="text-right">Price</span>
                    </div>

                    {{-- Product Info Rows Container (Dynamic) --}}
                    <div id="summary-items-list" class="space-y-3 pb-5 mb-5 border-b border-dashed border-gray-200">
                        <div class="flex justify-between items-start gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-14 h-14 rounded-lg overflow-hidden flex-shrink-0 border border-gray-100 bg-gray-50">
                                    <img id="summary-img" src="{{ $mainProduct->thumbnail_url }}" width="56" height="56"
                                        alt="{{ $landing->product->title ?? 'Product Image' }}"
                                        class="w-full h-full object-contain">
                                </div>
                                <div class="max-w-[150px]">
                                    <p class="text-xs font-bold text-gray-800 leading-tight">{{ $mainProduct->title }}</p>
                                    <p id="summary-variant-name"
                                        class="text-[10px] text-green-800 font-bold mt-1 uppercase"></p>
                                </div>
                            </div>
                            <div class="text-right whitespace-nowrap pt-1">
                                <span class="text-sm font-bold text-gray-800">
                                    <span class="text-gray-400">×</span> <span id="summary-qty">1</span>
                                    <span id="summary-unit-price" class="ml-1"></span>{{ $setup->currency }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Qty Selector & Shipping Area (Inside Summary) --}}
                    <div class="px-2 space-y-4 mb-6">
                        <div id="global-qty-wrapper" class="flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-700">Quantity</span>
                            <div class="flex items-center border border-gray-300 rounded bg-white overflow-hidden">
                                <button type="button" onclick="changeQty(-1)" aria-label="Decrease quantity"
                                    class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 border-r border-gray-300 font-bold cursor-pointer">−</button>
                                <span id="qty-display" class="w-10 text-center font-bold text-sm">1</span>
                                <button type="button" onclick="changeQty(1)" aria-label="Increase quantity"
                                    class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 border-l border-gray-300 font-bold cursor-pointer">+</button>
                            </div>
                        </div>

                        <div id="delivery-area-section" class="space-y-2 {{ $isFree ? 'hidden' : '' }}">
                            <label class="block text-sm font-bold text-gray-500 uppercase tracking-wide">Delivery
                                Area</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="shipping_area" id="shipping-area-inside" value="inside" checked
                                        onchange="updateDeliveryCharge({{ $defaultInsideCharge }})" class="hidden peer">
                                    <div
                                        class="p-2 border-2 border-gray-100 rounded-lg text-center text-sm font-semibold text-gray-600 peer-checked:border-[#1f8a54] peer-checked:text-[#145a32] bg-gray-50">
                                        Inside Dhaka <span class="text-xs font-bold ml-1">({{ $defaultInsideCharge.$setup->currency }})</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="shipping_area" id="shipping-area-outside" value="outside"
                                        onchange="updateDeliveryCharge({{ $defaultOutsideCharge }})" class="hidden peer">
                                    <div
                                        class="p-2 border-2 border-gray-100 rounded-lg text-center text-sm font-semibold text-gray-600 peer-checked:border-[#1f8a54] peer-checked:text-[#145a32] bg-gray-50">
                                        Outside Dhaka <span class="text-xs font-bold ml-1">({{ $defaultOutsideCharge.$setup->currency }})</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Calculations --}}
                    <div class="px-2 space-y-2 border-t border-dashed border-gray-100 pt-4 mb-6">
                        <div class="flex justify-between text-sm md:text-base text-gray-800">
                            <span>Subtotal</span>
                            <span class="font-bold text-gray-800"><span
                                    id="summary-subtotal"></span>{{ $setup->currency }}</span>
                        </div>
                        <div class="flex justify-between text-sm md:text-base text-gray-800">
                            <span>Shipping Charge</span>
                            <span class="font-bold text-red-600" id="delivery-charge-display-wrapper">
                                +<span id="delivery-charge-display"></span>{{ $setup->currency }}
                            </span>
                        </div>
                        <div
                            class="flex justify-between items-center text-lg font-bold text-gray-900 pt-2 border-t border-gray-50">
                            <span>Total</span>
                            <span id="summary-total" class="text-2xl font-black"></span>
                        </div>
                    </div>

                    <button type="{{ $isPreviewMode ? 'button' : 'submit' }}"
                        id="submit-order-btn"
                        {{ $isEntirelyOutOfStock ? 'disabled' : '' }}
                        @if($isPreviewMode) onclick="showPreviewAlert()" @endif
                        class="w-full {{ $isEntirelyOutOfStock ? 'bg-gray-400 cursor-not-allowed opacity-75' : 'bg-[#1f8a54] hover:bg-[#176840] cursor-pointer' }} text-white font-bold py-5 rounded-xl text-2xl active:translate-y-1 transition-all flex items-center justify-center gap-2">
                        @if($isEntirelyOutOfStock)
                            <span>স্টক আউট (পণ্যটি শেষ)</span>
                        @else
                            <span id="btn-text">অর্ডার করুন</span> <span id="btn-total"></span>{{ $setup->currency }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Order Error Modal --}}
<div id="order-error-modal" class="fixed inset-0 z-[999999] {{ session('error') ? 'flex' : 'hidden' }} items-center justify-center bg-black/60 backdrop-blur-xs p-4" onclick="if(event.target === this) closeOrderErrorModal()">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 md:p-8 text-center transform transition-all border border-gray-100 animate-in fade-in zoom-in duration-200">
        <!-- Icon -->
        <div class="w-16 h-16 mx-auto mb-4 bg-red-100 text-red-600 rounded-full flex items-center justify-center shadow-inner">
            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 7.5h.008v.008H12v-.008z" />
            </svg>
        </div>
        <!-- Title -->
        <h3 class="text-2xl font-bold text-gray-900 mb-2">দুঃখিত! অর্ডার সম্পন্ন হয়নি</h3>
        <!-- Error message -->
        <div id="order-error-modal-message" class="bg-red-50 text-red-700 text-sm md:text-base font-medium rounded-xl p-3.5 mb-6 border border-red-200/60 leading-relaxed">
            {{ session('error') ?? 'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে।' }}
        </div>
        <!-- Action Button -->
        <button type="button" onclick="closeOrderErrorModal()" class="w-full py-3.5 px-6 bg-[#1f8a54] hover:bg-[#176840] text-white font-bold text-lg rounded-xl transition-all shadow-lg hover:shadow-xl cursor-pointer active:scale-98">
            আবার চেষ্টা করুন
        </button>
    </div>
</div>

{{-- Order Success Modal --}}
<div id="order-success-modal" class="fixed inset-0 z-[999999] {{ session('success') ? 'flex' : 'hidden' }} items-center justify-center bg-black/60 backdrop-blur-xs p-4" onclick="if(event.target === this) closeOrderSuccessModal()">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 md:p-8 text-center transform transition-all border border-gray-100 animate-in fade-in zoom-in duration-200">
        <!-- Icon -->
        <div class="w-16 h-16 mx-auto mb-4 bg-green-100 text-green-600 rounded-full flex items-center justify-center shadow-inner">
            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>
        <!-- Title -->
        <h3 class="text-2xl font-bold text-gray-900 mb-1">অভিনন্দন! আপনার অর্ডার সফল হয়েছে</h3>
        <p id="order-success-modal-code" class="text-sm font-bold text-[#1f8a54] mb-3 {{ session('order_code') ? '' : 'hidden' }}">অর্ডার আইডি: <span>{{ session('order_code') }}</span></p>
        <!-- Success message -->
        <div id="order-success-modal-message" class="bg-green-50 text-green-800 text-sm md:text-base font-medium rounded-xl p-3.5 mb-6 border border-green-200/60 leading-relaxed">
            {{ session('success') ?? 'কিছুক্ষণের মধ্যে আমাদের প্রতিনিধি আপনার সাথে ফোনে যোগাযোগ করে অর্ডারটি কনফার্ম করবে।' }}
        </div>
        <!-- Action Button -->
        <button type="button" onclick="closeOrderSuccessModal()" class="w-full py-3.5 px-6 bg-[#1f8a54] hover:bg-[#176840] text-white font-bold text-lg rounded-xl transition-all shadow-lg hover:shadow-xl cursor-pointer active:scale-98">
            ঠিক আছে
        </button>
    </div>
</div>

@if($isPreviewMode)
    {{-- Preview Alert Modal --}}
    <div id="preview-alert-modal" class="fixed inset-0 z-[999999] hidden items-center justify-center bg-black/60 p-4" onclick="if(event.target === this) closePreviewAlert()">
        <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 text-center transform transition-all border border-gray-100">
            <div class="w-14 h-14 mx-auto mb-4 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Preview Mode</h3>
            <p class="text-gray-600 text-sm leading-relaxed mb-6">
                This is a landing page preview. Order submission is disabled in preview mode.
            </p>
            <button type="button" onclick="closePreviewAlert()" class="w-full py-3 px-4 bg-[#1f8a54] hover:bg-[#176840] text-white font-bold rounded-xl transition-all shadow-md cursor-pointer">
                Got it
            </button>
        </div>
    </div>
@endif

@if($isMultiProductMode)
    {{-- Floating Cart Pill / Button for Multi-Product Mode --}}
    <div id="landing-floating-cart" 
        onclick="const el = document.getElementById('order'); if (el) el.scrollIntoView({ behavior: 'smooth' });"
        class="fixed bottom-6 right-5 z-[999] hidden items-center gap-3 bg-[#0D2601] hover:bg-[#1a4a03] text-white px-4 py-3 rounded-full shadow-2xl border-2 border-white/20 cursor-pointer transition-all duration-300 transform hover:scale-105 active:scale-95 group">
        <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-[#35B11E] text-white">
            <i class="fa-solid fa-cart-shopping text-sm"></i>
            <span id="floating-cart-count" class="absolute -top-1.5 -right-1.5 bg-red-600 text-white text-[10px] font-black rounded-full h-4 min-w-[16px] px-1 flex items-center justify-center border border-white">0</span>
        </div>
        <div class="text-left pr-1">
            <p class="text-[10px] uppercase font-bold text-gray-300 tracking-wider leading-none">কার্ট দেখুন</p>
            <p class="text-sm font-extrabold text-[#7dfa6b] leading-tight mt-0.5">
                <span id="floating-cart-total">0.00</span>{{ $setup->currency }}
            </p>
        </div>
        <i class="fa-solid fa-chevron-down text-xs text-gray-300 group-hover:translate-y-0.5 transition-transform"></i>
    </div>
@endif

<script>
    const DEFAULT_INSIDE_CHARGE = parseFloat("{{ $defaultInsideCharge }}") || 60;
    const DEFAULT_OUTSIDE_CHARGE = parseFloat("{{ $defaultOutsideCharge }}") || 100;
    let UNIT_PRICE = parseFloat("{{ $mainProduct->type === 'single' ? $mainProduct->sale_price : 0 }}") || 0;
    let DELIVERY = parseFloat("{{ $insideCharge }}");
    const CURRENCY = "{{ $setup->currency }}";
    let qty = 1;
    const isVarProduct = {{ $mainProduct->type === 'variation' ? 'true' : 'false' }};
    const isMultipleAttribute = {{ ($mainProduct->type === 'variation' && $isMultipleAttribute) ? 'true' : 'false' }};
    const manageStockEnabled = {{ $mainProduct->manage_stock ? 'true' : 'false' }};
    let currentVariationOutOfStock = {{ $isEntirelyOutOfStock ? 'true' : 'false' }};
    let currentMaxStock = {{ ($isSingleProduct && $mainProduct->manage_stock && $mainProduct->available_stock !== null) ? $mainProduct->available_stock : 999999 }};

    const isMultiProductMode = {{ $isMultiProductMode ? 'true' : 'false' }};

    document.addEventListener("DOMContentLoaded", function () {
        if (isMultiProductMode) {
            onVariationCheckboxChange();
        } else if (isVarProduct) {
            if (isMultipleAttribute) {
                onVariationCheckboxChange();
            } else {
                const firstRadio = document.querySelector('input[name="variant_radio"]:checked') || document.querySelector('input[name="variant_radio"]');
                if (firstRadio) firstRadio.dispatchEvent(new Event('change'));
            }
        } else {
            updateUI();
        }
    });

    const PRODUCT_TITLE = @json($mainProduct->title);
    const PRODUCT_THUMB = @json($mainProduct->thumbnail_url);

    // Map to store per-variation quantities: { [variationId]: quantity }
    let variationQuantities = {};

    function renderSummaryItems(items, isMulti = false) {
        const container = document.getElementById('summary-items-list');
        const globalQtyWrapper = document.getElementById('global-qty-wrapper');
        if (!container) return;

        // Toggle global quantity visibility
        if (globalQtyWrapper) {
            if (isMulti) {
                globalQtyWrapper.classList.add('hidden');
            } else {
                globalQtyWrapper.classList.remove('hidden');
            }
        }

        if (!items || items.length === 0) {
            container.innerHTML = `
                <div class="py-3 text-center text-xs text-gray-500 font-medium bg-gray-50 rounded-lg border border-dashed border-gray-200">
                    কোনো ভ্যারিয়েন্ট সিলেক্ট করা হয়নি
                </div>`;
            return;
        }

        let html = '';
        items.forEach(item => {
            const itemKey = item.storageKey || item.id;
            const itemQty = isMulti ? (variationQuantities[itemKey] || 1) : qty;
            const price = parseFloat(item.price) || 0;
            const lineTotalFormatted = (price * itemQty).toLocaleString(undefined, { minimumFractionDigits: 2 });

            html += `
                <div class="flex justify-between items-center gap-3 py-2.5 border-b border-gray-100 last:border-0" data-item-id="${item.id}">
                    <div class="flex items-center gap-2.5 flex-1 min-w-0">
                        <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 border border-gray-200 bg-white p-0.5">
                            <img src="${item.img || PRODUCT_THUMB}" width="48" height="48"
                                alt="${item.title || PRODUCT_TITLE}"
                                class="w-full h-full object-contain">
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-gray-800 truncate">${item.title || PRODUCT_TITLE}</p>
                            ${item.option ? `<p class="text-[11px] text-[#145a32] font-semibold mt-0.5 uppercase">${item.option}</p>` : ''}
                            <p class="text-[11px] text-gray-500 font-medium">${price.toFixed(2)}${CURRENCY}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-shrink-0">
                        ${isMulti ? `
                            <div class="flex items-center border border-gray-300 rounded bg-white overflow-hidden shadow-sm">
                                <button type="button" onclick="changeItemQty('${itemKey}', -1, ${item.maxStock})"
                                    class="w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-gray-100 border-r border-gray-300 cursor-pointer text-gray-600">−</button>
                                <span class="w-7 text-center font-bold text-xs text-gray-800">${itemQty}</span>
                                <button type="button" onclick="changeItemQty('${itemKey}', 1, ${item.maxStock})"
                                    class="w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-gray-100 border-l border-gray-300 cursor-pointer text-gray-600">+</button>
                            </div>
                            <button type="button" onclick="removeVariationItem('${item.id}', ${item.isProduct ? 'true' : 'false'})"
                                class="w-6 h-6 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 transition-colors cursor-pointer"
                                title="রিমুভ করুন">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            ${item.isProduct
                                ? `<input type="hidden" name="product_quantities[${item.id}]" value="${itemQty}">`
                                : `<input type="hidden" name="variation_quantities[${item.id}]" value="${itemQty}">`
                            }
                        ` : `
                            <span class="text-xs font-bold text-gray-700">
                                <span class="text-gray-400">×</span> <span class="item-summary-qty">${qty}</span>
                            </span>
                        `}
                        <div class="text-right w-16">
                            <span class="text-xs font-bold text-gray-900">${lineTotalFormatted}${CURRENCY}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    function changeItemQty(storageKey, delta, maxStock = 999999) {
        const current = variationQuantities[storageKey] || 1;
        const newQty = current + delta;

        if (newQty < 1) return;
        if (manageStockEnabled && maxStock !== null && newQty > maxStock) {
            alert('দুঃখিত! এই পণ্যের সর্বোচ্চ ' + maxStock + ' টি স্টকে রয়েছে।');
            return;
        }

        variationQuantities[storageKey] = newQty;
        onVariationCheckboxChange();
    }

    function removeVariationItem(id, isProduct = false) {
        const selector = isProduct
            ? `input[name="product_ids[]"][value="${id}"]`
            : `.variant-checkbox[value="${id}"]`;
        const cb = document.querySelector(selector);
        if (cb) {
            cb.checked = false;
            const storageKey = isProduct ? ('prod_' + id) : id;
            delete variationQuantities[storageKey];
            onVariationCheckboxChange();
        }
    }

    function onVariationCheckboxChange() {
        const checkboxes = Array.from(document.querySelectorAll('.variant-checkbox'));
        const checkedList = checkboxes.filter(cb => cb.checked);

        // Update visual checkmark icons and box highlights
        checkboxes.forEach(cb => {
            const label = cb.closest('label');
            if (label) {
                const box = label.querySelector('.checkmark-box');
                const card = label.querySelector('.variant-card-box');
                const icon = label.querySelector('.checkmark-icon');

                if (cb.checked) {
                    if (box) {
                        box.classList.remove('bg-white', 'border-gray-300');
                        box.classList.add('bg-[#1f8a54]', 'border-[#1f8a54]');
                    }
                    if (card) {
                        card.classList.remove('border-gray-200', 'bg-white');
                        card.classList.add('border-[#1f8a54]', 'bg-green-50/70');
                    }
                    if (icon) {
                        icon.classList.remove('hidden');
                    }
                } else {
                    if (box) {
                        box.classList.remove('bg-[#1f8a54]', 'border-[#1f8a54]');
                        box.classList.add('bg-white', 'border-gray-300');
                    }
                    if (card) {
                        card.classList.remove('border-[#1f8a54]', 'bg-green-50/70');
                        card.classList.add('border-gray-200', 'bg-white');
                    }
                    if (icon) {
                        icon.classList.add('hidden');
                    }
                }
            }
        });

        // Show/hide product cards in form based on whether any checkbox in that product is checked
        let anyProductVisible = false;
        document.querySelectorAll('.order-product-item-wrapper').forEach(wrapper => {
            const hasChecked = wrapper.querySelector('.variant-checkbox:checked');
            if (hasChecked) {
                wrapper.classList.remove('hidden');
                anyProductVisible = true;
            } else {
                wrapper.classList.add('hidden');
            }
        });

        const emptyNotice = document.getElementById('multi-product-empty-notice');
        if (emptyNotice) {
            if (anyProductVisible) {
                emptyNotice.classList.add('hidden');
            } else {
                emptyNotice.classList.remove('hidden');
            }
        }

        const selectedIds = checkedList
            .filter(cb => !cb.dataset.isProduct)
            .map(cb => cb.value);

        const selectedIdInput = document.getElementById('selected-variation-id');
        if (selectedIdInput) {
            selectedIdInput.value = selectedIds.join(',');
        }

        // Update Floating Cart Widget
        const floatingCart = document.getElementById('landing-floating-cart');
        const floatingCount = document.getElementById('floating-cart-count');
        const floatingTotal = document.getElementById('floating-cart-total');

        // Update hero section order button visibility if present
        const heroOrderBtn = document.getElementById('hero-order-btn-wrapper');

        if (checkedList.length === 0) {
            UNIT_PRICE = 0;
            currentVariationOutOfStock = false;
            updateBtnState(false, true);
            renderSummaryItems([], true);
            updateUI(0);
            if (floatingCart) {
                floatingCart.classList.add('hidden');
                floatingCart.classList.remove('flex');
            }
            if (heroOrderBtn) {
                heroOrderBtn.classList.add('hidden');
            }
            return;
        }

        let calculatedSubtotal = 0;
        let anyOutOfStock = false;
        let items = [];
        let totalItemsCount = 0;

        checkedList.forEach(cb => {
            const itemId = cb.value;
            const isProd = cb.dataset.isProduct === '1';
            const storageKey = isProd ? ('prod_' + itemId) : itemId;

            if (!variationQuantities[storageKey]) {
                variationQuantities[storageKey] = 1;
            }
            const p = parseFloat(cb.dataset.price) || 0;
            const stock = parseInt(cb.dataset.maxStock) || 999999;
            const itemQty = variationQuantities[storageKey];

            calculatedSubtotal += (p * itemQty);
            totalItemsCount += itemQty;
            if (cb.dataset.outOfStock === '1') anyOutOfStock = true;

            items.push({
                id: itemId,
                storageKey: storageKey,
                isProduct: isProd,
                title: cb.dataset.title || PRODUCT_TITLE,
                option: isProd ? '' : cb.dataset.name,
                img: cb.dataset.img || PRODUCT_THUMB,
                price: p,
                maxStock: stock
            });
        });

        // Check if all selected products have free delivery
        let allFreeDelivery = checkedList.length > 0 && checkedList.every(cb => cb.dataset.isFree === '1');
        const deliveryAreaSection = document.getElementById('delivery-area-section');

        if (allFreeDelivery) {
            DELIVERY = 0;
            if (deliveryAreaSection) deliveryAreaSection.classList.add('hidden');
        } else {
            const currentArea = document.querySelector('input[name="shipping_area"]:checked')?.value || 'inside';
            DELIVERY = (currentArea === 'outside') ? DEFAULT_OUTSIDE_CHARGE : DEFAULT_INSIDE_CHARGE;
            if (deliveryAreaSection) deliveryAreaSection.classList.remove('hidden');
        }

        currentVariationOutOfStock = anyOutOfStock;
        renderSummaryItems(items, true);
        updateBtnState(currentVariationOutOfStock, false);
        updateUI(calculatedSubtotal);

        // Update floating cart UI
        if (floatingCart) {
            if (totalItemsCount > 0) {
                if (floatingCount) floatingCount.innerText = totalItemsCount;
                if (floatingTotal) floatingTotal.innerText = calculatedSubtotal.toLocaleString(undefined, { minimumFractionDigits: 2 });
                floatingCart.classList.remove('hidden');
                floatingCart.classList.add('flex');
            } else {
                floatingCart.classList.add('hidden');
                floatingCart.classList.remove('flex');
            }
        }

        // Show hero order button if items are in cart
        if (heroOrderBtn) {
            if (totalItemsCount > 0) {
                heroOrderBtn.classList.remove('hidden');
            } else {
                heroOrderBtn.classList.add('hidden');
            }
        }
    }

    function updateBtnState(isOutOfStock, isEmptySelection = false) {
        const btn = document.getElementById('submit-order-btn');
        if (!btn || isPreview) return;

        if (isOutOfStock) {
            btn.disabled = true;
            btn.className = "w-full bg-gray-400 cursor-not-allowed opacity-75 text-white font-bold py-5 rounded-xl text-2xl flex items-center justify-center gap-2";
            btn.innerHTML = '<span>স্টক আউট (পণ্যটি শেষ)</span>';
        } else if (isEmptySelection) {
            btn.disabled = true;
            btn.className = "w-full bg-gray-400 cursor-not-allowed opacity-75 text-white font-bold py-5 rounded-xl text-2xl flex items-center justify-center gap-2";
            btn.innerHTML = '<span>পণ্য নির্বাচন করুন</span>';
        } else {
            btn.disabled = false;
            btn.className = "w-full bg-[#1f8a54] hover:bg-[#176840] cursor-pointer text-white font-bold py-5 rounded-xl text-2xl active:translate-y-1 transition-all flex items-center justify-center gap-2";
            btn.innerHTML = '<span id="btn-text">অর্ডার করুন</span> <span id="btn-total"></span>' + CURRENCY;
        }
    }

    function updateVariation(id, price, name, img, isOutOfStock = false, maxStock = 999999) {
        const selectedIdInput = document.getElementById('selected-variation-id');
        if (selectedIdInput) selectedIdInput.value = id;
        UNIT_PRICE = parseFloat(price);

        currentVariationOutOfStock = isOutOfStock;
        currentMaxStock = maxStock;

        renderSummaryItems([{
            id: id,
            title: PRODUCT_TITLE,
            option: name,
            img: img || PRODUCT_THUMB,
            price: UNIT_PRICE,
            maxStock: maxStock
        }], false);

        updateBtnState(isOutOfStock, false);
        updateUI();
    }

    function updateDeliveryCharge(amount) {
        DELIVERY = parseFloat(amount);
        if (isMultiProductMode || isMultipleAttribute) {
            onVariationCheckboxChange();
        } else {
            updateUI();
        }
    }

    function changeQty(delta) {
        if (delta > 0 && manageStockEnabled && currentMaxStock !== null && (qty + delta) > currentMaxStock) {
            alert('দুঃখিত! স্টকে আর পণ্য নেই (সর্বোচ্চ ' + currentMaxStock + ' টি প্রযোজ্য)।');
            return;
        }
        qty = Math.max(1, qty + delta);
        updateUI();
    }

    function updateUI(explicitSubtotal = null) {
        const sub = explicitSubtotal !== null ? explicitSubtotal : (qty * UNIT_PRICE);
        const total = sub + DELIVERY;

        const elQtyDisplay = document.getElementById('qty-display');
        if (elQtyDisplay) elQtyDisplay.innerText = qty;

        const elInputQty = document.getElementById('input-qty');
        if (elInputQty) elInputQty.value = qty;

        const elSummaryQty = document.getElementById('summary-qty');
        if (elSummaryQty) elSummaryQty.innerText = qty;

        document.querySelectorAll('.item-summary-qty').forEach(el => {
            el.innerText = qty;
        });

        const unitFormatted = UNIT_PRICE.toLocaleString(undefined, { minimumFractionDigits: 2 });
        const subFormatted = sub.toLocaleString(undefined, { minimumFractionDigits: 2 });
        const totalFormatted = total.toLocaleString(undefined, { minimumFractionDigits: 2 });

        const elUnitPrice = document.getElementById('summary-unit-price');
        if (elUnitPrice) elUnitPrice.innerText = unitFormatted;

        const elSubtotal = document.getElementById('summary-subtotal');
        if (elSubtotal) elSubtotal.innerText = subFormatted;

        const elDeliveryWrapper = document.getElementById('delivery-charge-display-wrapper');
        if (elDeliveryWrapper) {
            if (DELIVERY === 0) {
                elDeliveryWrapper.innerText = 'Free';
            } else {
                elDeliveryWrapper.innerHTML = '+' + DELIVERY.toFixed(2) + CURRENCY;
            }
        }

        const elSummaryTotal = document.getElementById('summary-total');
        if (elSummaryTotal) elSummaryTotal.innerText = totalFormatted + CURRENCY;

        const elBtnTotal = document.getElementById('btn-total');
        if (elBtnTotal) elBtnTotal.innerText = totalFormatted;

        if (typeof window.saveLandingDraft === 'function') {
            window.saveLandingDraft();
        }
    }

    const isPreview = {{ $isPreviewMode ? 'true' : 'false' }};

    function showPreviewAlert() {
        const modal = document.getElementById('preview-alert-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            return;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Preview Mode',
                text: 'This is a landing page preview. Order submission is disabled in preview mode.',
                icon: 'info',
                confirmButtonText: 'OK',
                confirmButtonColor: '#1f8a54'
            });
        } else {
            alert('This is a landing page preview. Order submission is disabled in preview mode.');
        }
    }

    function closePreviewAlert() {
        const modal = document.getElementById('preview-alert-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    let latestThankyouUrl = "{{ session('order_id') ? route('order.thankyou', session('order_id')) : '' }}";

    function showOrderSuccessModal(message, orderCode, thankyouUrl) {
        if (thankyouUrl) {
            latestThankyouUrl = thankyouUrl;
        }
        const modal = document.getElementById('order-success-modal');
        if (modal) {
            const msgEl = document.getElementById('order-success-modal-message');
            if (msgEl && message) msgEl.innerText = message;
            const codeEl = document.getElementById('order-success-modal-code');
            if (codeEl) {
                if (orderCode) {
                    const span = codeEl.querySelector('span');
                    if (span) {
                        span.innerText = orderCode;
                    } else {
                        codeEl.innerText = 'অর্ডার আইডি: ' + orderCode;
                    }
                    codeEl.classList.remove('hidden');
                } else {
                    codeEl.classList.add('hidden');
                }
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function showOrderErrorModal(message) {
        const modal = document.getElementById('order-error-modal');
        if (modal) {
            const msgEl = document.getElementById('order-error-modal-message');
            if (msgEl && message) msgEl.innerText = message;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeOrderErrorModal() {
        const modal = document.getElementById('order-error-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        // স্ক্রল করে অর্ডার ফর্মে নিয়ে যাওয়া যাতে কাস্টমার তথ্য দেখে কারেকশন করতে পারে
        const orderSection = document.getElementById('order') || document.getElementById('landing-order-form');
        if (orderSection) {
            orderSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function closeOrderSuccessModal() {
        if (latestThankyouUrl) {
            window.location.href = latestThankyouUrl;
            return;
        }
        const modal = document.getElementById('order-success-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // Partial order draft save on phone number entry
    let isSubmittingOrder = false;

    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.getElementById('phone');
        const nameInput = document.getElementById('name');
        const addressInput = document.getElementById('address');
        const form = document.getElementById('landing-order-form');

        let draftDebounceTimer = null;

        window.saveLandingDraft = function () {
            if (!phoneInput || isPreview || currentVariationOutOfStock || isSubmittingOrder) return;
            const phone = phoneInput.value.trim();
            if (phone.length < 11) return;

            const tokenInput = form ? form.querySelector('input[name="_token"]') : null;
            const token = tokenInput ? tokenInput.value : '';

            const productId = form ? form.querySelector('input[name="product_id"]')?.value : null;
            const landingPageId = form ? form.querySelector('input[name="landing_page_id"]')?.value : null;
            const draftOrderId = document.getElementById('draft-order-id')?.value || null;
            const qtyVal = document.getElementById('input-qty')?.value || 1;
            const variationId = document.getElementById('selected-variation-id')?.value || null;
            const shippingArea = (form && form.querySelector('input[name="shipping_area"]:checked')?.value)
                || (form && form.querySelector('input[name="shipping_area"]')?.value)
                || 'inside';

            const selectedProdCheckboxes = Array.from(document.querySelectorAll('input[name="product_ids[]"]:checked'));
            const selectedProdIds = selectedProdCheckboxes.map(cb => cb.value);
            let productQuantities = {};
            selectedProdIds.forEach(pId => {
                productQuantities[pId] = variationQuantities['prod_' + pId] || 1;
            });

            fetch("{{ route('landing.order.partial') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    phone: phone,
                    name: nameInput ? nameInput.value.trim() : '',
                    address: addressInput ? addressInput.value.trim() : '',
                    product_id: productId,
                    landing_page_id: landingPageId,
                    draft_order_id: draftOrderId,
                    qty: qtyVal,
                    variation_id: variationId,
                    variation_quantities: ((isMultipleAttribute || isMultiProductMode) && typeof variationQuantities === 'object') ? variationQuantities : null,
                    product_ids: selectedProdIds.length > 0 ? selectedProdIds : null,
                    product_quantities: Object.keys(productQuantities).length > 0 ? productQuantities : null,
                    shipping_area: shippingArea
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.order_id) {
                    const draftInput = document.getElementById('draft-order-id');
                    if (draftInput) {
                        draftInput.value = data.order_id;
                    }
                    console.log("Landing draft order synced. Order ID:", data.order_id);
                }
            })
            .catch(err => {
                console.log("Draft save failed:", err);
            });
        };

        function scheduleDraftSave() {
            clearTimeout(draftDebounceTimer);
            draftDebounceTimer = setTimeout(window.saveLandingDraft, 500);
        }

        if (phoneInput) {
            phoneInput.addEventListener('blur', function () {
                if (!isSubmittingOrder) window.saveLandingDraft();
            });
            phoneInput.addEventListener('change', function () {
                if (!isSubmittingOrder) window.saveLandingDraft();
            });
            phoneInput.addEventListener('input', function () {
                if (this.value.trim().length >= 11) {
                    scheduleDraftSave();
                }
            });
        }

        if (nameInput) {
            nameInput.addEventListener('blur', scheduleDraftSave);
        }
        if (addressInput) {
            addressInput.addEventListener('blur', scheduleDraftSave);
        }
    });

    const landingForm = document.getElementById('landing-order-form');
    if (landingForm) {
        landingForm.onsubmit = function (e) {
            if (isPreview) {
                e.preventDefault();
                showPreviewAlert();
                return false;
            }
            if (currentVariationOutOfStock) {
                e.preventDefault();
                showOrderErrorModal('দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।');
                return false;
            }
            if (isMultiProductMode) {
                const checkedAny = document.querySelector('.variant-checkbox:checked');
                if (!checkedAny) {
                    e.preventDefault();
                    alert('দয়া করে অন্তত একটি পণ্য বা ভ্যারিয়েন্ট যোগ করুন!');
                    return false;
                }
            } else if (isVarProduct && !document.getElementById('selected-variation-id').value) {
                e.preventDefault();
                alert('দয়া করে পণ্য সিলেক্ট করুন!');
                return false;
            }

            e.preventDefault();
            isSubmittingOrder = true;
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg> প্রসেসিং...
                `;
            }

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async (res) => {
                let data = {};
                try {
                    data = await res.json();
                } catch (e) {
                    data = {};
                }

                if (res.ok && data.success) {
                    if (typeof fbq === 'function') {
                        fbq('track', 'Lead', {
                            content_name: '{{ addslashes($mainProduct->title ?? ($landing->title ?? "Order")) }}',
                            content_ids: ['{{ $mainProduct->id ?? "" }}'],
                            value: parseFloat(document.getElementById('summary-total')?.innerText.replace(/[^0-9.]/g, '') || 0),
                            currency: 'BDT'
                        });
                    }
                    showOrderSuccessModal(data.message, data.order_code, data.thankyou_url);
                    try {
                        landingForm.reset();
                        qty = 1;
                        if (typeof updateUI === 'function') {
                            updateUI();
                        }
                    } catch (e) {
                        console.warn("Reset error:", e);
                    }
                } else {
                    let msg = data.message || 'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।';
                    if (data.errors && typeof data.errors === 'object') {
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
                            msg = data.errors[firstKey][0];
                        }
                    }
                    showOrderErrorModal(msg);
                }
            })
            .catch((err) => {
                console.error("Order error:", err);
                showOrderErrorModal('সার্ভারে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।');
            })
            .finally(() => {
                isSubmittingOrder = false;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            });

            return false;
        };
    }

    @if(session('error'))
        // এরর থাকলে স্বয়ংক্রিয়ভাবে অর্ডার সেকশনে স্ক্রল করবে
        document.addEventListener('DOMContentLoaded', function () {
            const orderSection = document.getElementById('order') || document.getElementById('landing-order-form');
            if (orderSection) {
                setTimeout(function() {
                    orderSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        });
    @endif

    // Live preview scroll preservation (SessionStorage method)
    window.addEventListener('scroll', function () {
        sessionStorage.setItem('landing_preview_scroll', window.scrollY);
    });

    (function () {
        @if(!session('error') && !session('success'))
        const savedScroll = sessionStorage.getItem('landing_preview_scroll');
        if (savedScroll && parseInt(savedScroll) > 0) {
            const targetScroll = parseInt(savedScroll);
            const restoreScroll = () => window.scrollTo({ top: targetScroll, behavior: 'instant' });

            restoreScroll();
            document.addEventListener('DOMContentLoaded', restoreScroll);
            window.addEventListener('load', restoreScroll);

            // Re-apply in case images push content down
            setTimeout(restoreScroll, 100);
            setTimeout(restoreScroll, 300);
            setTimeout(restoreScroll, 500);
            setTimeout(restoreScroll, 1000);
        }
        @endif
    })();
</script>
