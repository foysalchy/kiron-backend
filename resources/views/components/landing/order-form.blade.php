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

    // Determine language (bn or en, default bn)
    $formLang = data_get($landing->extras, 'order_form_lang') ?: ($landing->order_form_lang ?? 'bn');
    $isEn = ($formLang === 'en');

    $trans = [
        'header_title' => $isEn ? 'Fill in the information to order' : 'অর্ডার করতে সঠিক তথ্য দিয়ে',
        'header_subtitle' => $isEn ? 'Complete the form below' : 'নিচের ফর্মটি পূরণ করুন',
        'select_product' => $isEn ? 'Select the product' : 'পণ্যটি সিলেক্ট করুন',
        'multiple_products_hint' => $isEn ? 'You can select multiple products' : 'একাধিক পণ্য সিলেক্ট করতে পারেন',
        'no_product_empty_title' => $isEn ? 'No product added to cart yet' : 'কোনো পণ্য এখনো কার্টে যোগ করা হয়নি',
        'no_product_empty_subtitle' => $isEn ? 'Please choose your desired product from above and add to cart.' : 'উপরের প্রোডাক্ট কার্ড থেকে পছন্দের পণ্য বেছে নিয়ে কার্টে যোগ করুন।',
        'choose_product_btn' => $isEn ? 'Choose Product' : 'পণ্য পছন্দ করুন',
        'price_label' => $isEn ? 'Price:' : 'মূল্য:',
        'out_of_stock' => $isEn ? 'Out of Stock' : 'স্টক আউট',
        'out_of_stock_full' => $isEn ? 'Out of Stock (Currently unavailable)' : 'স্টক আউট (বর্তমানে পণ্যটি স্টকে নেই)',
        'out_of_stock_btn' => $isEn ? 'Out of Stock (Unavailable)' : 'স্টক আউট (পণ্যটি শেষ)',
        'add_product_to_cart' => $isEn ? 'Remove from Cart' : 'কার্ট থেকে রিমুভ করুন',
        'your_name' => $isEn ? 'Your Name' : 'আপনার নাম লিখুন',
        'phone_number' => $isEn ? 'Mobile Number' : 'মোবাইল নাম্বার লিখুন',
        'full_address' => $isEn ? 'Full Address' : 'সম্পূর্ণ ঠিকানা লিখুন',
        'product' => $isEn ? 'Product' : 'Product',
        'price' => $isEn ? 'Price' : 'Price',
        'quantity' => $isEn ? 'Quantity' : 'Quantity',
        'delivery_area' => $isEn ? 'Delivery Area' : 'Delivery Area',
        'inside_dhaka' => $isEn ? 'Inside Dhaka' : 'Inside Dhaka',
        'outside_dhaka' => $isEn ? 'Outside Dhaka' : 'Outside Dhaka',
        'subtotal' => $isEn ? 'Subtotal' : 'Subtotal',
        'shipping_charge' => $isEn ? 'Shipping Charge' : 'Shipping Charge',
        'total' => $isEn ? 'Total' : 'Total',
        'free' => $isEn ? 'Free' : 'Free',
        'place_order' => $isEn ? 'Place Order' : 'অর্ডার করুন',
        'select_product_btn' => $isEn ? 'Select a product' : 'পণ্য নির্বাচন করুন',
        'view_cart' => $isEn ? 'View Cart' : 'কার্ট দেখুন',
        'no_variant_selected' => $isEn ? 'No variant selected' : 'কোনো ভ্যারিয়েন্ট সিলেক্ট করা হয়নি',
        'remove' => $isEn ? 'Remove' : 'রিমুভ করুন',
        'order_failed_title' => $isEn ? 'Sorry! Order could not be placed' : 'দুঃখিত! অর্ডার সম্পন্ন হয়নি',
        'order_failed_msg' => $isEn ? 'There was a problem placing your order.' : 'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে।',
        'try_again' => $isEn ? 'Try Again' : 'আবার চেষ্টা করুন',
        'order_success_title' => $isEn ? 'Congratulations! Your order was successful' : 'অভিনন্দন! আপনার অর্ডার সফল হয়েছে',
        'order_id' => $isEn ? 'Order ID:' : 'অর্ডার আইডি:',
        'order_success_msg' => $isEn ? 'Our representative will contact you shortly to confirm your order.' : 'কিছুক্ষণের মধ্যে আমাদের প্রতিনিধি আপনার সাথে ফোনে যোগাযোগ করে অর্ডারটি কনফার্ম করবে।',
        'ok' => $isEn ? 'OK' : 'ঠিক আছে',
        'processing' => $isEn ? 'Processing...' : 'প্রসেসিং...',
        'stock_limit_alert' => $isEn ? 'Sorry! Maximum :count items available in stock.' : 'দুঃখিত! এই পণ্যের সর্বোচ্চ :count টি স্টকে রয়েছে।',
        'stock_out_alert' => $isEn ? 'Sorry! No more items in stock (Maximum :count allowed).' : 'দুঃখিত! স্টকে আর পণ্য নেই (সর্বোচ্চ :count টি প্রযোজ্য)।',
        'selected_item_out_of_stock' => $isEn ? 'Sorry! The selected product is currently out of stock.' : 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।',
        'select_at_least_one' => $isEn ? 'Please add at least one product or variant!' : 'দয়া করে অন্তত একটি পণ্য বা ভ্যারিয়েন্ট যোগ করুন!',
        'select_product_prompt' => $isEn ? 'Please select a product!' : 'দয়া করে পণ্য সিলেক্ট করুন!',
        'order_failed_retry' => $isEn ? 'There was a problem placing your order. Please try again.' : 'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।',
        'server_error_retry' => $isEn ? 'Server error occurred. Please try again.' : 'সার্ভারে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।',
    ];
@endphp

