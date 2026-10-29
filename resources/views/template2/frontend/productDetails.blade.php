@extends('template2.layouts.front')
@push('styles')
    <link rel="stylesheet"
        href="{{ asset('build/' . (json_decode(file_get_contents(public_path('build/manifest.json')), true)['node_modules/swiper/swiper-bundle.css']['file'] ?? 'assets/swiper-bundle-CXe816k3.css')) }}">
    <style>
        .t2-product-thumb-swiper {
            width: 100%;
            overflow: hidden;
        }

        .t2-product-thumb-swiper .swiper-wrapper {
            display: flex;
            align-items: center;
        }

        .t2-product-thumb-swiper .swiper-slide {
            flex-shrink: 0;
            height: auto;
        }
    </style>
@endpush
@section('meta')
    @include('components.meta-info.ecommerce-meta.product-details-meta', [
        'setup' => $setup,
        'product' => $product,
        'breadcrumb' => $breadcrumb ?? []
    ])
@endsection
@section('content')
    @php
        $isWishlisted = false;
        if (auth('customer')->check()) {
            $isWishlisted = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
                ->where('product_id', $product->id)
                ->exists();
        }
    @endphp
    <section class="py-2   container mx-auto px-4 lg:px-0">

        <nav aria-label="Breadcrumb"
            class="flex items-center gap-2 mb-4 text-sm font-medium text-gray-500 overflow-x-auto no-scrollbar whitespace-nowrap md:pt-4">

            <a href="{{ route('home') }}"
                class="hover:text-[var(--primary-color)] transition-colors flex items-center gap-1">
                <i class="fas fa-home text-xs"></i> Home
            </a>

            @if(isset($breadcrumb) && count($breadcrumb) > 0)
                @foreach($breadcrumb as $item)
                    <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                        class="w-4 h-4 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                    </svg>
                    <a href="{{ url($item['slug']) }}"
                        class="hover:text-[var(--primary-color)] transition-colors">{{ $item['name'] }}</a>
                @endforeach
            @endif

            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24"
                class="w-4 h-4 text-gray-400 shrink-0" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
            </svg>
            <span
                class="text-[var(--primary-color)] font-bold truncate max-w-[200px] md:max-w-none">{{ $product->title }}</span>
        </nav>

        <!-- 2. Product Top Info Card -->
        <div class="overflow-hidden mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 bg-white rounded-lg shadow-xs ">

                <!-- Left: Image Gallery -->
                <div class="p-4 lg:border-r border-b lg:border-b-0 border-gray-100">
                    <div
                        class="aspect-square mb-4 overflow-hidden rounded-xl bg-gray-50 border border-gray-100 relative group flex items-center justify-center">
                        <img id="mainImage"
                            src="{{ $product->thumbnail_url ?? asset('./images/template1/frontend/product-thumbnail.webp') }}"
                            alt="{{ $product->title ?? 'Product Image' }}"
                            class="w-full h-full object-contain transition-transform duration-500">
                    </div>
                    <div class="relative mt-3 px-8">
                        <div class="swiper t2-product-thumb-swiper w-full">
                            <div id="thumbnail-container" class="swiper-wrapper">
                                @php
                                    $videoData = $product->video_data;
                                @endphp

                                @foreach ($allProductImages as $imgUrl)
                                    <div class="swiper-slide">
                                        <button onclick="changeImage('{{ $imgUrl }}', this)"
                                            class="thumb-btn w-full aspect-square rounded-lg border {{ $loop->first ? 'border-2 border-[var(--primary-color)]' : 'border-gray-200' }} p-1 bg-white hover:border-[#FF6A00] transition-colors overflow-hidden cursor-pointer block">
                                            <img src="{{ $imgUrl }}"
                                                onerror="this.src='{{ asset('./images/template1/frontend/product-thumbnail.webp') }}'"
                                                class="w-full h-full object-contain pointer-events-none" alt="Product Image"
                                                loading="lazy" width="800" height="800">
                                        </button>
                                    </div>
                                @endforeach

                                @if ($videoData)
                                    <div class="swiper-slide">
                                        <button type="button"
                                            onclick="openVideoModal('{{ $videoData['embed_url'] }}', '{{ $videoData['type'] }}')"
                                            aria-label="Play Product Video"
                                            class="thumb-btn w-full aspect-square rounded-lg border border-gray-200 p-1 bg-white hover:border-[#FF6A00] transition-colors overflow-hidden relative group/vbtn cursor-pointer block">
                                            <img src="{{ $videoData['thumbnail_url'] ?? asset('./images/template1/frontend/product-thumbnail.webp') }}"
                                                onerror="this.src='{{ asset('./images/template1/frontend/product-thumbnail.webp') }}'"
                                                class="w-full h-full object-contain pointer-events-none"
                                                alt="Product Video Thumbnail" loading="lazy" width="800" height="800">
                                            <div
                                                class="absolute inset-0 bg-black/35 flex items-center justify-center group-hover/vbtn:bg-black/50 transition-colors">
                                                <span
                                                    class="w-7 h-7 sm:w-9 sm:h-9 bg-black/60 rounded-full flex items-center justify-center text-white shadow-md border border-white/70">
                                                    <i class="fas fa-play text-xs sm:text-sm ml-0.5"></i>
                                                </span>
                                            </div>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Navigation Arrows (Always visible outside slider) -->
                        <button type="button" aria-label="Previous image"
                            class="t2-thumb-prev absolute left-0 top-1/2 -translate-y-1/2 z-10 w-7 h-7 bg-gray-100 hover:bg-[var(--primary-color)] hover:text-white text-gray-700 rounded-full flex items-center justify-center shadow-xs border border-gray-200 cursor-pointer transition disabled:opacity-30 disabled:pointer-events-none">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                        <button type="button" aria-label="Next image"
                            class="t2-thumb-next absolute right-0 top-1/2 -translate-y-1/2 z-10 w-7 h-7 bg-gray-100 hover:bg-[var(--primary-color)] hover:text-white text-gray-700 rounded-full flex items-center justify-center shadow-xs border border-gray-200 cursor-pointer transition disabled:opacity-30 disabled:pointer-events-none">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Right: Product Purchase Details -->
                <div class="lg:p-6 md:p-8 px-0 py-4">
                    @php $isUpcoming = ($product->status == \App\Enums\Status::Upcoming->value); @endphp

                    @if ($isUpcoming)
                        <span class="inline-block bg-blue-600 text-white text-sm font-bold px-3 py-1 rounded-full mb-4">
                            {{ \App\Enums\Status::Upcoming->label() }}
                        </span>
                    @endif

                    @php
                        $hasDiscount = ($product->display_price_data->regular_price > $product->display_price_data->sale_price);
                    @endphp

                    <span id="discount-badge"
                        class="{{ $hasDiscount ? '' : 'hidden ' }}inline-block bg-[#FFCF00] text-black text-sm font-bold px-3 py-1 rounded-full mb-4">
                        @if ($hasDiscount)
                            {{ number_format((($product->display_price_data->regular_price - $product->display_price_data->sale_price) / $product->display_price_data->regular_price) * 100) }}%
                            OFF
                        @endif
                    </span>

                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 leading-tight">{{ $product->title }}</h1>

                    @php
                        $skus = [];
                        if ($product->type === 'variation') {
                            $skus = $product->variations->pluck('sku')->filter()->toArray();
                        } else {
                            $skus = is_array($product->sku_code) ? $product->sku_code : [$product->sku_code];
                        }
                        $skus = array_unique(array_filter($skus));
                    @endphp
                    @if(!empty($skus) || !empty($product->brand))
                        <div class="flex items-center gap-2 mb-2 text-sm text-gray-600">
                            @if(!empty($skus))
                                <span class="uppercase font-bold">SKU</span>: <span id="product-sku" class="font-mono">
                                    {{ implode(', ', $skus) }}
                                </span>
                            @endif
                            @if(!empty($product->brand))
                                <span class="@if(!empty($skus)) ml-4 @endif font-bold">Brand:</span>
                                <span>{{ $product->brand->name }}</span>
                            @endif
                        </div>
                    @endif
                    @if(!empty(strip_tags(trim($product->short_description))))
                        <div
                            class="prose prose-slate max-w-none mb-4 text-lg leading-relaxed font-medium overflow-visible relative">
                            <h2 class="text-xl font-bold text-gray-900 mb-6">Summury </h2>
                            {!! $product->short_description !!}
                        </div>
                    @endif

                    <div class="flex items-baseline gap-4 mb-6">
                        @php
                            $hasRegPrice = ($product->display_price_data->regular_price > $product->display_price_data->sale_price);
                        @endphp
                        <span id="regular-price"
                            class="{{ $hasRegPrice ? '' : 'hidden ' }}text-gray-500 text-lg line-through">
                            @if ($hasRegPrice)
                                @if(($setup->currency_position ?? 'left') == 'left')
                                    {{ $setup->currency }} {{ number_format($product->display_price_data->regular_price) }}
                                @else
                                    {{ number_format($product->display_price_data->regular_price) }} {{ $setup->currency }}
                                @endif
                            @endif
                        </span>

                        <span id="sale-price" class="text-xl md:text-3xl font-bold secondary-text">
                            @if(($setup->currency_position ?? 'left') == 'left')
                                {{ $setup->currency }} {{ number_format($product->display_price_data->sale_price) }}
                            @else
                                {{ number_format($product->display_price_data->sale_price) }} {{ $setup->currency }}
                            @endif
                        </span>

                        @if ($product->is_free_delivery == 1)
                            <span
                                class="bg-green-100 text-green-700 border border-green-200 text-[10px] md:text-xs font-bold px-2 py-1 rounded self-center ml-2">
                                Free Shipping
                            </span>
                        @endif
                    </div>

                    <!-- Dynamic Variations Container -->
                    @if ($product->type === 'variation')
                        <div id="dynamic-attributes-container" class="mb-5"></div>
                    @endif

                    <!-- Quantity -->
                    <div class="flex items-center gap-6 mb-8 mt-4">
                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden h-10">
                            <button onclick="changeQty(-1)" aria-label="Decrease quantity" class="px-3 hover:bg-gray-50"><i
                                    class="fas fa-minus text-[10px]"></i></button>
                            <input type="number" id="main-qty" aria-label="Quantity" value="1" readonly
                                class="w-10 text-center font-bold border-none outline-none">
                            <button onclick="changeQty(1)" aria-label="Increase quantity" class="px-3 hover:bg-gray-50"><i
                                    class="fas fa-plus text-[10px]"></i></button>
                        </div>
                        {{-- Replace this part --}}
                        @php
                            $isProductOutOfStock = ($product->stock_status === 'out_of_stock') || ($product->manage_stock && $product->available_stock <= 0);
                        @endphp
                        <span
                            class="text-sm {{ $isProductOutOfStock ? 'text-red-500' : 'text-green-700' }}">
                            {{ $isProductOutOfStock ? 'Out of stock' : (!$product->manage_stock ? 'In Stock' : $product->available_stock . ' in stock') }}
                        </span>
                    </div>

                    <!-- Hidden Input for Selected Variation -->
                    <input type="hidden" id="selected-variation-id" value="">

                    {{-- Free Shipping Notice (Only if product does not have its own free delivery) --}}
                    @if ($product->is_free_delivery != 1 && !empty($setup->free_shipping_text))
                        <div class="mb-4">
                            @if(!empty($activePreviewTheme))
                                <div class="mb-1">
                                    <x-preview-badge name="Shipping Charge Rule" />
                                </div>
                            @endif
                            <div
                                class="p-2.5 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2 text-amber-800 text-xs md:text-xl font-medium">
                                <i class="fa-solid fa-gift text-amber-600"></i>
                                <span>{{ $setup->free_shipping_text }}</span>
                            </div>
                        </div>
                    @elseif(!empty($activePreviewTheme))
                        {{-- In Preview mode show dummy shipping text if empty --}}
                        <div class="mb-4">
                            <div class="mb-1">
                                <x-preview-badge name="Shipping Charge Rule" :isDummy="true" />
                            </div>
                            <div
                                class="p-2.5 bg-amber-50 border border-amber-200 rounded-lg flex items-center gap-2 text-amber-800 text-xs md:text-xl font-medium">
                                <i class="fa-solid fa-gift text-amber-600"></i>
                                <span>Free shipping on orders over ৳1500 (Sample Preview)</span>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-3 mb-8">
                        <!-- 1. Add To Cart -->
                        <button id="btn-cart" onclick="handleAddToCart()" {{ ($isUpcoming || $isProductOutOfStock) ? 'disabled' : '' }}
                            class="order-2 md:order-1 col-span-1 md:col-span-1 flex-1 primary-bg hover:bg-green-700 text-primary h-12 rounded-lg font-bold flex items-center justify-center gap-2 transition-all disabled:opacity-40 disabled:cursor-not-allowed">{{ isset($setup->lang) && $setup->lang == 'bn' ? 'কার্টে যোগ করুন' : 'Add To Cart' }}</button>

                        <!-- 2. Order Now Button -->
                        <button id="btn-order" onclick="handleAddToCart(true)" {{ ($isUpcoming || $isProductOutOfStock) ? 'disabled' : '' }}
                            class="order-1 md:order-2 col-span-2 md:col-span-1 flex-1 secondary-bg hover:bg-yellow-500 text-black h-12 rounded-lg font-bold flex items-center justify-center gap-2 transition-all disabled:opacity-40 disabled:cursor-not-allowed">{{ isset($setup->lang) && $setup->lang == 'bn' ? 'অর্ডার করুন' : 'Order Now' }}</button>

                        <!--  Wishlist -->
                        <button id="btn-wish" type="button" onclick="toggleWishlist({{ $product->id }})"
                            class="order-3 md:order-3 col-span-1 md:col-span-1 flex-1 border-2 h-12 rounded-lg font-bold flex items-center justify-center gap-2 transition-all
                                                                                        {{ $isWishlisted ? 'bg-orange-50 text-[#FF6A00] border-[#FF6A00]' : 'bg-white border-gray-100 text-gray-600' }}">

                            <i id="wish-icon-main"
                                class="{{ $isWishlisted ? 'fas fa-heart text-red-500' : 'far fa-heart' }}"></i>
                            <span id="wish-text-main">{{ $isWishlisted ? 'Wishlisted' : 'Wishlist' }}</span>
                        </button>
                    </div>

                    <!-- Product Custom Block (Notice/Announcement/Policy) -->
                    @if (!empty($productCustomBlock) && !empty($productCustomBlock->text_content))
                        <div class="my-5">
                            @if(!empty($activePreviewTheme))
                                <div class="mb-1">
                                    <x-preview-badge name="Content Setting" :isDummy="!empty($productCustomBlock->is_dummy)" />
                                </div>
                            @endif
                            <div class="product-custom-block p-4 rounded-xl border border-gray-200 bg-white shadow-xs prose max-w-none text-gray-700 leading-relaxed text-sm">
                                {!! $productCustomBlock->text_content !!}
                            </div>
                        </div>
                    @endif

                    <div class="bg-gray-100 py-3 px-3 rounded">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-center flex-1 text-gray-700">Call or WhatsApp to order directly</div>
                            @if(!empty($activePreviewTheme))
                                <x-preview-badge name="Social Setting" />
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-2 md:gap-3">

                            <a href="tel:{{ $setup->phone }}"
                                class="bg-[#EE4D2D] hover:bg-red-600 text-black h-11 rounded-xl flex items-center justify-center gap-3 font-bold transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-phone h-5 w-5 mr-2">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                    </path>
                                </svg>
                                Call Now
                            </a>

                            @php
                                $rawPhone = preg_replace('/[^0-9]/', '', $setup->phone ?? '');
                                if (str_starts_with($rawPhone, '01') && strlen($rawPhone) === 11) {
                                    $waPhone = '88' . $rawPhone;
                                } elseif (str_starts_with($rawPhone, '1') && strlen($rawPhone) === 10) {
                                    $waPhone = '880' . $rawPhone;
                                } else {
                                    $waPhone = $rawPhone;
                                }
                            @endphp

                            <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode("Assalamu Alaikum, I want to order this product:\n\n*" . $product->title . "*\n\nClick here for details:\n" . url()->current()) }}"
                                target="_blank"
                                class="bg-[#25D366] hover:bg-green-600 text-black h-11 rounded-xl flex items-center justify-center gap-3 font-bold transition-colors px-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-message-circle h-5 w-5 mr-2">
                                    <path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"></path>
                                </svg>
                                WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @php
                $hasDescription = !empty(trim(strip_tags($product->full_description ?? '')));

                $grouped = [];
                if ($product->type === 'variation') {
                    foreach ($product->variations as $variation) {
                        foreach ($variation->attributes as $attr) {
                            $name = $attr->attributeGroup->name ?? '';
                            $val = $attr->attributeValue->name ?? '';
                            if (!isset($grouped[$name])) {
                                $grouped[$name] = [];
                            }
                            if (!in_array($val, $grouped[$name])) {
                                $grouped[$name][] = $val;
                            }
                        }
                    }
                }

                $hasSku = !empty($skus);
                $hasSpecs = !empty($product->brand) || $hasSku || (!empty($grouped) && count($grouped) > 0) || !empty($product->mega_categories->first());
                $hasReviews = true;
                $hasAnyTab = $hasDescription || $hasSpecs || $hasReviews;
                $hasRelated = isset($relatedProducts) && $relatedProducts->count() > 0;

                // Pick first visible tab
                $firstTab = $hasDescription ? 'description' : ($hasSpecs ? 'specification' : 'review');
            @endphp

            @if($hasAnyTab || $hasRelated)
                <!-- 3. TABS & RELATED PRODUCTS SECTION -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-12 items-start">

                    <!-- Left Column: Tabs Section -->
                    <div
                        class="{{ $hasAnyTab ? 'md:col-span-7 lg:col-span-8' : 'hidden' }} bg-white rounded-lg shadow-xs relative overflow-hidden">
                        <!-- Tab Buttons -->
                        <div class="flex items-center border-b border-gray-100 bg-[#F9FAFB] overflow-x-auto no-scrollbar"
                            id="tabs-nav">
                            @if($hasDescription)
                                <button onclick="switchTab('description')" id="tab-btn-description"
                                    class="tab-btn px-4 md:px-8 py-3 md:py-4 text-xs md:text-sm whitespace-nowrap transition-all border-b-2 {{ $firstTab === 'description' ? 'border-[#FF6A00] text-gray-900 bg-white' : 'border-transparent text-gray-600 hover:text-gray-900' }} font-bold">Description</button>
                            @endif

                            @if($hasSpecs)
                                <button onclick="switchTab('specification')" id="tab-btn-specification"
                                    class="tab-btn px-4 md:px-8 py-3 md:py-4 text-xs md:text-sm whitespace-nowrap transition-all border-b-2 {{ $firstTab === 'specification' ? 'border-[#FF6A00] text-gray-900 bg-white' : 'border-transparent text-gray-600 hover:text-gray-900' }} font-bold">Specification</button>
                            @endif

                            @if($hasReviews)
                                <button onclick="switchTab('review')" id="tab-btn-review"
                                    class="tab-btn px-4 md:px-8 py-3 md:py-4 text-xs md:text-sm whitespace-nowrap transition-all border-b-2 {{ $firstTab === 'review' ? 'border-[#FF6A00] text-gray-900 bg-white' : 'border-transparent text-gray-600 hover:text-gray-900' }} font-bold">
                                    Reviews ({{ $product->reviews->count() }})
                                </button>
                            @endif
                        </div>

                        <!-- Tab Content Area -->
                        <div class="p-6 md:p-10">

                            @if($hasDescription)
                                <!-- Section: Description  -->
                                <div id="tab-content-description"
                                    class="tab-content {{ $firstTab === 'description' ? 'block' : 'hidden' }}">
                                    <h2 class="text-xl font-bold text-gray-900 mb-6">Product Description</h2>
                                    <div class="text-lg prose prose-orange max-w-none">
                                        {!! $product->full_description !!}
                                    </div>
                                </div>
                            @endif

                            @if($hasSpecs)
                                <!-- Section: Specification  -->
                                <div id="tab-content-specification"
                                    class="tab-content {{ $firstTab === 'specification' ? 'block' : 'hidden' }}">
                                    <h2 class="text-xl font-bold text-gray-900 mb-8">Product Specification</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-2 text-md text-gray-800">

                                        @if(!empty($product->brand))
                                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                                <span class="font-medium text-gray-500">Brand:</span>
                                                <span class="font-bold">{{ $product->brand->name ?? 'N/A' }}</span>
                                            </div>
                                        @endif

                                        @if($hasSku)
                                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                                <span class="font-medium text-gray-500">SKU:</span>
                                                <span id="tab-sku" class="font-mono font-bold">{{ implode(', ', $skus) }}</span>
                                            </div>
                                        @endif

                                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                            <span class="font-medium text-gray-500">Availability:</span>
                                            <span
                                                class="font-bold {{ $isProductOutOfStock ? 'text-red-500' : 'text-green-700' }}">
                                                {{ $isProductOutOfStock ? 'Out of Stock' : 'In Stock' }}
                                            </span>
                                        </div>

                                        @if($product->mega_categories->first())
                                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                                <span class="font-medium text-gray-500">Category:</span>
                                                <span class="font-bold">{{ $product->mega_categories->first()->name }}</span>
                                            </div>
                                        @endif

                                        @foreach ($grouped as $groupName => $values)
                                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                                <span class="font-medium text-gray-500">{{ $groupName }}:</span>
                                                <span class="font-bold">{{ implode(', ', $values) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif


                            <!-- Section: Review -->
                            <div id="tab-content-review" class="tab-content hidden px-2">
                                <h2 class="text-xl font-bold text-gray-900 mb-6 mt-4">Customer Reviews</h2>

                                @php
                                    $reviews = $product->reviews;
                                    $avgRating = $reviews->avg('rating') ?? 0;
                                    $totalReviews = $reviews->count();
                                    $avatarColors = ['bg-orange-600', 'bg-blue-600', 'bg-purple-600', 'bg-indigo-600'];
                                @endphp

                                <!-- Review Summary Card -->
                                <div class="bg-gray-50/50 rounded-2xl p-8 mb-10 border border-gray-100">
                                    <div class="text-4xl font-bold text-[#FF6A00] mb-2">{{ number_format($avgRating, 1) }}</div>
                                    <div class="flex text-yellow-400 text-sm mb-2 gap-0.5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i
                                                class="{{ $i <= round($avgRating) ? 'fas' : 'far' }} fa-star {{ $i <= round($avgRating) ? '' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                    <div class="text-sm text-gray-900 font-bold">{{ $totalReviews }} Reviews</div>
                                </div>

                                <!-- Individual Reviews List -->
                                <div class="space-y-0">
                                    @forelse($reviews as $index => $review)
                                        <div class="py-8 border-b border-gray-100 last:border-0">
                                            <div class="flex items-start gap-5">

                                                <!-- Initials Avatar (Like "RH" or "F") -->
                                                @php
                                                    $nameParts = explode(' ', $review->customer->name ?? 'User');
                                                    $initials = '';
                                                    foreach ($nameParts as $part) {
                                                        $initials .= substr($part, 0, 1);
                                                    }
                                                    $initials = strtoupper(substr($initials, 0, 2));
                                                @endphp

                                                <div
                                                    class="w-11 h-11 {{ $avatarColors[$index % count($avatarColors)] }} text-primary rounded-full flex items-center justify-center text-sm font-black shrink-0 shadow-sm">
                                                    {{ $initials }}
                                                </div>

                                                <div class="flex-1">
                                                    <!-- Header Line: Name, Stars, Date -->
                                                    <div class="flex items-center gap-3 mb-2">
                                                        <h3 class="text-md font-bold text-gray-900">
                                                            {{ $review->customer->name ?? 'Customer' }}
                                                        </h3>

                                                        <div class="flex text-yellow-400 text-[10px] gap-0.5">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                                            @endfor
                                                        </div>

                                                        <span class="text-sm text-gray-500 font-medium ml-1">
                                                            {{ $review->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>

                                                    <!-- Review Content inside the loop -->
                                                    <p class="text-md text-gray-700 leading-relaxed mb-4">
                                                        {{ $review->comment }}
                                                    </p>

                                                    @if ($review->images && count($review->images) > 0)
                                                        <!-- Thumbnails -->
                                                        <div class="flex flex-wrap gap-3 mb-4">
                                                            @foreach ($review->images as $img)
                                                                <div
                                                                    class="w-20 h-20 rounded-lg overflow-hidden border border-gray-100 shadow-sm hover:ring-2 hover:ring-[#FF6A00] transition-all cursor-pointer">
                                                                    <img src="{{ asset('storage/' . $img) }}"
                                                                        onclick="expandReviewImage(this.src, '{{ $review->id }}')"
                                                                        class="w-full h-full object-cover" alt="Review Image">
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <!-- Expanded Image Container (Hidden by default) -->
                                                        <div id="expanded-container-{{ $review->id }}"
                                                            class="hidden mb-6 transition-all duration-500">
                                                            <div class="relative inline-block group">
                                                                <img id="large-view-{{ $review->id }}" src=""
                                                                    alt="Expanded review image"
                                                                    class="max-w-full md:max-w-[450px] max-h-[500px] rounded-2xl border border-gray-100 shadow-xl object-contain bg-white">

                                                                <!-- Close Button -->
                                                                <button onclick="closeReviewImage('{{ $review->id }}')"
                                                                    class="absolute top-3 right-3 bg-black/50 hover:bg-red-500 text-primary w-8 h-8 rounded-full flex items-center justify-center transition-colors cursor-pointer">
                                                                    <i class="fas fa-times text-xs"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <!-- Variant Tag -->
                                                    <div
                                                        class="inline-block bg-[#F3F4F6] text-gray-500 text-[10px] font-bold px-3 py-1.5 rounded-md uppercase tracking-tight">
                                                        @if ($review->variation)
                                                            {{ $review->variation->display_name }}
                                                        @else
                                                            Single Product
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-20 bg-white">
                                            <p class="text-gray-500 font-medium italic">No reviews yet. Be the first to share your
                                                experience!</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /Left Column -->

                    @if($hasRelated)
                        <!-- Right Column: Related Products (Sticky Sidebar) -->
                        <aside class="md:col-span-5 lg:col-span-4 flex flex-col gap-6 sticky top-24">
                            <div class="bg-white rounded-lg shadow-xs border border-gray-100 overflow-hidden">
                                <div class="px-5 py-4 bg-[#F9FAFB] border-b border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <h2 class="font-bold text-gray-900 text-base md:text-lg flex items-center gap-2">
                                            <span class="w-1.5 h-5 bg-[var(--primary-color)] rounded-full inline-block"></span>
                                            Related Products
                                        </h2>
                                        @if(!empty($activePreviewTheme))
                                            <x-preview-badge name="Products" :isDummy="!empty($relatedProducts->first()?->is_dummy)" />
                                        @endif
                                    </div>
                                    @if ($product->mega_categories->first())
                                        <a href="{{ url($product->mega_categories->first()->slug) }}"
                                            class="text-xs font-semibold text-[var(--primary-color)] hover:underline">
                                            See All
                                        </a>
                                    @endif
                                </div>

                                <div class="divide-y divide-gray-100 p-2">
                                    @php
                                        $currency = $setup->currency ?? '৳';
                                        $isL = ($setup->currency_position ?? 'left') == 'left';
                                    @endphp

                                    @foreach ($relatedProducts->take(6) as $rel)
                                        @php
                                            $priceData = $rel->display_price_data;
                                            $relSalePrice = (float) ($priceData->sale_price ?? $rel->sale_price ?? $rel->regular_price ?? 0);
                                            $relRegularPrice = (float) ($priceData->regular_price ?? $rel->regular_price ?? 0);
                                            $hasDiscount = $relRegularPrice > $relSalePrice;
                                        @endphp
                                        <a href="{{ url($rel->slug) }}"
                                            class="p-2.5 sm:p-3 flex items-center gap-3.5 hover:bg-gray-50/80 rounded-lg transition-all group">
                                            <!-- Thumbnail -->
                                            <div
                                                class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-50 rounded-md overflow-hidden shrink-0 border border-gray-100 p-1 flex items-center justify-center">
                                                <img src="{{ $rel->thumbnail_95_url ?? asset('./images/template1/frontend/product-thumbnail.webp') }}"
                                                    alt="{{ $rel->title }}" loading="lazy"
                                                    class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300" />
                                            </div>

                                            <!-- Info -->
                                            <div class="min-w-0 flex-1">
                                                <h3
                                                    class="text-xs sm:text-sm font-semibold text-gray-800 line-clamp-2 group-hover:text-[var(--primary-color)] transition-colors leading-snug">
                                                    {{ $rel->title }}
                                                </h3>
                                                <div class="flex items-baseline gap-2 mt-1.5">
                                                    <span class="text-sm sm:text-base font-bold text-[var(--primary-color)]">
                                                        {{ $isL ? $currency : '' }}{{ number_format($relSalePrice) }}{{ !$isL ? $currency : '' }}
                                                    </span>
                                                    @if ($hasDiscount)
                                                        <span class="text-xs text-gray-500 line-through">
                                                            {{ $isL ? $currency : '' }}{{ number_format($relRegularPrice) }}{{ !$isL ? $currency : '' }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </aside>
                    @endif

                </div>
            @endif

            {{-- Video Popup Modal --}}
            @if ($videoData)
                <div id="productVideoModal"
                    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/75 backdrop-blur-xs p-4 transition-opacity duration-300"
                    onclick="handleVideoModalBackdropClick(event)">
                    <div class="relative w-full max-w-4xl bg-black rounded-2xl overflow-hidden shadow-2xl border border-white/10"
                        onclick="event.stopPropagation()">
                        {{-- Close Button --}}
                        <button type="button" onclick="closeVideoModal()" aria-label="Close Video"
                            class="absolute top-3 right-3 sm:top-4 sm:right-4 z-30 w-8 h-8 sm:w-10 sm:h-10 bg-white text-gray-900 rounded-full flex items-center justify-center shadow-lg hover:bg-gray-100 hover:scale-105 transition-all cursor-pointer">
                            <i class="fas fa-times text-sm sm:text-lg"></i>
                        </button>

                        {{-- Player Area --}}
                        <div id="videoPlayerContainer" class="w-full aspect-video bg-black flex items-center justify-center">
                        </div>
                    </div>
                </div>
            @endif

    </section>
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

        const productVideoData = @json($videoData ?? null);

        function openVideoModal(url, type) {
            const modal = document.getElementById('productVideoModal');
            const playerContainer = document.getElementById('videoPlayerContainer');
            if (!modal || !playerContainer) return;

            if (type === 'youtube' || type === 'vimeo') {
                playerContainer.innerHTML = `<iframe src="${url}" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>`;
            } else {
                playerContainer.innerHTML = `<video src="${url}" class="w-full h-full object-contain" controls autoplay playsinline></video>`;
            }

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeVideoModal() {
            const modal = document.getElementById('productVideoModal');
            const playerContainer = document.getElementById('videoPlayerContainer');
            if (!modal) return;

            modal.classList.add('hidden');
            if (playerContainer) {
                playerContainer.innerHTML = '';
            }
            document.body.style.overflow = '';
        }

        function handleVideoModalBackdropClick(event) {
            if (event.target.id === 'productVideoModal') {
                closeVideoModal();
            }
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeVideoModal();
            }
        });
        function changeQty(amount) {
            let mainQty = document.getElementById('main-qty');

            if (mainQty) {
                let currentQty = parseInt(mainQty.value) || 1;
                let newQty = currentQty + amount;

                if (newQty < 1) {
                    newQty = 1;
                }

                mainQty.value = newQty;
            }
        }
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById('tab-content-' + tabId).classList.remove('hidden');
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('text-gray-900', 'border-[#FF6A00]', 'bg-white');
                b.classList.add('text-gray-500', 'border-transparent');
            });
            document.getElementById('tab-btn-' + tabId).classList.add('text-gray-900', 'border-[#FF6A00]', 'bg-white');
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
                        <div class="swiper-slide">
                            <button class="thumb-btn w-full aspect-square rounded-lg border ${borderClass} p-1 bg-white hover:border-[#FF6A00] transition-colors overflow-hidden cursor-pointer block"
                                onclick="changeImage('${imgUrl}', this)">
                                <img src="${imgUrl}" onerror="this.src='{{ asset('./images/template1/frontend/product-thumbnail.webp') }}'" class="w-full h-full object-contain pointer-events-none" alt="Product Thumbnail" loading="lazy" width="800" height="800">
                            </button>
                        </div>`;
            });

            if (productVideoData && productVideoData.embed_url) {
                container.innerHTML += `
                        <div class="swiper-slide">
                            <button type="button" onclick="openVideoModal('${productVideoData.embed_url}', '${productVideoData.type}')"
                                aria-label="Play Product Video"
                                class="thumb-btn w-full aspect-square rounded-lg border border-gray-200 p-1 bg-white hover:border-[#FF6A00] transition-colors overflow-hidden relative group/vbtn cursor-pointer block">
                                <img src="${productVideoData.thumbnail_url || '{{ asset('./images/template1/frontend/product-thumbnail.webp') }}'}"
                                    onerror="this.src='{{ asset('./images/template1/frontend/product-thumbnail.webp') }}'"
                                    class="w-full h-full object-contain pointer-events-none" alt="Product Video Thumbnail" loading="lazy" width="800" height="800">
                                <div class="absolute inset-0 bg-black/35 flex items-center justify-center group-hover/vbtn:bg-black/50 transition-colors">
                                    <span class="w-7 h-7 sm:w-9 sm:h-9 bg-black/60 rounded-full flex items-center justify-center text-white shadow-md border border-white/70">
                                        <i class="fas fa-play text-xs sm:text-sm ml-0.5"></i>
                                    </span>
                                </div>
                            </button>
                        </div>`;
            }

            if (window.t2ThumbSwiper) {
                window.t2ThumbSwiper.update();
                window.t2ThumbSwiper.slideTo(0);
            }
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

                let groupHtml = `<div class="mb-4"><h2 class="text-lg font-bold text-gray-700 mb-2">Choose ${groupName}:</h2><div class="flex flex-wrap gap-2">`;

                for (const [valId, valName] of Object.entries(availableValues)) {
                    const isFilterActive = (activeFilters[groupName] == valId);
                    const isVariationSelected = checkIsSelected(groupName, valId);

                    const activeClass = (isFilterActive || isVariationSelected) ?
                        'border-[var(--primary-color)] bg-orange-50 text-[var(--primary-color)]' :
                        'border-gray-200 bg-white text-gray-700';

                    // Logic to show image on variation button
                    let btnContent = valueImages[valId]
                        ? `<img src="${valueImages[valId]}" alt="${valName}" class="w-8 h-8 rounded object-cover mr-2 inline-block" loading="lazy" width="800" height="800"> ${valName}`
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
                    if (regularEl) regularEl.classList.add('hidden');
                }
            }

            if (badgeEl) {
                if (regularPrice && regularPrice > salePrice) {
                    const percent = Math.round(((regularPrice - salePrice) / regularPrice) * 100);
                    badgeEl.innerText = percent + '% OFF';
                    badgeEl.classList.remove('hidden');
                } else {
                    if (badgeEl) badgeEl.classList.add('hidden');
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
                    if (skuEl) skuEl.innerText = text;
                    if (tabSkuEl) tabSkuEl.innerText = text;
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
                if (skuEl) skuEl.innerText = text;
                if (tabSkuEl) tabSkuEl.innerText = text;
            }
        }

        function syncVariationGallery() {
            if (finalSelectedVariationIds.length === 0) {
                updateGalleryThumbnails(defaultGalleries);
                const defaultMainImg = @json($product->thumbnail_url ?? asset('./images/template1/frontend/product-thumbnail.webp'));
                if (defaultMainImg) document.getElementById('mainImage').src = defaultMainImg;
                return;
            }

            let selectedGalleries = [];
            finalSelectedVariationIds.forEach(id => {
                const v = allVariations.find(item => item.id === id);
                if (v) {
                    if (v.galleries && v.galleries.length > 0) {
                        selectedGalleries.push(...v.galleries);
                    } else if (v.main_image) {
                        selectedGalleries.push(v.main_image);
                    }
                }
            });

            let combined = selectedGalleries.length > 0 ? selectedGalleries : defaultGalleries;
            updateGalleryThumbnails([...new Set(combined)]);

            const lastSelectedId = finalSelectedVariationIds[finalSelectedVariationIds.length - 1];
            const lastVariation = allVariations.find(item => item.id === lastSelectedId);
            if (lastVariation && lastVariation.main_image) {
                document.getElementById('mainImage').src = lastVariation.main_image;
            }
        }

        function handleSelection(group, valId, isLastGroup) {
            let isSingleChoice = false;
            for (let gName in groupCategories) {
                if (groupCategories[gName] && groupCategories[gName].toString().toLowerCase() === 'single') {
                    isSingleChoice = true;
                    break;
                }
            }

            if (!isLastGroup) {
                activeFilters[group] = (activeFilters[group] == valId) ? null : valId;
                let idx = attributeGroups.indexOf(group);
                for (let i = idx + 1; i < attributeGroups.length; i++) activeFilters[attributeGroups[i]] = null;
                if (finalSelectedVariationIds.length === 0) {
                    syncVariationGallery();
                    recalculateSelectedPrice();
                }
            } else {
                let matched = allVariations.find(v => {
                    let tempFilters = { ...activeFilters };
                    tempFilters[group] = valId;
                    return attributeGroups.every(g => v.attributes[g] && v.attributes[g].id == tempFilters[g]);
                });

                if (isSingleChoice) {
                    if (activeFilters[group] == valId) {
                        // Single choice: ২য় বার ক্লিক করলে deselect
                        activeFilters[group] = null;
                        finalSelectedVariationIds = [];
                    } else if (matched) {
                        activeFilters[group] = valId;
                        finalSelectedVariationIds = [matched.id];
                    }
                } else {
                    // Multiple choice: প্রতিটি ভ্যারিয়েন্ট টগল হবে (সিলেক্ট থাকলে deselect, না থাকলে select)
                    if (matched) {
                        const index = finalSelectedVariationIds.indexOf(matched.id);
                        if (index > -1) {
                            // ২য় বার ক্লিক: deselect
                            finalSelectedVariationIds.splice(index, 1);
                            if (finalSelectedVariationIds.length === 0) {
                                activeFilters[group] = null;
                            } else {
                                // যদি অন্য ভ্যারিয়েন্ট এখনও সিলেক্টেড থাকে, শেষটির ফিল্টার অ্যাক্টিভ রাখা
                                const lastSelected = allVariations.find(v => v.id === finalSelectedVariationIds[finalSelectedVariationIds.length - 1]);
                                if (lastSelected && lastSelected.attributes[group]) {
                                    activeFilters[group] = lastSelected.attributes[group].id;
                                }
                            }
                        } else {
                            // ১ম বার ক্লিক: select
                            activeFilters[group] = valId;
                            finalSelectedVariationIds.push(matched.id);
                        }
                    }
                }

                syncVariationGallery();
                recalculateSelectedPrice();
            }
            updateSkuDisplay();
            renderAttributes();
        }

        function checkIsSelected(groupName, valId) {
            return allVariations.some(v => finalSelectedVariationIds.includes(v.id) && v.attributes[groupName].id == valId);
        }

        function handleAddToCart(isOrderNow = false) {
            if (@json($isUpcoming)) {
                return;
            }
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
                        if (isOrderNow) window.location.href = "{{ route('checkout.index', request()->query()) }}";
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
                            // SVG support
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
    <script>
        function initT2ThumbSwiper(SwiperClass) {
            const el = document.querySelector('.t2-product-thumb-swiper');
            if (SwiperClass && el) {
                if (window.t2ThumbSwiper && typeof window.t2ThumbSwiper.destroy === 'function') {
                    window.t2ThumbSwiper.destroy(true, true);
                }
                window.t2ThumbSwiper = new SwiperClass(el, {
                    slidesPerView: 4,
                    spaceBetween: 8,
                    watchSlidesProgress: true,
                    observer: true,
                    observeParents: true,
                    navigation: {
                        nextEl: '.t2-thumb-next',
                        prevEl: '.t2-thumb-prev',
                    },
                    breakpoints: {
                        640: {
                            slidesPerView: 5,
                            spaceBetween: 10,
                        },
                        1024: {
                            slidesPerView: 6,
                            spaceBetween: 10,
                        }
                    }
                });
            }
        }

        function checkAndInitT2Swiper() {
            if (window.Swiper) {
                initT2ThumbSwiper(window.Swiper);
            } else if (window.SwiperPromise) {
                window.SwiperPromise.then(SwiperClass => {
                    initT2ThumbSwiper(SwiperClass || window.Swiper);
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener("DOMContentLoaded", checkAndInitT2Swiper);
        } else {
            checkAndInitT2Swiper();
        }
    </script>
@endpush
@push('scripts')
    @include('components.meta-info.pixel-events', ['event' => 'ViewContent', 'data' => ['product' => $product]])
@endpush
