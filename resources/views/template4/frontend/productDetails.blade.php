@extends('template4.layouts.front')
@section('meta')
    @include('components.meta-info.ecommerce-meta.product-details-meta', ['setup' => $setup])
@endsection
@section('content')
    @php
        // উইশলিস্ট চেক
        $isWishlisted = false;
        if (auth('customer')->check()) {
            $isWishlisted = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
                ->where('product_id', $product->id)
                ->exists();
        }
    @endphp

    <section class="bg-[#F9F9F9] py-2">
        <!-- Dynamic Breadcrumbs -->
        <nav aria-label="Breadcrumb"
            class="container mx-auto px-4 flex   items-center pt-2 md:pt-4 gap-1 md:gap-2 text-xs sm:text-sm md:text-base lg:text-lg mb-4 md:mb-6">

            <a href="{{ route('home') }}" class="text-[var(--primary-color)] hover:text-[#52166d] transition font-medium">Home</a>

            @if(isset($breadcrumb) && count($breadcrumb) > 0)
                @foreach($breadcrumb as $item)
                    <span class="text-gray-400">/</span>
                    <a href="{{ route('category.products', $item['slug']) }}"
                        class="text-[var(--primary-color)] hover:text-[#52166d] transition font-medium">
                        {{ $item['name'] }}
                    </a>
                @endforeach
            @endif

            <span class="text-gray-400">/</span>
            <span class="text-gray-500 font-normal truncate max-w-[200px] md:max-w-none">{{ $product->title }}</span>
        </nav>
    </section>

    <section class="bg-white">
        <div class="container mx-auto px-4 py-4 md:py-8">
            <!-- MAIN PRODUCT GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 md:gap-8 items-start pb-8 md:pb-16">

                <!-- LEFT COLUMN (Gallery) -->
                <div class="lg:col-span-7 flex flex-col md:flex-row gap-3 md:gap-4">
                    <!-- Thumbnails -->
                    <div
                        class="flex md:flex-col gap-2 overflow-x-auto md:overflow-y-auto shrink-0 order-2 md:order-1 md:w-20 lg:w-24 pb-2 md:pb-0 no-scrollbar">

                        <!-- Thumbnails (Updated to show all images) -->
                        <div id="thumbnail-container"
                            class="flex md:flex-col gap-2 overflow-x-auto md:overflow-y-auto shrink-0 order-2 md:order-1 md:w-20 lg:w-24 pb-2 md:pb-0 no-scrollbar">
                            @foreach ($allProductImages as $imgUrl)
                                <button aria-label="View product image"
                                    class="thumb-btn border {{ $loop->first ? 'border-2 border-[var(--primary-color)]' : 'border-gray-200' }} p-0.5 rounded overflow-hidden w-16 h-16 md:w-full md:h-auto aspect-square shrink-0"
                                    onclick="changeImage('{{ $imgUrl }}', this)">
                                    <img src="{{ $imgUrl }}" class="w-full h-full object-cover" alt="Product thumbnail" />
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Main Image Box -->
                    <div
                        class="relative flex-1 border border-gray-100 rounded overflow-hidden order-1 md:order-2  ">
                        <img id="mainImage" src="{{ $product->thumbnail_url ?? '' }}" loading="lazy" height="" width=""
                            alt="main image" class=" m-auto object-contain transition-all duration-500" />

                        <!-- Wishlist Button -->
                        <button onclick="toggleWishlist({{ $product->id }})" type="button" class="absolute top-3 left-3 md:top-4 md:left-4 p-2 rounded-full shadow-md transition-all active:scale-90 cursor-pointer z-10
                                {{ $isWishlisted ? 'bg-red-500 text-white' : 'bg-white text-[var(--primary-color)]' }}">

                            <i
                                class="wish-icon-{{ $product->id }} {{ $isWishlisted ? 'fa-solid fa-heart' : 'fa-regular fa-heart' }} text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- RIGHT COLUMN (Info) -->
                <div class="lg:col-span-5 flex flex-col gap-y-4 md:gap-y-6">
                    <div>
                        <h1 class="text-xl md:text-2xl lg:text-3xl font-normal text-gray-900 leading-tight">
                            {{ $product->title }}
                        </h1>

                        <p class="text-gray-500 mt-1 text-lg font-medium pt-2 ">
                            Brand Name: <span
                                class="text-[var(--primary-color)] font-semibold">{{ $product->brand->name ?? 'No Brand' }}</span>
                        </p>

                        <div class="flex items-baseline gap-2 md:gap-3 pt-3 md:pt-4">
                            <span id="sale-price" class="text-2xl md:text-3xl font-bold text-[var(--primary-color)]">
                                @if(($setup->currency_position ?? 'left') == 'left')
                                    {{ $setup->currency }} {{ number_format($product->display_price_data->sale_price) }}
                                @else
                                    {{ number_format($product->display_price_data->sale_price) }} {{ $setup->currency }}
                                @endif
                            </span>

                            @if ($product->display_price_data->regular_price > $product->display_price_data->sale_price)
                                <span id="regular-price" class="text-lg md:text-xl text-gray-400 line-through">
                                    @if(($setup->currency_position ?? 'left') == 'left')
                                        {{ $setup->currency }} {{ number_format($product->display_price_data->regular_price) }}
                                    @else
                                        {{ number_format($product->display_price_data->regular_price) }} {{ $setup->currency }}
                                    @endif
                                </span>
                            @endif
                        </div>

                        <hr class="my-2 md:my-3 border-gray-200" />

                        <!-- Dynamic Variations Container (বিকাশের মতো ভ্যারিয়েশন লজিক এখানে আসবে) -->
                        @if ($product->type === 'variation')
                            <div id="dynamic-attributes-container" class="mb-5"></div>
                        @endif

                        <!-- Short Description / Features List -->
                        <div class="space-y-3 md:space-y-4 prose prose-sm max-w-none text-gray-700">
                            {!! $product->short_description !!}
                        </div>

                        <hr class="my-2 md:my-3 border-gray-200" />
                    </div>

                    <div>
                        <!-- Quantity & Action Buttons -->
                        <div class="flex flex-col xl:flex-row items-start xl:items-center gap-4 md:gap-6">
                            <div class="flex items-center gap-3 md:gap-4">
                                <span class="text-[#0f172a] font-bold text-base md:text-lg select-none">Quantity:</span>
                                <div class="flex items-center gap-3 md:gap-4">
                                    <button onclick="changeQty(-1)"
                                        class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center border border-slate-400 rounded-lg hover:bg-gray-50 transition text-gray-700 text-xl font-normal">-</button>
                                    <span id="qty-value"
                                        class="w-4 md:w-6 text-center text-base md:text-lg font-bold text-[#0f172a]">1</span>
                                    <button onclick="changeQty(1)"
                                        class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center border border-[var(--primary-color)] rounded-lg hover:bg-purple-50 transition text-[var(--primary-color)] text-xl font-bold">+</button>
                                </div>
                            </div>

                            <div class="flex-1 flex flex-col sm:flex-row gap-3 md:gap-4 w-full">
                                <!-- Add to Cart Button -->
                                <button onclick="handleAddToCart()"
                                    {{ ($product->manage_stock && $product->available_stock <= 0) ? 'disabled' : '' }}
                                    class="flex-1 flex items-center justify-center gap-2 border border-gray-200 text-[#0f172a] font-bold py-2.5 rounded-2xl hover:bg-gray-50 transition shadow-sm disabled:opacity-40 disabled:cursor-not-allowed">
                                    Add to Cart
                                </button>

                                <!-- Buy Now Button -->
                                <button onclick="handleAddToCart(true)"
                                    {{ ($product->manage_stock && $product->available_stock <= 0) ? 'disabled' : '' }}
                                    class="flex-1 flex items-center justify-center gap-2 primary-bg hover:bg-[#52166d] text-white font-bold py-2.5 rounded-2xl transition shadow-sm disabled:opacity-40 disabled:cursor-not-allowed">
                                    Buy Now
                                </button>
                            </div>
                        </div>
                        <p class="text-sm mt-4 {{ (!$product->manage_stock || $product->available_stock > 0) ? 'text-green-600' : 'text-red-500' }}">
                            <i class="fas {{ (!$product->manage_stock || $product->available_stock > 0) ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                            {{ !$product->manage_stock ? 'In Stock' : ($product->available_stock > 0 ? $product->available_stock . ' in stock' : 'Out of stock') }}
                        </p>

                        <p class="text-sm md:text-base text-gray-500 mt-5 md:mt-6 leading-relaxed">
                            <span class="font-bold text-gray-700 text-xs md:text-sm">Categories:</span>
                            {{ $product->mega_categories->pluck('name')->implode(', ') }}
                        </p>

                        <!-- Contact Help -->
                        <div class="mt-3 md:mt-4 border border-gray-200 rounded-xl p-4 md:p-5 bg-white">
                            <p class="font-semibold text-gray-800 text-sm md:text-base mb-3">
                                Have a question?
                            </p>

                            <div class="flex flex-wrap gap-2 md:gap-3">
                                <!-- Call -->
                                <a href="tel:{{ $setup->phone }}"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg text-gray-700 text-sm font-medium hover:border-gray-300 hover:bg-gray-50 transition-all duration-200">
                                    <i class="fa-solid fa-phone text-xs"></i>
                                    <span>Call: {{ $setup->phone }}</span>
                                </a>

                                <!-- WhatsApp -->
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $setup->phone) }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 border border-gray-200 rounded-lg text-gray-700 text-sm font-medium hover:border-green-300 hover:bg-green-50 hover:text-green-600 transition-all duration-200">
                                    <i class="fa-brands fa-whatsapp text-base"></i>
                                    <span>WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ─── BOTTOM SECTION: ALL DETAILS SECTION (Stacked with Sticky Nav) ─── -->
    <section id="product-listing-page" class="container mx-auto px-4 py-4">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 md:gap-8 items-start">

            <!-- LEFT COLUMN: All Sections -->
            <div class="lg:col-span-3 flex flex-col gap-6 md:gap-8">

                <!-- Sticky Navigation Bar (৩টি ট্যাব) -->
                <div class="sticky top-0 z-30 py-2 md:py-4 bg-[#F9F9F9]">
                    <div class="flex gap-2 sm:gap-3 overflow-x-auto no-scrollbar" role="tablist">
                        <button onclick="scrollToSection('section-description', this)" role="tab" aria-selected="true"
                            aria-controls="section-description" {}
                            class="tab-nav-btn flex-1 bg-white text-[var(--primary-color)] font-bold text-sm md:text-lg py-2.5 px-4 text-center rounded-lg shadow-sm border-2 border-[var(--primary-color)]">
                            Description
                        </button>
                        <button onclick="scrollToSection('section-features', this)" role="tab" aria-selected="false"
                            aria-controls="section-features"
                            class="tab-nav-btn flex-1 bg-white text-gray-500 hover:bg-gray-100 font-bold text-sm md:text-lg py-2.5 px-4 text-center rounded-lg shadow-sm border-2 border-transparent">
                            Features
                        </button>
                        <button onclick="scrollToSection('section-specifications', this)" role="tab" aria-selected="false"
                            aria-controls="section-specifications"
                            class="tab-nav-btn flex-1 bg-white text-gray-500 hover:bg-gray-100 font-bold text-sm md:text-lg py-2.5 px-4 text-center rounded-lg shadow-sm border-2 border-transparent">
                            Specifications
                        </button>
                    </div>
                </div>

                <!-- Content Area: All Sections Visible simultaneously -->
                <div class="flex flex-col gap-6 md:gap-8">

                    <!-- 1. Description Content (scroll-mt-32 দিয়ে উপরে জায়গা রাখা হয়েছে) -->
                    <div id="section-description"
                        class="bg-white p-4 sm:p-6 border border-gray-100 rounded shadow-sm scroll-mt-32">
                        <h2
                            class="text-lg md:text-xl font-bold text-gray-900 border-l-4 border-[var(--primary-color)] pl-3 mb-4">
                            Product Description
                        </h2>
                        <div class="text-sm md:text-base text-gray-700 leading-relaxed prose max-w-none">
                            {!! $product->full_description !!}
                        </div>
                    </div>

                    <!-- 2. Features Content (short_description থেকে ডাটা নেওয়া হয়েছে) -->
                    <div id="section-features"
                        class="bg-white p-4 sm:p-6 border border-gray-100 rounded shadow-sm scroll-mt-32">
                        <h2
                            class="text-lg md:text-xl font-bold text-gray-900 border-l-4 border-[var(--primary-color)] pl-3 mb-4">
                            Product Features
                        </h2>
                        <div class="text-sm md:text-base text-gray-700 leading-relaxed prose max-w-none">
                            {!! $product->short_description !!}
                        </div>
                    </div>

                    <!-- 3. Specifications Content -->
                    <div id="section-specifications"
                        class="bg-white p-4 sm:p-6 border border-gray-100 rounded shadow-sm scroll-mt-32">
                        <h2
                            class="text-lg md:text-xl font-bold text-gray-900 border-l-4 border-[var(--primary-color)] pl-3 mb-4">
                            Product Specifications
                        </h2>
                        <div class="grid grid-cols-1 gap-y-3 text-sm md:text-base text-gray-800">
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500 font-medium">Brand:</span>
                                <span class="font-bold">{{ $product->brand->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100">
                                <span class="text-gray-500 font-medium">SKU:</span>
                                <span
                                    class="font-mono font-bold">{{ is_array($product->sku_code) ? implode(', ', $product->sku_code) : $product->sku_code }}</span>
                            </div>
                            @php
                                $grouped = [];
                                if ($product->type === 'variation') {
                                    foreach ($product->variations as $variation) {
                                        foreach ($variation->attributes as $attr) {
                                            $groupName = $attr->attributeGroup->name ?? null;
                                            $valName = $attr->attributeValue->name ?? null;

                                            if ($groupName && $valName) {
                                                if (!isset($grouped[$groupName])) {
                                                    $grouped[$groupName] = [];
                                                }

                                                if (!in_array($valName, $grouped[$groupName])) {
                                                    $grouped[$groupName][] = $valName;
                                                }
                                            }
                                        }
                                    }
                                }
                            @endphp
                            @foreach ($grouped as $groupName => $values)
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-500 font-medium">{{ $groupName }}:</span>
                                    <span class="font-bold">{{ implode(', ', array_unique($values)) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Related Products (Sticky Sidebar) -->
            <aside class="lg:col-span-2 flex flex-col gap-6 sticky top-24">
                <div class="bg-white border border-gray-100 rounded-lg overflow-hidden shadow-sm">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900 text-lg">Related Products</h3>
                    </div>
                   <div class="divide-y divide-gray-200">
            @php $isL = ($setup->currency_position ?? 'left') == 'left'; @endphp

            @foreach ($relatedProducts->take(5) as $rel)
                <a href="{{ route('product.details', $rel->slug) }}"
                    class="p-3 flex items-center gap-3 hover:bg-gray-50 transition">
                    <img src="{{ $rel->thumbnail_url ?? '' }}" loading="lazy" height="" width=""
                        alt="related product image" class="w-16 h-16 object-cover rounded-lg shrink-0" />
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $rel->title }}</p>
                        <p class="text-xs font-bold text-[var(--primary-color)] mt-1">
                            {{ $isL ? $setup->currency : '' }} {{ number_format($rel->sale_price) }} {{ !$isL ? $setup->currency : '' }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
                </div>
            </aside>
        </div>
    </section>

    {{-- Hidden input for JS --}}
    <input type="hidden" id="main-qty" value="1">
    <input type="hidden" id="selected-variation-id" value="">
@endsection

@push('scripts')
    <script>

        function changeImage(src, btn) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumb-btn').forEach(b => {
                b.classList.remove('border-2', 'border-[var(--primary-color)]');
                b.classList.add('border-gray-200');
            });
            if (btn) {
                btn.classList.add('border-2', 'border-[var(--primary-color)]');
                btn.classList.remove('border-gray-200');
            }
        }
        function changeQty(amount) {
            let qtyVal = document.getElementById('qty-value');
            let mainQty = document.getElementById('main-qty');
            let currentQty = parseInt(qtyVal.innerText);

            let newQty = currentQty + amount;

            if (newQty < 1) {
                newQty = 1;
            }

            qtyVal.innerText = newQty;
            mainQty.value = newQty;
        }

        const attributeGroups = @json($attributeGroups ?? []);
        const allVariations = @json($formattedVariations ?? []);
        const valueImages = @json($valueImages ?? []);
        const defaultGalleries = @json($defaultGalleries ?? []);
        const groupCategories = @json($groupCategories ?? []);

        let activeFilters = {};
        attributeGroups.forEach(group => activeFilters[group] = null);
        let finalSelectedVariationIds = [];

        function updateGalleryThumbnails(images) {
            const container = document.getElementById('thumbnail-container');
            if (!container) return;

            container.innerHTML = '';
            images.forEach((imgUrl, index) => {
                const borderClass = (index === 0) ? 'border-2 border-[var(--primary-color)]' : 'border-gray-200';
                container.innerHTML += `
                        <button class="thumb-btn border ${borderClass} p-0.5 rounded overflow-hidden w-16 h-16 md:w-full md:h-auto aspect-square shrink-0"
                            onclick="changeImage('${imgUrl}', this)">
                            <img src="${imgUrl}" onerror="this.src='{{ asset('./images/template1/frontend/default.webp') }}'" class="w-full h-full object-cover" />
                        </button>`;
            });
        }

        function renderAttributes() {
            const container = document.getElementById('dynamic-attributes-container');
            if (!container) return;
            container.innerHTML = '';

            let currentlyValidVariations = allVariations;

            for (let i = 0; i < attributeGroups.length; i++) {
                const groupName = attributeGroups[i];
                const isLastGroup = (i === attributeGroups.length - 1);

                let availableValues = {};
                currentlyValidVariations.forEach(v => {
                    if (v.attributes[groupName]) availableValues[v.attributes[groupName].id] = v.attributes[groupName].name;
                });

                let groupHtml = `<div class="mb-4"><h3 class="text-lg font-bold text-gray-700 mb-2">Choose ${groupName}:</h3><div class="flex flex-wrap gap-2">`;

                for (const [valId, valName] of Object.entries(availableValues)) {
                    const isFilterActive = (activeFilters[groupName] == valId);
                    const isVariationSelected = checkIsSelected(groupName, valId);

                    const activeClass = (isFilterActive || isVariationSelected) ?
                        'border-[var(--primary-color)] bg-orange-50 text-[var(--primary-color)]' :
                        'border-gray-200 bg-white text-gray-700';

                    // ভ্যারিয়েশন বাটনে ইমেজ দেখানোর লজিক
                    let btnContent = valueImages[valId]
                        ? `<img src="${valueImages[valId]}" class="w-8 h-8 rounded object-cover mr-2 inline-block"> ${valName}`
                        : valName;

                    groupHtml += `<button type="button" onclick="handleSelection('${groupName}', ${valId}, ${isLastGroup})" class="flex items-center px-4 py-2 rounded-lg border text-lg font-bold transition-all ${activeClass}">${btnContent}</button>`;
                }
                groupHtml += `</div></div>`;
                container.innerHTML += groupHtml;

                if (!activeFilters[groupName]) break;
                currentlyValidVariations = currentlyValidVariations.filter(v => v.attributes[groupName].id == activeFilters[groupName]);
            }
            document.getElementById('selected-variation-id').value = finalSelectedVariationIds.join(',');
        }

        
        
        function updatePriceDisplay(salePrice, regularPrice) {
            const saleEl = document.getElementById('sale-price');
            const regularEl = document.getElementById('regular-price');
            const badgeEl = document.getElementById('discount-badge');
            const currency = "{{ $setup->currency }}";
            const currencyPosition = "{{ $setup->currency_position ?? 'left' }}";

            const formattedSale = Math.round(salePrice).toLocaleString();
            if (saleEl) {
                saleEl.innerText = (currencyPosition === 'left')
                    ? `${currency} ${formattedSale}`
                    : `${formattedSale} ${currency}`;
            }

            if (regularEl) {
                if (regularPrice && regularPrice > salePrice) {
                    const formattedRegular = Math.round(regularPrice).toLocaleString();
                    regularEl.innerText = (currencyPosition === 'left')
                        ? `${currency} ${formattedRegular}`
                        : `${formattedRegular} ${currency}`;
                    regularEl.classList.remove('hidden');
                } else {
                    if(regularEl) regularEl.classList.add('hidden');
                }
            }

            if (badgeEl) {
                if (regularPrice && regularPrice > salePrice) {
                    const percent = Math.round(((regularPrice - salePrice) / regularPrice) * 100);
                    badgeEl.innerText = percent + '% OFF';
                    badgeEl.classList.remove('hidden');
                } else {
                    if(badgeEl) badgeEl.classList.add('hidden');
                }
            }
        }

        function recalculateSelectedPrice() {
            if (finalSelectedVariationIds.length === 0) {
                updatePriceDisplay(
                    {{ $product->display_price_data->sale_price ?? 0 }},
                    {{ $product->display_price_data->regular_price ?? 0 }}
                );
                return;
            }

            let totalSale = 0;
            let totalRegular = 0;

            finalSelectedVariationIds.forEach(id => {
                const v = allVariations.find(v => v.id === id);
                if (v) {
                    totalSale += parseFloat(v.price);
                    totalRegular += parseFloat(v.regular_price || v.price);
                }
            });

            updatePriceDisplay(totalSale, totalRegular);
        }

        function updateSkuDisplay() {
            const skuEl = document.getElementById('product-sku');
            const tabSkuEl = document.getElementById('tab-sku');
            
            if (finalSelectedVariationIds.length === 0) {
                const allSkus = allVariations.map(v => v.sku).filter(sku => sku);
                if (allSkus.length > 0) {
                    const text = [...new Set(allSkus)].join(", ");
                    if(skuEl) skuEl.innerText = text;
                    if(tabSkuEl) tabSkuEl.innerText = text;
                }
                return;
            }
            
            let selectedSkus = [];
            finalSelectedVariationIds.forEach(id => {
                const v = allVariations.find(v => v.id === id);
                if (v && v.sku) selectedSkus.push(v.sku);
            });
            
            if (selectedSkus.length > 0) {
                const text = [...new Set(selectedSkus)].join(", ");
                if(skuEl) skuEl.innerText = text;
                if(tabSkuEl) tabSkuEl.innerText = text;
            }
        }

        function handleSelection(group, valId, isLastGroup) {
            if (!isLastGroup) {
                activeFilters[group] = (activeFilters[group] == valId) ? null : valId;
                let idx = attributeGroups.indexOf(group);
                for (let i = idx + 1; i < attributeGroups.length; i++) activeFilters[attributeGroups[i]] = null;
                if (finalSelectedVariationIds.length === 0) {
                    updateGalleryThumbnails(defaultGalleries);
                    recalculateSelectedPrice();
                }
            } else {
                activeFilters[group] = valId;
                let matched = allVariations.find(v => {
                    return attributeGroups.every(g => v.attributes[g].id == activeFilters[g]);
                });

                if (matched) {
                    let isSingleChoice = false;
                    for (let gName in matched.attributes) {
                        if (groupCategories[gName] === 'single') { isSingleChoice = true; break; }
                    }

                    if (isSingleChoice) {
                        finalSelectedVariationIds = [matched.id];
                        let combined = [...(matched.galleries || []), ...defaultGalleries];
                        updateGalleryThumbnails([...new Set(combined)]);
                        if (matched.main_image) document.getElementById('mainImage').src = matched.main_image;
                    } else {
                        const index = finalSelectedVariationIds.indexOf(matched.id);
                        if (index > -1) {
                            finalSelectedVariationIds.splice(index, 1);
                            if (finalSelectedVariationIds.length === 0) updateGalleryThumbnails(defaultGalleries);
                        } else {
                            finalSelectedVariationIds.push(matched.id);
                            let combined = [...(matched.galleries || []), ...defaultGalleries];
                            updateGalleryThumbnails([...new Set(combined)]);
                            if (matched.main_image) document.getElementById('mainImage').src = matched.main_image;
                        }
                    }

                    recalculateSelectedPrice();
                }
            }
            updateSkuDisplay();
            renderAttributes();
        }

        function checkIsSelected(groupName, valId) {
            return allVariations.some(v => finalSelectedVariationIds.includes(v.id) && v.attributes[groupName].id == valId);
        }

        function handleAddToCart(isOrderNow = false) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const qty = document.getElementById('main-qty').value;
            let items = [];

            let varIds = document.getElementById('selected-variation-id').value;
            if ('{{ $product->type }}' === 'variation') {
                let missingAttribute = attributeGroups.find(group => !activeFilters[group]);

                if (missingAttribute) {
                    toastr.warning(`Please select ${missingAttribute}`);
                    return;
                }
                varIds.split(',').forEach(id => items.push({
                    variation_id: id,
                    qty: qty
                }));
            } else {
                items.push({
                    id: {{ $product->id }},
                    qty: qty
                });
            }

            fetch("{{ route('cart.add') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    items: items
                })
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

        document.addEventListener("DOMContentLoaded", () => {
            if (attributeGroups.length > 0) renderAttributes();
        });
    </script>
    <script>
        function toggleWishlist(productId) {
            const token = document.querySelector('meta[name="csrf-token"]').content;

            fetch("{{ route('wishlist.toggle') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'unauthorized') {
                        toastr.warning(data.message);
                        return;
                    }

                    const allIcons = document.querySelectorAll(
                        `[id="wish-icon-${productId}"], .wish-icon-${productId}, button[onclick="toggleWishlist(${productId})"] i`
                    );

                    allIcons.forEach(icon => {
                        if (data.status === 'added') {
                            // SVG সাপোর্ট (Template 1)
                            icon.setAttribute('fill', '#ef4444');
                            icon.setAttribute('stroke', '#ef4444');
                            icon.classList.replace('fa-regular', 'fa-solid');
                            icon.classList.add('text-white');

                            const btn = icon.closest('button');
                            if (btn) btn.classList.replace('primary-bg', 'bg-red-500');
                        } else {
                            icon.setAttribute('fill', 'none');
                            icon.setAttribute('stroke', 'currentColor');
                            icon.classList.replace('fa-solid', 'fa-regular');

                            const btn = icon.closest('button');
                            if (btn) btn.classList.replace('bg-red-500', 'primary-bg');
                        }
                    });

                    const wishCountElements = document.querySelectorAll('.wishlist-count-val');
                    wishCountElements.forEach(el => {
                        el.innerText = data.wish_count;
                    });

                    if (data.status === 'added') {
                        toastr.success(data.message);
                    } else {
                        toastr.info(data.message);
                    }
                })
                .catch(error => console.error('Error:', error));
        }
    </script>
    <script>
        function scrollToSection(sectionId, btn) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

                document.querySelectorAll('.tab-nav-btn').forEach(b => {
                    b.classList.remove('text-[var(--primary-color)]', 'border-[var(--primary-color)]');
                    b.classList.add('text-gray-500', 'border-transparent');
                });
                btn.classList.remove('text-gray-500', 'border-transparent');
                btn.classList.add('text-[var(--primary-color)]', 'border-[var(--primary-color)]');
            }
        }
    </script>
@endpush
@push('scripts')
    @include('components.meta-info.pixel-events', ['event' => 'ViewContent', 'data' => ['product' => $product]])
@endpush