<div class="max-w-7xl mx-auto bg-white p-6 md:p-12 rounded-xl shadow-sm  border border-gray-200">
    <form @if($isPreviewMode) action="javascript:void(0);" onsubmit="event.preventDefault(); showPreviewAlert(); return false;" @else action="{{ route('landing.order.store') }}" method="POST" @endif id="landing-order-form" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
        @csrf
        {{-- Anti-Spam Honeypot Field --}}
        <div style="display:none !important;" aria-hidden="true">
            <input type="text" name="_hp_security_check" value="" tabindex="-1" autocomplete="off" />
        </div>
        {{-- Decoy fields to trap browser address autofill --}}
        <div style="opacity: 0; position: absolute; top: 0; left: -9999px; height: 0; width: 0; z-index: -1; overflow: hidden;" aria-hidden="true">
            <input type="text" name="fake_autofill_name" tabindex="-1" autocomplete="name" />
            <input type="text" name="fake_autofill_address" tabindex="-1" autocomplete="street-address" />
            <input type="tel" name="fake_autofill_phone" tabindex="-1" autocomplete="tel" />
        </div>
        <input type="hidden" name="product_id" value="{{ $landing->product_id }}">
        <input type="hidden" name="landing_page_id" value="{{ $landing->id }}">
        <input type="hidden" name="draft_order_id" id="draft-order-id" value="">
        <input type="hidden" name="qty" id="input-qty" value="1">

        @if ($mainProduct->type === 'variation')
            <input type="hidden" name="variation_id" id="selected-variation-id">
        @endif

        {{-- Header --}}
        <div class="text-center mb-10">
            <h2 class="text-xl sm:text-2xl md:text-4xl font-bold mb-5 text-black leading-normal md:leading-relaxed">{{ $trans['header_title'] }}</h2>
            <div
                class="inline-block bg-[#1f8a54] text-white px-10 py-3 rounded-lg text-xl md:text-2xl shadow-md font-bold">
                {{ $trans['header_subtitle'] }}</div>
        </div>

        {{-- Product Selection Area (Only shown for multi-product mode or when product has variations) --}}
        @if ($isMultiProductMode || $mainProduct->type === 'variation')
        <div class="space-y-4 mb-10">
            <div class="flex items-center justify-between">
                <label class="block text-lg font-bold text-gray-800">
                    {{ $trans['select_product'] }} <span class="text-red-500">*</span>
                </label>
                @if ($mainProduct->type === 'variation' && $isMultipleAttribute)
                    <span class="text-xs font-bold text-[#14532d] bg-[#dcfce7] px-3 py-1 rounded-full border border-[#86efac]">
                        {{ $trans['multiple_products_hint'] }}
                    </span>
                @endif
            </div>

            @if ($isMultiProductMode)
                {{-- Empty state prompt if nothing is added yet --}}
                <div id="multi-product-empty-notice" class="p-6 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl">
                    <div class="text-center mb-4">
                        <p class="text-base font-bold text-gray-800 mb-1">{{ $trans['no_product_empty_title'] }}</p>
                        <p class="text-sm text-gray-500 font-medium">{{ $trans['no_product_empty_subtitle'] }}</p>
                    </div>
                    <div class="flex justify-center">
                        <a href="#landing-products-section"
                            onclick="const el = document.getElementById('landing-products-section') || document.querySelector('.product-card'); if (el) { el.scrollIntoView({ behavior: 'smooth' }); return false; }"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#15803d] hover:bg-[#166534] text-white text-sm font-bold rounded-xl shadow-sm hover:shadow transition-all active:scale-95 cursor-pointer">
                            <i class="fa-solid fa-cart-plus text-sm"></i>
                            <span>{{ $trans['choose_product_btn'] }}</span>
                            <i class="fa-solid fa-arrow-up text-xs"></i>
                        </a>
                    </div>
                </div>

                {{-- MULTI-PRODUCT SELECTION LIST --}}
                <div class="space-y-6" id="multi-product-list-wrapper">
                    @foreach ($allProducts as $p)
                        @php
                            $varsCount = ($p->variations) ? $p->variations->count() : 0;
                            $pHasMultipleVars = ($p->type === 'variation') && ($varsCount > 1);
                            $pHasSingleVar = ($p->type === 'variation') && ($varsCount === 1);
                            $singleVar = $pHasSingleVar ? $p->variations->first() : null;

                            $firstVar = ($p->type === 'variation' && $p->variations) ? $p->variations->first() : null;
                            $pPrice = $firstVar ? ($firstVar->regular_price - ($firstVar->discount ?? 0)) : ($p->sale_price ?? ($p->regular_price - ($p->discount ?? 0)));
                            $pImg = $p->thumbnail_url ?? $p->display_image_url ?? asset('images/default.webp');

                            $pIsSingleOutOfStock = ($p->type === 'single') && (
                                $p->stock_status === 'out_of_stock' ||
                                ($p->manage_stock && ($p->available_stock <= 0))
                            );

                            $singleVarOutOfStock = false;
                            $singleVarMaxStock = 999999;
                            if ($pHasSingleVar && $singleVar) {
                                $singleVarOutOfStock = (
                                    $singleVar->stock_status === 'out_of_stock' ||
                                    ($p->manage_stock && ($singleVar->available_stock <= 0))
                                );
                                $singleVarMaxStock = $p->manage_stock ? ($singleVar->available_stock ?? 0) : 999999;
                            }

                            $pIsMultipleAttribute = false;
                            if ($p->type === 'variation' && $p->variations) {
                                $pVarIds = $p->variations->pluck('id')->filter()->toArray();
                                if (!empty($pVarIds)) {
                                    $pIsMultipleAttribute = \App\Models\AttributeGroup::withoutGlobalScopes()
                                        ->whereIn('id', function($q) use ($pVarIds) {
                                            $q->select('attribute_group_id')
                                              ->from('product_variation_attributes')
                                              ->whereIn('product_variation_id', $pVarIds);
                                        })
                                        ->whereRaw('LOWER(category) = ?', ['multiple'])
                                        ->exists();
                                }
                            }
                        @endphp

                        <div class="order-product-item-wrapper l1-single-variant-container border-2 border-gray-200 rounded-2xl p-4 sm:p-5 bg-white transition-all duration-200 hover:border-gray-300 hidden" data-order-product-id="{{ $p->id }}" data-product-id="{{ $p->id }}" data-is-multiple="{{ $pIsMultipleAttribute ? '1' : '0' }}">
                            {{-- Top Row: Checkbox + Thumbnail + Product Title + Price + Side Remove --}}
                            <div class="flex items-start justify-between gap-3 cursor-pointer select-none" onclick="toggleMultiProductCard('{{ $p->id }}')">
                                <div class="flex items-center gap-3.5 sm:gap-4 flex-grow min-w-0">
                                    <input type="checkbox" id="multi-card-cb-{{ $p->id }}" class="w-5 h-5 rounded border-2 border-gray-300 text-[#35B11E] focus:ring-0 focus:ring-offset-0 pointer-events-none cursor-pointer">

                                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-xl overflow-hidden flex-shrink-0 border border-gray-100 flex items-center justify-center p-1">
                                        <img id="multi-card-img-{{ $p->id }}" src="{{ $pImg }}" width="80" height="80" class="w-full h-full object-contain pointer-events-none" alt="{{ $p->title }}">
                                    </div>

                                    <div class="flex-grow min-w-0 text-left">
                                        <h4 class="font-bold text-gray-900 text-sm sm:text-base leading-snug truncate">
                                            {{ $p->title }}
                                        </h4>
                                        <div class="mt-1 flex items-baseline gap-2 flex-wrap">
                                            <span id="multi-card-price-{{ $p->id }}" class="text-base sm:text-lg font-black text-[#0D2601]">
                                                {{ number_format($pPrice, 2) }}{{ $setup->currency }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button type="button"
                                    onclick="event.stopPropagation(); removeEntireMultiProduct('{{ $p->id }}')"
                                    class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition-colors cursor-pointer flex-shrink-0 mt-0.5"
                                    title="{{ $trans['remove'] }}">
                                    <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            @if ($pHasMultipleVars)
                                {{-- Variations of this product (Identical collapsible drawer matching single variation design) --}}
                                <div id="multi-drawer-{{ $p->id }}" class="mt-4 pt-4 border-t border-gray-100 hidden transition-all">
                                    <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap pt-1">
                                        @foreach ($p->variations as $variation)
                                            @php
                                                $vSalePrice = $variation->regular_price - ($variation->discount ?? 0);
                                                $vImage = $variation->image ? (\Illuminate\Support\Facades\Storage::disk('r2')->url($variation->image)) : $pImg;
                                                $vOutOfStock = (
                                                    $variation->stock_status === 'out_of_stock' ||
                                                    ($p->manage_stock && ($variation->available_stock <= 0))
                                                );
                                                $maxStock = $p->manage_stock ? ($variation->available_stock ?? 0) : 999999;
                                                $dispName = $variation->display_name ?: ($variation->sku ?: 'Option');
                                            @endphp

                                            <button type="button"
                                                onclick="selectMultiProductVariationButton('{{ $p->id }}', '{{ $variation->id }}', this)"
                                                data-prod-id="{{ $p->id }}"
                                                data-var-id="{{ $variation->id }}"
                                                data-price="{{ $vSalePrice }}"
                                                data-img="{{ $vImage }}"
                                                data-out-of-stock="{{ $vOutOfStock ? '1' : '0' }}"
                                                class="l1-size-btn multi-size-btn-{{ $p->id }} px-4 py-2 rounded-xl border-2 text-xs sm:text-sm font-bold transition-all cursor-pointer bg-white text-gray-800 border-gray-300 hover:border-[#35B11E] {{ $vOutOfStock ? 'opacity-50 cursor-not-allowed line-through' : '' }}"
                                                {{ $vOutOfStock ? 'disabled' : '' }}>
                                                {{ $dispName }}
                                            </button>

                                            {{-- Hidden input for form binding --}}
                                            <input type="checkbox" name="variation_ids[]" id="multi_var_input_{{ $variation->id }}" value="{{ $variation->id }}"
                                                data-price="{{ $vSalePrice }}"
                                                data-name="{{ $dispName }}"
                                                data-title="{{ $p->title }}"
                                                data-img="{{ $vImage }}"
                                                data-is-free="{{ $p->is_free_delivery ? '1' : '0' }}"
                                                data-out-of-stock="{{ $vOutOfStock ? '1' : '0' }}"
                                                data-max-stock="{{ $maxStock }}"
                                                {{ $vOutOfStock ? 'disabled' : '' }}
                                                onchange="onVariationCheckboxChange()"
                                                class="hidden variant-checkbox">
                                        @endforeach
                                    </div>
                                </div>
                            @elseif ($pHasSingleVar && $singleVar)
                                <input type="checkbox" name="variation_ids[]" id="multi_var_input_{{ $singleVar->id }}" value="{{ $singleVar->id }}"
                                    data-price="{{ $pPrice }}"
                                    data-name="{{ $singleVar->display_name }}"
                                    data-title="{{ $p->title }}"
                                    data-img="{{ $pImg }}"
                                    data-is-free="{{ $p->is_free_delivery ? '1' : '0' }}"
                                    data-out-of-stock="{{ $singleVarOutOfStock ? '1' : '0' }}"
                                    data-max-stock="{{ $singleVarMaxStock }}"
                                    {{ $singleVarOutOfStock ? 'disabled' : '' }}
                                    onchange="onVariationCheckboxChange()"
                                    class="hidden variant-checkbox">
                            @else
                                <input type="checkbox" name="product_ids[]" id="multi_prod_input_{{ $p->id }}" value="{{ $p->id }}"
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
                                    class="hidden variant-checkbox">
                            @endif
                        </div>
                    @endforeach
                </div>
            @elseif ($mainProduct->type === 'variation')
                @php
                    $pImg = $landing->thumbnail_url ?? $mainProduct->thumbnail_url ?? asset('images/template1/frontend/default.webp');
                    $firstVar = $mainProduct->variations->first();
                    $pPrice = $firstVar ? ($firstVar->regular_price - $firstVar->discount) : ($mainProduct->sale_price ?? $mainProduct->regular_price);
                @endphp

                <div class="l1-single-variant-container border-2 border-gray-200 rounded-2xl p-4 sm:p-5 bg-white transition-all duration-200 hover:border-gray-300">
                    {{-- Top Header Row: Checkbox + Thumbnail + Product Title / Code + Price --}}
                    <div class="flex items-start justify-between gap-3 cursor-pointer select-none" onclick="toggleL1MainCard()">
                        <div class="flex items-center gap-3.5 sm:gap-4 flex-grow min-w-0">
                            <input type="checkbox" id="l1-main-checkbox-input" class="w-5 h-5 rounded border-2 border-gray-300 text-[#35B11E] focus:ring-0 focus:ring-offset-0 pointer-events-none cursor-pointer">

                            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-xl overflow-hidden flex-shrink-0 border border-gray-100 flex items-center justify-center p-1">
                                <img id="l1-main-thumb" src="{{ $pImg }}" width="80" height="80" class="w-full h-full object-contain pointer-events-none" alt="{{ $mainProduct->title }}">
                            </div>

                            <div class="flex-grow min-w-0 text-left">
                                <h4 class="font-bold text-gray-900 text-sm sm:text-base leading-snug truncate">
                                    {{ $landing->title ?? $mainProduct->title }}
                                </h4>
                                <div class="mt-1 flex items-baseline gap-2 flex-wrap">
                                    <span id="l1-main-price" class="text-base sm:text-lg font-black text-[#0D2601]">
                                        {{ number_format($pPrice, 2) }}{{ $setup->currency }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button type="button"
                            onclick="event.stopPropagation(); removeSingleVariationCard();"
                            class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition-colors cursor-pointer flex-shrink-0 mt-0.5"
                            title="{{ $trans['remove'] }}">
                            <svg class="w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Hidden inputs to maintain form binding --}}
                    <div class="hidden">
                        @foreach ($mainProduct->variations as $index => $variation)
                            @php
                                $vSalePrice = $variation->regular_price - $variation->discount;
                                $vImage = $variation->image_url ?? $mainProduct->thumbnail_url ?? asset('images/template1/frontend/default.webp');
                                $vOutOfStock = (
                                    $variation->stock_status === 'out_of_stock' ||
                                    ($mainProduct->manage_stock && ($variation->available_stock <= 0))
                                );
                                $maxStock = $mainProduct->manage_stock ? ($variation->available_stock ?? 0) : 999999;
                                $dispName = $variation->display_name ?: ($variation->sku ?: 'Option ' . ($index + 1));
                            @endphp
                            @if ($isMultipleAttribute)
                                <input type="checkbox" name="variation_ids[]" id="l1_input_{{ $variation->id }}" value="{{ $variation->id }}"
                                    data-price="{{ $vSalePrice }}"
                                    data-name="{{ $dispName }}"
                                    data-img="{{ $vImage }}"
                                    data-is-free="{{ $mainProduct->is_free_delivery ? '1' : '0' }}"
                                    data-out-of-stock="{{ $vOutOfStock ? '1' : '0' }}"
                                    data-max-stock="{{ $maxStock }}"
                                    {{ $vOutOfStock ? 'disabled' : '' }}
                                    onchange="onVariationCheckboxChange()"
                                    class="l1-internal-input variant-checkbox">
                            @else
                                <input type="radio" name="variant_radio" id="l1_radio_{{ $variation->id }}" value="{{ $variation->id }}"
                                    data-price="{{ $vSalePrice }}"
                                    data-name="{{ $dispName }}"
                                    data-img="{{ $vImage }}"
                                    data-is-free="{{ $mainProduct->is_free_delivery ? '1' : '0' }}"
                                    data-out-of-stock="{{ $vOutOfStock ? '1' : '0' }}"
                                    data-max-stock="{{ $maxStock }}"
                                    {{ $vOutOfStock ? 'disabled' : '' }}
                                    onchange="updateVariation('{{ $variation->id }}', {{ $vSalePrice }}, '{{ addslashes($dispName) }}', '{{ $vImage }}', {{ $vOutOfStock ? 'true' : 'false' }}, {{ $maxStock }})"
                                    class="l1-internal-input">
                            @endif
                        @endforeach
                    </div>

                    {{-- Collapsible Variation Selector (Hidden initially until card/checkbox is clicked) --}}
                    <div id="l1-size-drawer" class="mt-4 pt-4 border-t border-gray-100 hidden transition-all">
                        <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap pt-1">
                            @foreach ($mainProduct->variations as $index => $variation)
                                @php
                                    $vSalePrice = $variation->regular_price - $variation->discount;
                                    $vImage = $variation->image_url ?? $mainProduct->thumbnail_url ?? asset('images/template1/frontend/default.webp');
                                    $vOutOfStock = (
                                        $variation->stock_status === 'out_of_stock' ||
                                        ($mainProduct->manage_stock && ($variation->available_stock <= 0))
                                    );
                                    $dispName = $variation->display_name ?: ($variation->sku ?: 'Option ' . ($index + 1));
                                @endphp
                                <button type="button"
                                    onclick="selectL1VariationButton('{{ $variation->id }}', this)"
                                    data-var-id="{{ $variation->id }}"
                                    data-price="{{ $vSalePrice }}"
                                    data-img="{{ $vImage }}"
                                    data-out-of-stock="{{ $vOutOfStock ? '1' : '0' }}"
                                    class="l1-size-btn px-4 py-2 rounded-xl border-2 text-xs sm:text-sm font-bold transition-all cursor-pointer bg-white text-gray-800 border-gray-300 hover:border-[#35B11E] {{ $vOutOfStock ? 'opacity-50 cursor-not-allowed line-through' : '' }}"
                                    {{ $vOutOfStock ? 'disabled' : '' }}>
                                    {{ $dispName }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @endif

        {{-- Main Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
 {{-- RIGHT: Order Summary with Shipping Area --}}
            <div class="lg:col-span-5">
                <div class="bg-white p-2 sticky top-8 ">

                    {{-- Free Shipping Banner / Threshold Indicator --}}
                    @php
                        $freeDeliverySuccessText = ($setup->lang ?? 'bn') === 'bn' ? 'এই পণ্যে ফ্রি ডেলিভারি সুবিধা রয়েছে!' : 'Free Delivery on this product!';
                        $activeRule = $setup->active_free_shipping_rule ?? null;
                        $hasLandingRule = !empty($activeRule) && (!empty($activeRule['landing']) || !empty($activeRule['apply_in_landing']));
                    @endphp
                    @if($isFree || ($isMultiProductMode && $hasLandingRule && !empty($setup->free_shipping_text)))
                        @if($isFree)
                            <div id="landing-free-shipping-banner" class="mb-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-2.5 text-emerald-800 text-xs sm:text-lg font-semibold shadow-xs">
                                <i class="fa-solid fa-truck-fast text-emerald-600 text-base"></i>
                                <span id="landing-free-shipping-text">{{ $freeDeliverySuccessText }}</span>
                            </div>
                        @elseif($isMultiProductMode && $hasLandingRule && !empty($setup->free_shipping_text))
                            <div id="landing-free-shipping-banner" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2.5 text-amber-800 text-xs sm:text-lg font-medium">
                                <i class="fa-solid fa-gift text-amber-600 text-sm"></i>
                                <span id="landing-free-shipping-text">{{ $setup->free_shipping_text }}</span>
                            </div>
                        @endif
                    @endif

                    {{-- Table Header --}}
                    <div
                        class="flex justify-between text-xs font-bold text-gray-500 uppercase tracking-widest mb-4 pb-3 border-b border-dashed border-gray-200">
                        <span>{{ $trans['product'] }}</span><span class="text-right">{{ $trans['price'] }}</span>
                    </div>

                    {{-- Product Info Rows Container (Dynamic) --}}
                    <div id="summary-items-list" class="space-y-3 pb-5 mb-3 border-b border-dashed border-gray-200">
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
                    <div class="px-2 space-y-4 mb-2">
                        <div id="global-qty-wrapper" class="flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-700">{{ $trans['quantity'] }}</span>
                            <div class="flex items-center border border-gray-300 rounded bg-white overflow-hidden">
                                <button type="button" onclick="changeQty(-1)" aria-label="Decrease quantity"
                                    class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 border-r border-gray-300 font-bold cursor-pointer">−</button>
                                <span id="qty-display" class="w-10 text-center font-bold text-sm">1</span>
                                <button type="button" onclick="changeQty(1)" aria-label="Increase quantity"
                                    class="w-8 h-8 flex items-center justify-center hover:bg-gray-100 border-l border-gray-300 font-bold cursor-pointer">+</button>
                            </div>
                        </div>

                        <div id="delivery-area-section" class="space-y-2 {{ $isFree ? 'hidden' : '' }}">
                            <label class="block text-sm font-bold text-gray-500 uppercase tracking-wide">{{ $trans['delivery_area'] }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="shipping_area" id="shipping-area-inside" value="inside" checked
                                        onchange="updateDeliveryCharge({{ $defaultInsideCharge }})" class="hidden peer">
                                    <div
                                        class="p-2 border-2 border-gray-100 rounded-lg text-center text-sm font-semibold text-gray-600 peer-checked:border-[#1f8a54] peer-checked:text-[#145a32] bg-gray-50">
                                        {{ $trans['inside_dhaka'] }} <span class="text-xs font-bold ml-1">({{ $defaultInsideCharge.$setup->currency }})</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="shipping_area" id="shipping-area-outside" value="outside"
                                        onchange="updateDeliveryCharge({{ $defaultOutsideCharge }})" class="hidden peer">
                                    <div
                                        class="p-2 border-2 border-gray-100 rounded-lg text-center text-sm font-semibold text-gray-600 peer-checked:border-[#1f8a54] peer-checked:text-[#145a32] bg-gray-50">
                                        {{ $trans['outside_dhaka'] }} <span class="text-xs font-bold ml-1">({{ $defaultOutsideCharge.$setup->currency }})</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Calculations --}}
                    <div class="px-2 space-y-1 border-t border-dashed border-gray-100 pt-2 mb-2">
                        <div class="flex justify-between text-sm md:text-base text-gray-800">
                            <span>{{ $trans['subtotal'] }}</span>
                            <span class="font-bold text-gray-800"><span
                                    id="summary-subtotal"></span>{{ $setup->currency }}</span>
                        </div>
                        <div class="flex justify-between text-sm md:text-base text-gray-800">
                            <span>{{ $trans['shipping_charge'] }}</span>
                            <span class="font-bold text-red-600" id="delivery-charge-display-wrapper">
                                +<span id="delivery-charge-display"></span>{{ $setup->currency }}
                            </span>
                        </div>
                        <div
                            class="flex justify-between items-center text-lg font-bold text-gray-900 pt-2 border-t border-gray-50">
                            <span>{{ $trans['total'] }}</span>
                            <span id="summary-total" class="text-2xl font-black"></span>
                        </div>
                    </div>

                   
                </div>
            </div>
            {{-- LEFT: Billing Info --}}
            <div class="lg:col-span-7 space-y-6">
                <div>
                    <label for="name" class="block text-md font-bold mb-2 text-gray-800">{{ $trans['your_name'] }} <span
                            class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" required
                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');" onmousedown="this.removeAttribute('readonly');" ontouchstart="this.removeAttribute('readonly');"
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 bg-white focus:border-[#1f8a54] outline-none">
                </div>
                <div>
                    <label for="phone" class="block text-md font-bold mb-2 text-gray-800">{{ $trans['phone_number'] }} <span
                            class="text-red-500">*</span></label>
                    <input type="tel" id="phone" name="phone" required
                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');" onmousedown="this.removeAttribute('readonly');" ontouchstart="this.removeAttribute('readonly');"
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 bg-white focus:border-[#1f8a54] outline-none">
                    <p id="landing-phone-error" class="hidden text-xs font-semibold text-red-600 mt-1.5 flex items-center gap-1">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span id="landing-phone-error-text">Invalid phone number</span>
                    </p>
                </div>
                <div>
                    <label for="address" class="block text-md font-bold mb-2 text-gray-800">{{ $trans['full_address'] }} <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="address" name="address" required
                        autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-form-type="other"
                        readonly onfocus="this.removeAttribute('readonly');" onpointerdown="this.removeAttribute('readonly');" onmousedown="this.removeAttribute('readonly');" ontouchstart="this.removeAttribute('readonly');"
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 bg-white focus:border-[#1f8a54] outline-none">
                </div>
                <div>
                     <button type="{{ $isPreviewMode ? 'button' : 'submit' }}"
                        id="submit-order-btn"
                        {{ $isEntirelyOutOfStock ? 'disabled' : '' }}
                        @if($isPreviewMode) onclick="showPreviewAlert()" @endif
                        class="w-full {{ $isEntirelyOutOfStock ? 'bg-gray-400 cursor-not-allowed opacity-75' : 'bg-[#1f8a54] hover:bg-[#176840] cursor-pointer' }} text-white font-bold py-5 rounded-xl text-2xl active:translate-y-1 transition-all flex items-center justify-center gap-2">
                        @if($isEntirelyOutOfStock)
                            <span>{{ $trans['out_of_stock_btn'] }}</span>
                        @else
                            <span id="btn-text">{{ $trans['place_order'] }}</span> <span id="btn-total"></span>{{ $setup->currency }}
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
        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $trans['order_failed_title'] }}</h3>
        <!-- Error message -->
        <div id="order-error-modal-message" class="bg-red-50 text-red-700 text-sm md:text-base font-medium rounded-xl p-3.5 mb-6 border border-red-200/60 leading-relaxed">
            {{ session('error') ?? $trans['order_failed_msg'] }}
        </div>
        <!-- Action Button -->
        <button type="button" onclick="closeOrderErrorModal()" class="w-full py-3.5 px-6 bg-[#1f8a54] hover:bg-[#176840] text-white font-bold text-lg rounded-xl transition-all shadow-lg hover:shadow-xl cursor-pointer active:scale-98">
            {{ $trans['try_again'] }}
        </button>
    </div>
</div>

{{-- Order Success Modal --}}
<div id="order-success-modal" class="fixed inset-0 z-[999999] {{ session('success') ? 'flex' : 'hidden' }} items-center justify-center bg-black/60 backdrop-blur-xs p-4" onclick="if(event.target === this) closeOrderSuccessModal()">
    <div class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full p-6 md:p-10 text-center transform transition-all border border-gray-100 animate-in fade-in zoom-in duration-200">
        <!-- Close Button -->
        <button type="button" onclick="closeOrderSuccessModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors cursor-pointer" aria-label="Close modal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Icon -->
        <div class="w-16 h-16 mx-auto mb-5 bg-[#dcfce7] text-[#16a34a] rounded-full flex items-center justify-center">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>
        <!-- Title -->
        <h3 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2.5">Thank you for your order!</h3>
        <!-- Subtitle -->
        <p class="text-gray-500 text-sm md:text-base leading-relaxed mb-8">
            Your order <span id="order-success-modal-code" class="font-semibold text-gray-800">{{ session('order_code') ? (str_starts_with(session('order_code'), '#') ? session('order_code') : '#' . session('order_code')) : '' }}</span> has been placed successfully. We'll send you a confirmation email shortly.
        </p>
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="w-full sm:w-auto px-6 py-2.5 bg-[#16a34a] hover:bg-[#1a3a1a] text-white font-medium text-sm md:text-base rounded-lg transition-colors text-center shadow-xs">
                Continue to Shopping
            </a>
            <a id="order-success-invoice-btn" href="{{ session('invoice_url') ?? (session('order_id') ? route('order.invoice', session('order_id')) : '#') }}" target="_blank" class="w-full sm:w-auto px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium text-sm md:text-base rounded-lg transition-colors flex items-center justify-center gap-2 text-center">
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Download Invoice</span>
            </a>
        </div>
    </div>
</div>

<x-frontend.checkout-otp-modal />

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
        onclick="const el = document.getElementById('landing-order-section') || document.getElementById('landing-order-form') || document.getElementById('order'); if (el) el.scrollIntoView({ behavior: 'smooth' });"
        class="fixed bottom-6 right-5 z-[999] hidden items-center gap-3 bg-[#0D2601] hover:bg-[#1a4a03] text-white px-4 py-3 rounded-full shadow-2xl border-2 border-white/20 cursor-pointer transition-all duration-300 transform hover:scale-105 active:scale-95 group">
        <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-[#35B11E] text-white">
            <i class="fa-solid fa-cart-shopping text-sm"></i>
            <span id="floating-cart-count" class="absolute -top-1.5 -right-1.5 bg-red-600 text-white text-[10px] font-black rounded-full h-4 min-w-[16px] px-1 flex items-center justify-center border border-white">0</span>
        </div>
        <div class="text-left pr-1">
            <p class="text-[10px] uppercase font-bold text-gray-300 tracking-wider leading-none">{{ $trans['view_cart'] }}</p>
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
    const FORM_TRANS = @json($trans);

    const isMultiProductMode = {{ $isMultiProductMode ? 'true' : 'false' }};
    const activeFreeShippingRule = @json($setup->active_free_shipping_rule ?? null);
    const hasLandingRule = activeFreeShippingRule && Boolean(activeFreeShippingRule.apply_in_landing || activeFreeShippingRule.landing);
    const FREE_SHIPPING_SUCCESS_TEXT = "{{ $freeDeliverySuccessText }}";
    const GLOBAL_FREE_SHIPPING_TEXT = @json($setup->free_shipping_text ?? '');

    function checkFreeShippingEligibility(totalQty, subtotal) {
        if (!hasLandingRule) return false;

        const minQty = parseInt(activeFreeShippingRule.qty || 0, 10);
        const minPrice = parseFloat(activeFreeShippingRule.price || 0);

        if (minQty > 0 && minPrice > 0) {
            return (totalQty >= minQty && subtotal >= minPrice);
        }
        if (minQty > 0) {
            return (totalQty >= minQty);
        }
        if (minPrice > 0) {
            return (subtotal >= minPrice);
        }
        return false;
    }

    document.addEventListener("DOMContentLoaded", function () {
        if (isMultiProductMode) {
            onVariationCheckboxChange();
        } else if (isVarProduct) {
            if (isMultipleAttribute) {
                // If multiple attribute, initially unselected or toggle based on checked
                onVariationCheckboxChange();
            } else {
                // Single variation attribute: default select the first available variation
                const firstAvailableBtn = document.querySelector('.l1-size-btn:not([disabled])');
                if (firstAvailableBtn) {
                    firstAvailableBtn.click();
                } else {
                    const firstBtn = document.querySelector('.l1-size-btn');
                    if (firstBtn) firstBtn.click();
                }
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
                    ${FORM_TRANS.no_variant_selected || 'কোনো ভ্যারিয়েন্ট সিলেক্ট করা হয়নি'}
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
                <div class="py-3 border-b border-gray-100 last:border-0" data-item-id="${item.id}">
                    {{-- Row 1: Image + Full Title & Option + Top-Right Cross Remove Button --}}
                    <div class="flex items-start gap-3">
                        <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 border border-gray-200 bg-white p-0.5">
                            <img src="${item.img || PRODUCT_THUMB}" width="48" height="48"
                                alt="${item.title || PRODUCT_TITLE}"
                                class="w-full h-full object-contain">
                        </div>
                        <div class="flex-1 min-w-0 pr-1">
                            <p class="text-xs font-bold text-gray-800 leading-snug truncate">${item.title || PRODUCT_TITLE}</p>
                            @if ($mainProduct->type === 'variation' || $isMultiProductMode)
                                ${item.option ? `<p class="text-[11px] text-[#145a32] font-bold mt-0.5 uppercase">${item.option}</p>` : ''}
                            @endif
                        </div>
                        ${isMulti ? `
                            <button type="button" onclick="removeVariationItem('${item.id}', ${item.isProduct ? 'true' : 'false'})"
                                class="w-5 h-5 flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors cursor-pointer flex-shrink-0 mt-0.5"
                                title="${FORM_TRANS.remove || 'রিমুভ করুন'}">
                                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        ` : ''}
                    </div>

                    {{-- Row 2: Unit Price + Quantity Selector + Line Total --}}
                    <div class="flex items-center justify-between gap-2 mt-2 pl-[60px]">
                        <div class="text-[11px] text-gray-500 font-semibold">
                            ${price.toFixed(2)}${CURRENCY}
                        </div>

                        <div class="flex items-center gap-3">
                            ${isMulti ? `
                                <div class="flex items-center border border-gray-300 rounded bg-white overflow-hidden shadow-xs">
                                    <button type="button" onclick="changeItemQty('${itemKey}', -1, ${item.maxStock})"
                                        class="w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-gray-100 border-r border-gray-300 cursor-pointer text-gray-600">−</button>
                                    <span class="w-7 text-center font-bold text-xs text-gray-800">${itemQty}</span>
                                    <button type="button" onclick="changeItemQty('${itemKey}', 1, ${item.maxStock})"
                                        class="w-6 h-6 flex items-center justify-center text-xs font-bold hover:bg-gray-100 border-l border-gray-300 cursor-pointer text-gray-600">+</button>
                                </div>
                                ${item.isProduct
                                    ? `<input type="hidden" name="product_quantities[${item.id}]" value="${itemQty}">`
                                    : `<input type="hidden" name="variation_quantities[${item.id}]" value="${itemQty}">`
                                }
                            ` : `
                                <span class="text-xs font-bold text-gray-700">
                                    <span class="text-gray-400">×</span> <span class="item-summary-qty">${qty}</span>
                                </span>
                            `}

                            <div class="text-right min-w-[70px]">
                                <span class="text-xs sm:text-sm font-black text-gray-900">${lineTotalFormatted}${CURRENCY}</span>
                            </div>
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
            const stockMsg = (FORM_TRANS.stock_limit_alert || 'দুঃখিত! এই পণ্যের সর্বোচ্চ :count টি স্টকে রয়েছে।').replace(':count', maxStock);
            alert(stockMsg);
            return;
        }

        variationQuantities[storageKey] = newQty;
        onVariationCheckboxChange();
    }

    function removeEntireMultiProduct(productId) {
        const wrapper = document.querySelector(`.order-product-item-wrapper[data-order-product-id="${productId}"]`);
        const checkbox = document.getElementById(`multi-card-cb-${productId}`);
        const drawer = document.getElementById(`multi-drawer-${productId}`);

        if (wrapper) {
            wrapper.querySelectorAll('input.variant-checkbox').forEach(input => {
                input.checked = false;
                delete variationQuantities[input.value];
                delete variationQuantities['prod_' + input.value];
            });
            wrapper.querySelectorAll('.l1-size-btn').forEach(b => {
                b.classList.remove('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                b.classList.add('bg-white', 'text-gray-800', 'border-gray-300');
            });
            wrapper.classList.add('hidden');
            wrapper.classList.remove('border-[#35B11E]', 'bg-[#fdf8f4]', 'shadow-sm');
            wrapper.classList.add('border-gray-200', 'bg-white');
        }
        if (checkbox) checkbox.checked = false;
        if (drawer) drawer.classList.add('hidden');

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

            // If this variation belongs to a button, deactivate its styling
            const btn = document.querySelector(`.l1-size-btn[data-var-id="${id}"]`);
            if (btn) {
                btn.classList.remove('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                btn.classList.add('bg-white', 'text-gray-800', 'border-gray-300');
            }

            onVariationCheckboxChange();
        }
    }

    function onVariationCheckboxChange() {
        const checkboxes = Array.from(document.querySelectorAll('.variant-checkbox'));
        const checkedList = checkboxes.filter(cb => cb.checked);

        // Update visual checkmark icons, box highlights, and button highlights
        checkboxes.forEach(cb => {
            const label = cb.closest('label');
            if (label) {
                const box = label.querySelector('.checkmark-box');
                const card = label.querySelector('.variant-card-box');
                const icon = label.querySelector('.checkmark-icon');
                const btn = label.querySelector('.l1-multi-opt-btn');

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
                    if (btn) {
                        btn.classList.remove('bg-white', 'text-gray-800', 'border-gray-300');
                        btn.classList.add('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
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
                    if (btn) {
                        btn.classList.remove('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                        btn.classList.add('bg-white', 'text-gray-800', 'border-gray-300');
                    }
                }
            }
        });

        // Show/hide product cards in form based on whether any checkbox in that product is checked
        let anyProductVisible = false;
        document.querySelectorAll('.order-product-item-wrapper').forEach(wrapper => {
            const hasChecked = wrapper.querySelector('.variant-checkbox:checked');
            const productId = wrapper.dataset.orderProductId;
            const cardCheckbox = document.getElementById(`multi-card-cb-${productId}`);
            const drawer = document.getElementById(`multi-drawer-${productId}`);

            if (hasChecked) {
                wrapper.classList.remove('hidden');
                wrapper.classList.remove('border-gray-200', 'bg-white');
                wrapper.classList.add('border-[#35B11E]', 'bg-[#fdf8f4]', 'shadow-sm');
                if (cardCheckbox) cardCheckbox.checked = true;
                if (drawer) drawer.classList.remove('hidden');

                // Highlight active variation button and update card info
                const activeBtn = wrapper.querySelector(`.l1-size-btn[data-var-id="${hasChecked.value}"]`);
                if (activeBtn) {
                    activeBtn.classList.add('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                    activeBtn.classList.remove('bg-white', 'text-gray-800', 'border-gray-300');
                    const priceEl = document.getElementById(`multi-card-price-${productId}`);
                    if (priceEl && activeBtn.dataset.price) {
                        priceEl.innerText = parseFloat(activeBtn.dataset.price).toFixed(2) + CURRENCY;
                    }
                    const thumbEl = document.getElementById(`multi-card-img-${productId}`);
                    if (thumbEl && activeBtn.dataset.img) {
                        thumbEl.src = activeBtn.dataset.img;
                    }
                }

                anyProductVisible = true;
            } else {
                wrapper.classList.add('hidden');
                wrapper.classList.remove('border-[#35B11E]', 'bg-[#fdf8f4]', 'shadow-sm');
                wrapper.classList.add('border-gray-200', 'bg-white');
                if (cardCheckbox) cardCheckbox.checked = false;
                if (drawer) drawer.classList.add('hidden');
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
        const isEligibleRule = !allFreeDelivery && checkFreeShippingEligibility(totalItemsCount, calculatedSubtotal);

        if (allFreeDelivery || isEligibleRule) {
            DELIVERY = 0;
            if (deliveryAreaSection) deliveryAreaSection.classList.add('hidden');
        } else {
            const currentArea = document.querySelector('input[name="shipping_area"]:checked')?.value || 'inside';
            DELIVERY = (currentArea === 'outside') ? DEFAULT_OUTSIDE_CHARGE : DEFAULT_INSIDE_CHARGE;
            if (deliveryAreaSection) deliveryAreaSection.classList.remove('hidden');
        }

        // Update Banner visual state
        const bannerEl = document.getElementById('landing-free-shipping-banner');
        const bannerTextEl = document.getElementById('landing-free-shipping-text');
        if (bannerEl) {
            if (allFreeDelivery) {
                bannerEl.classList.remove('hidden');
                bannerEl.className = "mb-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-2.5 text-emerald-800 text-xs sm:text-sm font-semibold shadow-xs";
                const icon = bannerEl.querySelector('i');
                if (icon) icon.className = "fa-solid fa-truck-fast text-emerald-600 text-base";
                if (bannerTextEl) bannerTextEl.innerText = FREE_SHIPPING_SUCCESS_TEXT;
            } else if (isEligibleRule) {
                bannerEl.classList.remove('hidden');
                bannerEl.className = "mb-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-2.5 text-emerald-800 text-xs sm:text-sm font-semibold shadow-xs";
                const icon = bannerEl.querySelector('i');
                if (icon) icon.className = "fa-solid fa-truck-fast text-emerald-600 text-base";
                if (bannerTextEl) bannerTextEl.innerText = GLOBAL_FREE_SHIPPING_TEXT;
            } else if (hasLandingRule && GLOBAL_FREE_SHIPPING_TEXT) {
                bannerEl.classList.remove('hidden');
                bannerEl.className = "mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2.5 text-amber-800 text-xs sm:text-sm font-medium";
                const icon = bannerEl.querySelector('i');
                if (icon) icon.className = "fa-solid fa-gift text-amber-600 text-sm";
                if (bannerTextEl) bannerTextEl.innerText = GLOBAL_FREE_SHIPPING_TEXT;
            } else {
                bannerEl.classList.add('hidden');
            }
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
            btn.innerHTML = `<span>${FORM_TRANS.out_of_stock_btn || 'স্টক আউট (পণ্যটি শেষ)'}</span>`;
        } else if (isEmptySelection) {
            btn.disabled = true;
            btn.className = "w-full bg-gray-400 cursor-not-allowed opacity-75 text-white font-bold py-5 rounded-xl text-2xl flex items-center justify-center gap-2";
            btn.innerHTML = `<span>${FORM_TRANS.select_product_btn || 'পণ্য নির্বাচন করুন'}</span>`;
        } else {
            btn.disabled = false;
            btn.className = "w-full bg-[#1f8a54] hover:bg-[#176840] cursor-pointer text-white font-bold py-5 rounded-xl text-2xl active:translate-y-1 transition-all flex items-center justify-center gap-2";
            btn.innerHTML = `<span id="btn-text">${FORM_TRANS.place_order || 'অর্ডার করুন'}</span> <span id="btn-total"></span>` + CURRENCY;
        }
    }

    let l1CardSelected = false;

    function toggleL1MainCard() {
        const container = document.querySelector('.l1-single-variant-container');
        const drawer = document.getElementById('l1-size-drawer');
        const checkboxInput = document.getElementById('l1-main-checkbox-input');
        if (!container || !drawer) return;

        // Clicking the card ensures it is selected and opens the drawer
        l1CardSelected = true;
        if (checkboxInput) checkboxInput.checked = true;

        container.classList.remove('border-gray-200', 'bg-white');
        container.classList.add('border-[#35B11E]', 'bg-[#fdf8f4]', 'shadow-sm');
        drawer.classList.remove('hidden');

        if (isMultipleAttribute) {
            const firstCb = document.querySelector('input.l1-internal-input.variant-checkbox');
            if (firstCb && !document.querySelector('input.l1-internal-input.variant-checkbox:checked')) {
                firstCb.checked = true;
                const btn = document.querySelector(`.l1-size-btn[data-var-id="${firstCb.value}"]`);
                if (btn) {
                    btn.classList.add('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                    btn.classList.remove('bg-white', 'text-gray-800', 'border-gray-300');
                }
            }
            onVariationCheckboxChange();
        } else {
            const checkedRadio = document.querySelector('input.l1-internal-input:checked') || document.querySelector('input.l1-internal-input');
            if (checkedRadio) {
                checkedRadio.checked = true;
                const btn = document.querySelector(`.l1-size-btn[data-var-id="${checkedRadio.value}"]`);
                if (btn) {
                    btn.classList.add('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                    btn.classList.remove('bg-white', 'text-gray-800', 'border-gray-300');
                }
                checkedRadio.dispatchEvent(new Event('change'));
            }
        }
    }

    function removeSingleVariationCard() {
        const container = document.querySelector('.l1-single-variant-container');
        const drawer = document.getElementById('l1-size-drawer');
        const checkboxInput = document.getElementById('l1-main-checkbox-input');
        if (!container || !drawer) return;

        l1CardSelected = false;
        if (checkboxInput) checkboxInput.checked = false;

        container.classList.remove('border-[#35B11E]', 'bg-[#fdf8f4]', 'shadow-sm');
        container.classList.add('border-gray-200', 'bg-white');
        drawer.classList.add('hidden');

        // Uncheck all variations
        document.querySelectorAll('input.l1-internal-input').forEach(r => r.checked = false);
        document.querySelectorAll('.l1-size-btn').forEach(b => {
            b.classList.remove('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
            b.classList.add('bg-white', 'text-gray-800', 'border-gray-300');
        });
        const selectedIdInput = document.getElementById('selected-variation-id');
        if (selectedIdInput) selectedIdInput.value = '';
        UNIT_PRICE = 0;
        renderSummaryItems([], isMultipleAttribute);
        updateBtnState(false, true);
        updateUI(0);
    }

    function selectL1VariationButton(varId, btn) {
        if (!l1CardSelected) {
            toggleL1MainCard();
        }

        const input = document.getElementById('l1_input_' + varId);
        if (!input) return;

        if (isMultipleAttribute) {
            // Toggle selection for multiple type
            input.checked = !input.checked;
            if (input.checked) {
                btn.classList.add('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                btn.classList.remove('bg-white', 'text-gray-800', 'border-gray-300');
            } else {
                btn.classList.remove('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                btn.classList.add('bg-white', 'text-gray-800', 'border-gray-300');
            }

            // Update card thumb and price if selected
            if (input.checked) {
                const price = btn.dataset.price;
                const img = btn.dataset.img;
                if (price) {
                    const priceEl = document.getElementById('l1-main-price');
                    if (priceEl) priceEl.innerText = parseFloat(price).toFixed(2) + CURRENCY;
                }
                if (img) {
                    const thumbEl = document.getElementById('l1-main-thumb');
                    if (thumbEl) thumbEl.src = img;
                }
            }

            onVariationCheckboxChange();
        } else {
            // Single selection (Radio)
            document.querySelectorAll('.l1-size-btn').forEach(b => {
                b.classList.remove('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                b.classList.add('bg-white', 'text-gray-800', 'border-gray-300');
            });
            btn.classList.add('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
            btn.classList.remove('bg-white', 'text-gray-800', 'border-gray-300');

            const price = btn.dataset.price;
            const img = btn.dataset.img;
            if (price) {
                const priceEl = document.getElementById('l1-main-price');
                if (priceEl) priceEl.innerText = parseFloat(price).toFixed(2) + CURRENCY;
            }
            if (img) {
                const thumbEl = document.getElementById('l1-main-thumb');
                if (thumbEl) thumbEl.src = img;
            }

            input.checked = true;
            input.dispatchEvent(new Event('change'));
        }
    }

    function toggleMultiProductCard(productId) {
        const wrapper = document.querySelector(`.order-product-item-wrapper[data-order-product-id="${productId}"]`);
        const drawer = document.getElementById(`multi-drawer-${productId}`);
        const checkbox = document.getElementById(`multi-card-cb-${productId}`);
        if (!wrapper) return;

        // Clicking the card ensures it is active and keeps options open
        if (checkbox) checkbox.checked = true;

        wrapper.classList.remove('border-gray-200', 'bg-white');
        wrapper.classList.add('border-[#35B11E]', 'bg-[#fdf8f4]', 'shadow-sm');
        if (drawer) drawer.classList.remove('hidden');

        // If it has a single product/variation input (no multiple variations drawer), check it directly
        const singleInput = wrapper.querySelector('#multi_prod_input_' + productId) || wrapper.querySelector('input.variant-checkbox:not(.l1-size-btn input)');
        if (singleInput && !drawer) {
            singleInput.checked = true;
        } else if (drawer) {
            // If it has variations, select the first available variation if none selected yet
            const firstVarInput = wrapper.querySelector('input.variant-checkbox');
            if (firstVarInput && !wrapper.querySelector('input.variant-checkbox:checked')) {
                firstVarInput.checked = true;
                const btn = wrapper.querySelector(`.l1-size-btn[data-var-id="${firstVarInput.value}"]`);
                if (btn) {
                    btn.classList.add('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                    btn.classList.remove('bg-white', 'text-gray-800', 'border-gray-300');
                }
            }
        }

        onVariationCheckboxChange();
    }

    function selectMultiProductVariationButton(productId, varId, btn) {
        const wrapper = document.querySelector(`.order-product-item-wrapper[data-order-product-id="${productId}"]`);
        const checkbox = document.getElementById(`multi-card-cb-${productId}`);
        const input = document.getElementById(`multi_var_input_${varId}`);
        if (!wrapper || !input) return;

        // Ensure parent card is active
        if (checkbox && !checkbox.checked) {
            checkbox.checked = true;
            wrapper.classList.remove('border-gray-200', 'bg-white');
            wrapper.classList.add('border-[#35B11E]', 'bg-[#fdf8f4]', 'shadow-sm');
            const drawer = document.getElementById(`multi-drawer-${productId}`);
            if (drawer) drawer.classList.remove('hidden');
        }

        // Toggle or single select based on product attribute category
        const isProdMultiple = wrapper.dataset.isMultiple === '1';
        if (isProdMultiple) {
            input.checked = !input.checked;
            if (input.checked) {
                btn.classList.add('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                btn.classList.remove('bg-white', 'text-gray-800', 'border-gray-300');
            } else {
                btn.classList.remove('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                btn.classList.add('bg-white', 'text-gray-800', 'border-gray-300');
                delete variationQuantities[varId];
            }
        } else {
            // Radio behavior for variations of this specific product
            wrapper.querySelectorAll(`.multi-size-btn-${productId}`).forEach(b => {
                b.classList.remove('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
                b.classList.add('bg-white', 'text-gray-800', 'border-gray-300');
            });
            wrapper.querySelectorAll('input.variant-checkbox').forEach(i => {
                i.checked = false;
                delete variationQuantities[i.value];
            });

            input.checked = true;
            btn.classList.add('bg-[#35B11E]', 'text-white', 'border-[#35B11E]', 'shadow-xs');
            btn.classList.remove('bg-white', 'text-gray-800', 'border-gray-300');
        }

        // Update card image and price
        const price = btn.dataset.price;
        const img = btn.dataset.img;
        if (price) {
            const priceEl = document.getElementById(`multi-card-price-${productId}`);
            if (priceEl) priceEl.innerText = parseFloat(price).toFixed(2) + CURRENCY;
        }
        if (img) {
            const thumbEl = document.getElementById(`multi-card-img-${productId}`);
            if (thumbEl) thumbEl.src = img;
        }

        onVariationCheckboxChange();
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
            const stockMsg = (FORM_TRANS.stock_out_alert || 'দুঃখিত! স্টকে আর পণ্য নেই (সর্বোচ্চ :count টি প্রযোজ্য)।').replace(':count', currentMaxStock);
            alert(stockMsg);
            return;
        }
        qty = Math.max(1, qty + delta);
        updateUI();
    }

    function updateUI(explicitSubtotal = null) {
        const sub = explicitSubtotal !== null ? explicitSubtotal : (qty * UNIT_PRICE);

        if (!isMultiProductMode && !isMultipleAttribute) {
            const isSingleFree = (typeof isFree !== 'undefined' && isFree) || ({{ $isFree ? 'true' : 'false' }});
            const deliveryAreaSection = document.getElementById('delivery-area-section');

            if (isSingleFree) {
                DELIVERY = 0;
                if (deliveryAreaSection) deliveryAreaSection.classList.add('hidden');
            } else {
                const currentArea = document.querySelector('input[name="shipping_area"]:checked')?.value || 'inside';
                DELIVERY = (currentArea === 'outside') ? DEFAULT_OUTSIDE_CHARGE : DEFAULT_INSIDE_CHARGE;
                if (deliveryAreaSection) deliveryAreaSection.classList.remove('hidden');
            }

            const bannerEl = document.getElementById('landing-free-shipping-banner');
            const bannerTextEl = document.getElementById('landing-free-shipping-text');
            if (bannerEl && isSingleFree) {
                bannerEl.className = "mb-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center gap-2.5 text-emerald-800 text-xs sm:text-sm font-semibold shadow-xs";
                const icon = bannerEl.querySelector('i');
                if (icon) icon.className = "fa-solid fa-truck-fast text-emerald-600 text-base";
                if (bannerTextEl) bannerTextEl.innerText = FREE_SHIPPING_SUCCESS_TEXT;
            }
        }

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
    let latestInvoiceUrl = "{{ session('invoice_url') ?? (session('order_id') ? route('order.invoice', session('order_id')) : '') }}";

    function showOrderSuccessModal(message, orderCode, thankyouUrl, invoiceUrl, orderId) {
        if (thankyouUrl) {
            latestThankyouUrl = thankyouUrl;
        }
        if (invoiceUrl) {
            latestInvoiceUrl = invoiceUrl;
        } else if (orderId) {
            latestInvoiceUrl = "/invoice/" + orderId;
        }
        const modal = document.getElementById('order-success-modal');
        if (modal) {
            const codeEl = document.getElementById('order-success-modal-code');
            if (codeEl) {
                if (orderCode) {
                    codeEl.innerText = (orderCode.startsWith('#') ? orderCode : '#' + orderCode);
                }
            }
            const invoiceBtn = document.getElementById('order-success-invoice-btn');
            if (invoiceBtn && latestInvoiceUrl) {
                invoiceBtn.href = latestInvoiceUrl;
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

        // Bangladesh Phone Number Real-Time Validation
        function validateBdPhone(raw) {
            let digits = (raw || '').replace(/[^0-9]/g, '');
            if (digits.startsWith('8801')) {
                digits = digits.substring(2);
            }
            if (!digits) {
                return { valid: false, message: 'Invalid phone number' };
            }
            const prefix = digits.substring(0, 3);
            const validPrefixes = ['013', '014', '015', '016', '017', '018', '019'];
            if (digits.length !== 11 || !validPrefixes.includes(prefix)) {
                return { valid: false, message: 'Invalid phone number' };
            }
            return { valid: true, cleanPhone: digits };
        }

        function checkLandingPhoneValidity() {
            if (!phoneInput) return true;
            const res = validateBdPhone(phoneInput.value);
            const errEl = document.getElementById('landing-phone-error');
            const errTxt = document.getElementById('landing-phone-error-text');
            const submitBtn = document.getElementById('submit-order-btn') || (form && form.querySelector('button[type="submit"]'));

            if (!phoneInput.value.trim()) {
                if (errEl) errEl.classList.add('hidden');
                phoneInput.classList.remove('!border-red-500');
                if (submitBtn && submitBtn.dataset.stockDisabled !== 'true') {
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
                return false;
            }

            if (!res.valid) {
                if (errEl && errTxt) {
                    errTxt.innerText = res.message;
                    errEl.classList.remove('hidden');
                }
                phoneInput.classList.add('!border-red-500');
                if (submitBtn && submitBtn.dataset.stockDisabled !== 'true') {
                    submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
                return false;
            } else {
                if (errEl) errEl.classList.add('hidden');
                phoneInput.classList.remove('!border-red-500');
                if (submitBtn && submitBtn.dataset.stockDisabled !== 'true') {
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                return true;
            }
        }

        // Initialize state on load
        checkLandingPhoneValidity();

        window.saveLandingDraft = function () {
            if (!phoneInput || isPreview || currentVariationOutOfStock || isSubmittingOrder) return;
            const valCheck = validateBdPhone(phoneInput.value);
            if (!valCheck.valid) return;
            const phone = valCheck.cleanPhone;

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

        let fbPixelUserUpdated = false;
        function updateMetaPixelUserData() {
            if (fbPixelUserUpdated || typeof fbq !== 'function') return;
            const pVal = phoneInput ? phoneInput.value.replace(/[^0-9]/g, '') : '';
            if (pVal.length >= 11) {
                fbPixelUserUpdated = true;
                const normPhone = (pVal.length === 11 && pVal.startsWith('01')) ? ('88' + pVal) : pVal;
                const nVal = nameInput ? nameInput.value.trim() : '';
                const uData = { ph: normPhone };
                if (nVal) {
                    const nParts = nVal.split(' ');
                    uData.fn = nParts[0].toLowerCase().replace(/[^a-z0-9]/g, '');
                    if (nParts.length > 1) {
                        uData.ln = nParts[nParts.length - 1].toLowerCase().replace(/[^a-z0-9]/g, '');
                    }
                }
                try {
                    fbq('set', 'user_data', uData);
                    fbq('track', 'InitiateCheckout', {
                        content_name: '{{ addslashes($mainProduct->title ?? ($landing->title ?? "Order")) }}',
                        content_ids: ['{{ $mainProduct->id ?? "" }}'],
                        content_type: 'product',
                        currency: 'BDT'
                    });
                } catch(e) {}
            }
        }

        if (phoneInput) {
            phoneInput.addEventListener('blur', function () {
                checkLandingPhoneValidity();
                if (validateBdPhone(this.value).valid) {
                    updateMetaPixelUserData();
                    if (!isSubmittingOrder) window.saveLandingDraft();
                }
            });
            phoneInput.addEventListener('change', function () {
                checkLandingPhoneValidity();
                if (validateBdPhone(this.value).valid) {
                    updateMetaPixelUserData();
                    if (!isSubmittingOrder) window.saveLandingDraft();
                }
            });
            phoneInput.addEventListener('input', function () {
                checkLandingPhoneValidity();
                if (validateBdPhone(this.value).valid) {
                    updateMetaPixelUserData();
                    scheduleDraftSave();
                }
            });
        }

        if (nameInput) {
            nameInput.addEventListener('blur', function() {
                updateMetaPixelUserData();
                scheduleDraftSave();
            });
        }
        if (addressInput) {
            addressInput.addEventListener('blur', scheduleDraftSave);
        }
    });

    const landingForm = document.getElementById('landing-order-form');
    if (landingForm) {
        landingForm.onsubmit = function (e) {
            const phoneField = document.getElementById('phone');
            if (phoneField) {
                const digits = phoneField.value.replace(/[^0-9]/g, '');
                const cleanPhone = digits.startsWith('8801') ? digits.substring(2) : digits;
                const validPrefixes = ['013', '014', '015', '016', '017', '018', '019'];
                if (cleanPhone.length !== 11 || !validPrefixes.includes(cleanPhone.substring(0, 3))) {
                    e.preventDefault();
                    const errEl = document.getElementById('landing-phone-error');
                    const errTxt = document.getElementById('landing-phone-error-text');
                    if (errEl && errTxt) {
                        errTxt.innerText = 'Invalid phone number';
                        errEl.classList.remove('hidden');
                    }
                    phoneField.focus();
                    phoneField.classList.add('!border-red-500');
                    return false;
                }
            }
            if (isPreview) {
                e.preventDefault();
                showPreviewAlert();
                return false;
            }
            if (currentVariationOutOfStock) {
                e.preventDefault();
                showOrderErrorModal(FORM_TRANS.selected_item_out_of_stock || 'দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।');
                return false;
            }
            if (isMultiProductMode) {
                const checkedAny = document.querySelector('.variant-checkbox:checked');
                if (!checkedAny) {
                    e.preventDefault();
                    alert(FORM_TRANS.select_at_least_one || 'দয়া করে অন্তত একটি পণ্য বা ভ্যারিয়েন্ট যোগ করুন!');
                    return false;
                }
            } else if (isVarProduct && !document.getElementById('selected-variation-id').value) {
                e.preventDefault();
                alert(FORM_TRANS.select_product_prompt || 'দয়া করে পণ্য সিলেক্ট করুন!');
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
                    </svg> ${FORM_TRANS.processing || 'প্রসেসিং...'}
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
                    if (data.redirect_url && data.already_placed) {
                        window.location.href = data.redirect_url;
                        return;
                    }
                    if (data.requires_otp) {
                        openCheckoutOtpModal(data.order_id, data.phone, function(otpSuccessData) {
                            closeCheckoutOtpModal();

                            const orderValue = parseFloat(document.getElementById('summary-total')?.innerText.replace(/[^0-9.]/g, '') || 0);
                            const finalOrderId = otpSuccessData.order_id || data.order_id || '';

                            if (typeof fbq === 'function') {
                                const pVal = phoneInput ? phoneInput.value.replace(/[^0-9]/g, '') : '';
                                const normPhone = (pVal.length === 11 && pVal.startsWith('01')) ? ('88' + pVal) : pVal;
                                const nVal = nameInput ? nameInput.value.trim() : '';
                                const uData = {};
                                if (normPhone) uData.ph = normPhone;
                                if (nVal) {
                                    const nParts = nVal.split(' ');
                                    uData.fn = nParts[0].toLowerCase().replace(/[^a-z0-9]/g, '');
                                    if (nParts.length > 1) {
                                        uData.ln = nParts[nParts.length - 1].toLowerCase().replace(/[^a-z0-9]/g, '');
                                    }
                                }
                                if (Object.keys(uData).length > 0) {
                                    try { fbq('set', 'user_data', uData); } catch(e){}
                                }
                                fbq('track', 'Purchase', {
                                    content_name: '{{ addslashes($mainProduct->title ?? ($landing->title ?? "Order")) }}',
                                    content_ids: ['{{ $mainProduct->id ?? "" }}'],
                                    content_type: 'product',
                                    value: orderValue,
                                    currency: 'BDT'
                                }, finalOrderId ? { eventID: 'ORDER_' + finalOrderId } : undefined);
                            }

                            if (typeof ttq !== 'undefined' && typeof ttq.track === 'function') {
                                ttq.track('CompletePayment', {
                                    content_id: '{{ $mainProduct->id ?? "" }}',
                                    content_type: 'product',
                                    value: orderValue,
                                    currency: 'BDT'
                                }, finalOrderId ? { event_id: 'ORDER_' + finalOrderId } : undefined);
                            }

                            showOrderSuccessModal(otpSuccessData.message || data.message, otpSuccessData.order_code || data.order_code, otpSuccessData.thankyou_url || data.thankyou_url, otpSuccessData.invoice_url || data.invoice_url, finalOrderId);
                            try {
                                landingForm.reset();
                                qty = 1;
                                if (typeof updateUI === 'function') {
                                    updateUI();
                                }
                            } catch (e) {
                                console.warn("Reset error:", e);
                            }
                        });
                        return;
                    }

                    const orderValue = parseFloat(document.getElementById('summary-total')?.innerText.replace(/[^0-9.]/g, '') || 0);
                    const orderId = data.order_id || '';

                    if (data.instant_purchase !== false) {
                        if (typeof fbq === 'function') {
                            const pVal = phoneInput ? phoneInput.value.replace(/[^0-9]/g, '') : '';
                            const normPhone = (pVal.length === 11 && pVal.startsWith('01')) ? ('88' + pVal) : pVal;
                            const nVal = nameInput ? nameInput.value.trim() : '';
                            const uData = {};
                            if (normPhone) uData.ph = normPhone;
                            if (nVal) {
                                const nParts = nVal.split(' ');
                                uData.fn = nParts[0].toLowerCase().replace(/[^a-z0-9]/g, '');
                                if (nParts.length > 1) {
                                    uData.ln = nParts[nParts.length - 1].toLowerCase().replace(/[^a-z0-9]/g, '');
                                }
                            }
                            if (Object.keys(uData).length > 0) {
                                try { fbq('set', 'user_data', uData); } catch(e){}
                            }
                            fbq('track', 'Purchase', {
                                content_name: '{{ addslashes($mainProduct->title ?? ($landing->title ?? "Order")) }}',
                                content_ids: ['{{ $mainProduct->id ?? "" }}'],
                                content_type: 'product',
                                value: orderValue,
                                currency: 'BDT'
                            }, orderId ? { eventID: 'ORDER_' + orderId } : undefined);
                        }

                        if (typeof ttq !== 'undefined' && typeof ttq.track === 'function') {
                            ttq.track('CompletePayment', {
                                content_id: '{{ $mainProduct->id ?? "" }}',
                                content_type: 'product',
                                value: orderValue,
                                currency: 'BDT'
                            }, orderId ? { event_id: 'ORDER_' + orderId } : undefined);
                        }
                    }

                    showOrderSuccessModal(data.message, data.order_code, data.thankyou_url, data.invoice_url, data.order_id);
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
                    let msg = data.message || (FORM_TRANS.order_failed_retry || 'অর্ডার সম্পন্ন করতে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।');
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
                showOrderErrorModal(FORM_TRANS.server_error_retry || 'সার্ভারে সমস্যা হয়েছে। দয়া করে আবার চেষ্টা করুন।');
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

    // Enforce browser autocomplete / address autofill disable on Landing Order Form
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('landing-order-form');
        if (!form) return;

        form.setAttribute('autocomplete', 'off');
        const inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="radio"]):not([type="checkbox"]):not([type="submit"]), textarea');
        inputs.forEach(el => {
            el.setAttribute('autocomplete', 'off');
            el.setAttribute('autocorrect', 'off');
            el.setAttribute('autocapitalize', 'off');
            el.setAttribute('spellcheck', 'false');
            el.setAttribute('data-lpignore', 'true');
            el.setAttribute('data-form-type', 'other');

            const unlock = () => { el.removeAttribute('readonly'); };
            el.addEventListener('focus', unlock);
            el.addEventListener('pointerdown', unlock);
            el.addEventListener('mousedown', unlock);
            el.addEventListener('touchstart', unlock);
        });

        form.addEventListener('submit', function() {
            inputs.forEach(el => el.removeAttribute('readonly'));
        });
    });
</script>
