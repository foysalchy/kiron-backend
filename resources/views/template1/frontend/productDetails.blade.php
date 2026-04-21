@extends('template1.layouts.front')

@section('content')
    @php
        $isWishlisted = false;
        if (auth('customer')->check()) {
            $isWishlisted = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
                ->where('product_id', $product->id)
                ->exists();
        }
    @endphp
     <section class="py-4 md:py-6 container mx-auto px-4 lg:px-0">

        <!-- 1. Breadcrumb -->
        <nav
            class="flex items-center space-x-2 text-md text-gray-500 mb-6 overflow-x-auto whitespace-nowrap pb-2 no-scrollbar">
            <a href="/" class="hover:text-[#FF6A00]">Home</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <a href="#" class="hover:text-[#FF6A00]">{{ $category->name ?? 'Product Details' }}</a>
            <i class="fas fa-chevron-right text-[8px]"></i>
            <span class="text-[#F97316]">{{ $product->title ?? 'Product' }}</span>
        </nav>

        <!-- 2. Product Top Info Card -->
        <div class="overflow-hidden mb-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 border border-gray-100 rounded-xl">

                <!-- Left: Image Gallery -->
                <div class="p-4 lg:border-r border-b lg:border-b-0 border-gray-100">
                    <div
                        class="aspect-square mb-4 overflow-hidden rounded-xl bg-gray-50 border border-gray-100 relative group">
                        <img id="mainImage" src="{{ $product->thumbnail_url }}"
                            class="w-full h-full object-contain transition-transform duration-500">
                    </div>
                    <div id="thumbnail-container" class="grid grid-cols-5 sm:grid-cols-6 gap-2 md:gap-3">
                        <button onclick="changeImage('{{ $product->thumbnail_url }}')"
                            class="aspect-square rounded-lg border-2 border-[#FF6A00] p-1 bg-white overflow-hidden">
                            <img src="{{ $product->thumbnail_url }}" class="w-full h-full object-contain">
                        </button>
                        @foreach ($product->galleries as $gallery)
                            <button onclick="changeImage('{{ asset('storage/' . $gallery->image) }}')"
                                class="aspect-square rounded-lg border border-gray-200 p-1 bg-white hover:border-[#FF6A00] transition-colors overflow-hidden">
                                <img src="{{ asset('storage/' . $gallery->image) }}" class="w-full h-full object-contain">
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Right: Product Purchase Details -->
                <div class="p-6 md:p-8">
                    @if ($product->display_price_data->regular_price > $product->display_price_data->sale_price)
                        <span id="discount-badge"
                            class="inline-block bg-[#FFCF00] text-black text-sm font-bold px-3 py-1 rounded-full mb-4">
                            {{ number_format((($product->display_price_data->regular_price - $product->display_price_data->sale_price) / $product->display_price_data->regular_price) * 100) }}%
                            OFF
                        </span>
                    @endif

                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 leading-tight">{{ $product->title }}</h1>

                    <div class="flex items-center gap-2 mb-6 text-sm text-gray-600">
                        <span class="uppercase font-bold">SKU</span>: <span class="font-mono">
                            @if (is_array($product->sku_code))
                                {{ implode(', ', $product->sku_code) }}
                            @else
                                {{ $product->sku_code ?? 'N/A' }}
                            @endif
                        </span>
                        <span class="ml-4 font-bold">Brand:</span> <span>{{ $product->brand->name ?? 'No Brand' }}</span>
                    </div>

                    <div class="flex items-baseline gap-4 mb-6">
                        @if ($product->display_price_data->regular_price > $product->display_price_data->sale_price)
                            <span id="regular-price" class="text-gray-400 text-lg line-through">{{ $setup->currency }}
                                {{ number_format($product->display_price_data->regular_price) }}</span>
                        @endif
                        <span id="sale-price" class="text-3xl font-black text-[#00A651]">{{ $setup->currency }}
                            {{ number_format($product->display_price_data->sale_price) }}</span>
                    </div>

                    <!-- Dynamic Variations Container -->
                    @if ($product->type === 'variation')
                        <div id="dynamic-attributes-container" class="mb-5"></div>
                    @endif

                    <!-- Quantity -->
                    <div class="flex items-center gap-6 mb-8 mt-4">
                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden h-10">
                            <button onclick="changeQty(-1)" class="px-3 hover:bg-gray-50"><i
                                    class="fas fa-minus text-[10px]"></i></button>
                            <input type="number" id="main-qty" value="1" readonly
                                class="w-10 text-center font-bold border-none outline-none">
                            <button onclick="changeQty(1)" class="px-3 hover:bg-gray-50"><i
                                    class="fas fa-plus text-[10px]"></i></button>
                        </div>
                        <span class="text-sm {{ $product->available_stock > 0 ? 'text-green-600' : 'text-red-500' }}">
                            {{ $product->available_stock > 0 ? $product->available_stock . ' in stock' : 'Out of stock' }}
                        </span>
                    </div>

                    <!-- Hidden Input for Selected Variation -->
                    <input type="hidden" id="selected-variation-id" value="">

                    <!-- Action Buttons -->
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-3 mb-8">
                        <!-- ১. Add To Cart -->
                        <button id="btn-cart" onclick="handleAddToCart()"
                            {{ $product->available_stock <= 0 ? 'disabled' : '' }}
                            class="flex-1 primary-bg hover:bg-green-700 text-primary h-12 rounded-lg font-bold flex items-center justify-center gap-2 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                            Add To Cart
                        </button>

                        <!-- ২. Order Now -->
                        <button id="btn-order" onclick="handleAddToCart(true)"
                            {{ $product->available_stock <= 0 ? 'disabled' : '' }}
                            class="flex-1 secondary-bg hover:bg-yellow-500 text-secondary h-12 rounded-lg font-bold flex items-center justify-center gap-2 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                            Order Now
                        </button>

                        <!-- ৩. Wishlist -->
                        <button id="btn-wish" type="button" onclick="toggleWishlist({{ $product->id }})"
                            {{ $product->available_stock <= 0 ? 'disabled' : '' }}
                            class="flex-1 bg-white border-2 border-gray-100 text-gray-600 h-12 rounded-lg font-bold flex items-center justify-center gap-2 transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                            Wishlist
                        </button>
                    </div>

                    <!-- Dynamic Trust List -->
                    <div class="space-y-3 text-md mb-6">
                        @foreach ($trustBadges->where('page_type', 'product_page') as $item)
                            <div class="flex items-center gap-2"
                                style="color: {{ $item->sort_order == 1 ? '#00A651' : ($item->sort_order == 2 ? '#3B82F6' : ($item->sort_order == 3 ? '#9333EA' : '#F15A24')) }};">
                                <div class="shrink-0">
                                    @if ($item->icon_file)
                                        <img src="{{ asset('storage/' . $item->icon_file) }}"
                                            class="h-5 w-5 object-contain">
                                    @else
                                        <i class="{{ $item->icon_url ?? 'fas fa-check-circle' }} h-5 w-5"></i>
                                    @endif
                                </div>
                                <span class="">
                                    {{ $item->title }} {{ $item->subtitle ? ': ' . $item->subtitle : '' }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <!-- Dynamic 3-Column Grid -->
                   <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
                        @foreach ($trustBadges->where('page_type', 'product_page_sub') as $card)
                            <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg text-center justify-center">
                                <div class="text-orange-500 shrink-0">
                                    @if ($card->icon_file)
                                        <img src="{{ asset('storage/' . $card->icon_file) }}"
                                            class="h-5 w-5 object-contain">
                                    @else
                                        <i class="{{ $card->icon_url ?? 'fas fa-star' }} h-5 w-5"></i>
                                    @endif
                                </div>
                                <div class="text-[12px] md:text-sm font-medium text-gray-800">{{ $card->title }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-center mb-4 text-gray-700">Call or WhatsApp to order directly</div>

                    <div class="grid grid-cols-2 gap-2 md:gap-3">

                        <a href="tel:{{ $setup->phone }}"
                            class="bg-[#EE4D2D] hover:bg-red-600 text-primary h-11 rounded-xl flex items-center justify-center gap-3 font-bold transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-phone h-5 w-5 mr-2">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                </path>
                            </svg>
                            Call Now
                        </a>

                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $setup->phone) }}?text={{ urlencode("Assalamu Alaikum, I want to order this product:\n\n*" . $product->title . "*\n\nClick here for details:\n" . url()->current()) }}"
                            target="_blank"
                            class="bg-[#25D366] hover:bg-green-600 text-primary h-11 rounded-xl flex items-center justify-center gap-3 font-bold transition-colors px-4">
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

        <!-- 3. TABS SECTION -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-12">
            <!-- Tab Buttons -->
            <div class="flex items-center border-b border-gray-100 bg-[#F9FAFB] overflow-x-auto no-scrollbar" id="tabs-nav">
                <button onclick="switchTab('description')" id="tab-btn-description"
                    class="tab-btn px-4 md:px-8 py-3 md:py-4 text-xs md:text-sm whitespace-nowrap transition-all border-b-2 border-[#FF6A00] text-gray-900 bg-white font-bold">Description</button>

                <button onclick="switchTab('specification')" id="tab-btn-specification"
                    class="tab-btn px-4 md:px-8 py-3 md:py-4 text-xs md:text-sm whitespace-nowrap transition-all border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-bold">Specification</button>

                <button onclick="switchTab('review')" id="tab-btn-review"
                    class="tab-btn px-4 md:px-8 py-3 md:py-4 text-xs md:text-sm whitespace-nowrap transition-all border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-bold">
                    Reviews ({{ $product->reviews->count() }})
                </button>
            </div>

            <!-- Tab Content Area -->
            <div class="p-6 md:p-10">

                <!-- Section: Description  -->
                <div id="tab-content-description" class="tab-content block">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Product Description</h3>
                    <div class="text-gray-600 leading-relaxed prose prose-orange max-w-none">
                        {!! $product->full_description ?? 'No detailed description available for this product.' !!}
                    </div>
                </div>

                <!-- Section: Specification  -->
                <div id="tab-content-specification" class="tab-content hidden">
                    <h3 class="text-xl font-bold text-gray-900 mb-8">Product Specification</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-2 text-md text-gray-800">

                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <span class="font-medium text-gray-500">Brand:</span>
                            <span class="font-bold">{{ $product->brand->name ?? 'N/A' }}</span>
                        </div>

                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <span class="font-medium text-gray-500">SKU:</span>
                            <span class="font-mono font-bold">
                                @if (is_array($product->sku_code))
                                    {{ implode(', ', $product->sku_code) }}
                                @else
                                    {{ $product->sku_code ?? 'N/A' }}
                                @endif
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <span class="font-medium text-gray-500">Availability:</span>
                            <span
                                class="font-bold {{ $product->available_stock > 0 ? 'text-green-600' : 'text-red-500' }}">
                                {{ $product->available_stock > 0 ? 'In Stock' : 'Out of Stock' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <span class="font-medium text-gray-500">Category:</span>
                            <span class="font-bold">{{ $product->mega_categories->first()->name ?? 'N/A' }}</span>
                        </div>

                        @php
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
                        @endphp

                        @foreach ($grouped as $groupName => $values)
                            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                <span class="font-medium text-gray-500">{{ $groupName }}:</span>
                                <span class="font-bold">{{ implode(', ', $values) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>


                <!-- Section: Review -->
                <div id="tab-content-review" class="tab-content hidden px-2">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 mt-4">Customer Reviews</h3>

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
                                            <h4 class="text-md font-bold text-gray-900">
                                                {{ $review->customer->name ?? 'Customer' }}</h4>

                                            <div class="flex text-yellow-400 text-[10px] gap-0.5">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="{{ $i <= $review->rating ? 'fas' : 'far' }} fa-star"></i>
                                                @endfor
                                            </div>

                                            <span class="text-sm text-gray-400 font-medium ml-1">
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
                                <p class="text-gray-400 font-medium italic">No reviews yet. Be the first to share your
                                    experience!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </section>
@endsection

@push('scripts')
    <script>
        // Function to show the image below thumbnails
        function expandReviewImage(imgSrc, reviewId) {
            const container = document.getElementById('expanded-container-' + reviewId);
            const largeImg = document.getElementById('large-view-' + reviewId);

            // Set the source and show the container
            largeImg.src = imgSrc;
            container.classList.remove('hidden');

            // Optional: Smooth scroll to the expanded image
            container.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        // Function to hide the expanded image
        function closeReviewImage(reviewId) {
            const container = document.getElementById('expanded-container-' + reviewId);
            container.classList.add('hidden');
        }
        // geneal function
        function changeImage(src) {
            document.getElementById('mainImage').src = src;
        }

        function changeQty(val) {
            let q = document.getElementById('main-qty');
            let newVal = parseInt(q.value) + val;
            if (newVal >= 1) q.value = newVal;
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

        // variation and gallery data
        const attributeGroups = @json($attributeGroups ?? []);
        const allVariations = @json($formattedVariations ?? []);
        let userSelections = {};
        let originalGalleryHtml = ''; // save main gallery

        function renderAttributes() {
            const container = document.getElementById('dynamic-attributes-container');
            const thumbContainer = document.getElementById('thumbnail-container');
            if (!container) return;

            // main gallery
            if (!originalGalleryHtml && thumbContainer) {
                originalGalleryHtml = thumbContainer.innerHTML;
            }

            container.innerHTML = '';
            let currentlyValidVariations = allVariations;

            // button
            for (let i = 0; i < attributeGroups.length; i++) {
                const groupName = attributeGroups[i];
                let availableValues = {};

                currentlyValidVariations.forEach(variation => {
                    if (variation.attributes[groupName]) {
                        availableValues[variation.attributes[groupName].id] = variation.attributes[groupName].name;
                    }
                });

                let groupHtml =
                    `<div class="mb-4"><h3 class="text-sm font-bold text-gray-700 mb-2">Choose ${groupName}:</h3><div class="flex flex-wrap gap-2">`;
                for (const [valId, valName] of Object.entries(availableValues)) {
                    const activeClass = (userSelections[groupName] == valId) ?
                        'border-[#FF6A00] bg-orange-50 text-[#FF6A00]' : 'border-gray-200 bg-white text-gray-700';
                    groupHtml +=
                        `<button type="button" onclick="selectOption('${groupName}', ${valId})" class="px-4 py-2 rounded-lg border text-sm font-bold transition-all ${activeClass}">${valName}</button>`;
                }
                groupHtml += `</div></div>`;
                container.innerHTML += groupHtml;

                if (!userSelections[groupName]) break;
                currentlyValidVariations = currentlyValidVariations.filter(v => v.attributes[groupName] && v.attributes[
                    groupName].id == userSelections[groupName]);
            }

            // update galleries
            if (currentlyValidVariations.length === 1 && Object.keys(userSelections).length === attributeGroups.length) {
                let final = currentlyValidVariations[0];

                if (final.image) changeImage(final.image);

                // show variatin image
                if (thumbContainer) {
                    let galleryHtml = '';
                    // variation main image
                    if (final.image) {
                        galleryHtml +=
                            `<button onclick="changeImage('${final.image}')" class="aspect-square rounded-lg border-2 border-[#FF6A00] p-1 bg-white overflow-hidden"><img src="${final.image}" class="w-full h-full object-contain"></button>`;
                    }
                    // variation others image
                    if (final.galleries && final.galleries.length > 0) {
                        final.galleries.forEach(imgUrl => {
                            galleryHtml +=
                                `<button onclick="changeImage('${imgUrl}')" class="aspect-square rounded-lg border border-gray-200 p-1 bg-white hover:border-[#FF6A00] transition-colors overflow-hidden"><img src="${imgUrl}" class="w-full h-full object-contain"></button>`;
                        });
                    }
                    thumbContainer.innerHTML = galleryHtml;
                }

                document.getElementById('sale-price').innerText = '{{ $setup->currency }} ' + final.price.toLocaleString();
                document.getElementById('selected-variation-id').value = final.id;
            } else {
                // back main galleries
                if (thumbContainer && originalGalleryHtml) {
                    thumbContainer.innerHTML = originalGalleryHtml;
                    changeImage("{{ $product->thumbnail_url }}");
                }
                document.getElementById('sale-price').innerText =
                    '{{ $setup->currency }} {{ number_format($product->display_price_data->sale_price ?? 0) }}';
                document.getElementById('selected-variation-id').value = '';
            }
        }

        function selectOption(group, valId) {
            userSelections[group] = valId;
            renderAttributes();
        }

        // add to cart
        function handleAddToCart(isOrderNow = false) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const qty = document.getElementById('main-qty').value;
            const productType = '{{ $product->type }}';

            let postData = {
                qty: qty
            };

            if (productType === 'single') {
                postData.id = {{ $product->id }};
            } else {
                let varId = document.getElementById('selected-variation-id').value;
                if (!varId) {
                    toastr.warning('Please select all options (color/size)');
                    return;
                }
                postData.variation_id = varId;
            }

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
                }).catch(err => toastr.error("Server error occurred."));
        }

        document.addEventListener("DOMContentLoaded", () => {
            if (attributeGroups.length > 0) renderAttributes();
        });

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
                    } else {
                        // update all icons with same product id in the page
                        const icons = document.querySelectorAll(`[id="wish-icon-${productId}"]`);
                        icons.forEach(icon => {
                            if (data.status === 'added') {
                                icon.setAttribute('fill', '#ef4444');
                                icon.setAttribute('stroke', '#ef4444');
                            } else {
                                icon.setAttribute('fill', 'none');
                                icon.setAttribute('stroke', 'currentColor');
                            }
                        });
                        if (data.status === 'added') toastr.success(data.message);
                        else toastr.info(data.message);
                    }
                });
        }
    </script>
@endpush
