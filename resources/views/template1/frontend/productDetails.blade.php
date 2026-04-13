@extends('template1.layouts.front')

@section('content')
    <section class="container mx-auto px-4">

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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">

                <!-- Left: Image Gallery -->
                <div class="p-4 border-r border-gray-50">
                    <div
                        class="aspect-square mb-4 overflow-hidden rounded-xl bg-gray-50 border border-gray-100 relative group">
                        <img id="mainImage" src="{{ $product->thumbnail_url }}"
                            class="w-full h-full object-contain transition-transform duration-500">
                    </div>
                    <div id="thumbnail-container" class="grid grid-cols-5 gap-3">
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
                            <span id="regular-price"
                                class="text-gray-400 text-lg line-through">{{ $setup->currency }} {{ number_format($product->display_price_data->regular_price) }}</span>
                        @endif
                        <span id="sale-price"
                            class="text-3xl font-black text-[#00A651]">{{ $setup->currency }} {{ number_format($product->display_price_data->sale_price) }}</span>
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
                    <div class="flex flex-wrap md:flex-nowrap items-center gap-3 mb-8">
                        <!-- Add To Cart -->
                        <button onclick="handleAddToCart()"
                            class="flex-1 bg-[#00A651] hover:bg-green-700 text-white h-12 rounded-lg font-bold flex items-center justify-center gap-2 cursor-pointer transition-all shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shopping-cart h-5 w-5 mr-2">
                                <circle cx="8" cy="21" r="1"></circle>
                                <circle cx="19" cy="21" r="1"></circle>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12">
                                </path>
                            </svg>
                            <span class="text-md whitespace-nowrap">Add To Cart</span>
                        </button>

                        <!-- Order Now -->
                        <button onclick="handleAddToCart(true)"
                            class="flex-1 bg-[#FFCF00] hover:bg-yellow-500 text-black h-12 rounded-lg font-bold flex items-center justify-center gap-2 cursor-pointer transition-all shadow-sm">
                            <i class="fas fa-bolt text-xs"></i>
                            <span class="text-md whitespace-nowrap">Order Now</span>
                        </button>

                        <!-- Wishlist  -->
                        <button onclick="toggleWishlist({{ $product->id }})"
                            class="flex-1 bg-white border-2 border-gray-100 hover:border-red-200 hover:text-red-500 text-gray-600 h-12 rounded-lg font-bold flex items-center justify-center gap-2 cursor-pointer transition-all group shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="lucide lucide-heart text-red-500 group-hover:fill-red-500 transition-all">
                                <path
                                    d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z">
                                </path>
                            </svg>
                            <span class="text-md whitespace-nowrap">Wishlist</span>
                        </button>
                    </div>

                    <!-- Trust Icons -->
                    <div class="space-y-3 text-md mb-6">
                        <div class="text-[#00A651] flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-truck h-5 w-5">
                                <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                                <path d="M15 18H9"></path>
                                <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                                </path>
                                <circle cx="17" cy="18" r="2"></circle>
                                <circle cx="7" cy="18" r="2"></circle>
                            </svg>
                            Free delivery on orders over {{ $setup->currency }} 5,000
                        </div>
                        <div class="text-[#3B82F6] flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-shield h-5 w-5">
                                <path
                                    d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z">
                                </path>
                            </svg>
                            Order before stock runs out!
                        </div>
                        <div class="text-[#9333EA] flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-rotate-ccw h-5 w-5">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                            </svg>
                            Pay when you receive the product!</div>
                        <div class="text-[#F15A24] flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-star h-5 w-5">
                                <path
                                    d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z">
                                </path>
                            </svg>
                            Home delivery across Bangladesh within 72 hours</div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg text-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-rotate-ccw h-5 w-5 text-orange-500">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                            </svg>
                            <div class="text-[12px] md:text-sm font-medium">Easy Return</div>
                        </div>
                        <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg text-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-star h-5 w-5 text-orange-500">
                                <path
                                    d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z">
                                </path>
                            </svg>
                            <div class="text-[12px] md:text-sm font-medium">Best Quality</div>
                        </div>
                        <div class="flex items-center space-x-2 p-3 bg-gray-50 rounded-lg text-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-truck h-5 w-5 text-orange-500">
                                <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                                <path d="M15 18H9"></path>
                                <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                                </path>
                                <circle cx="17" cy="18" r="2"></circle>
                                <circle cx="7" cy="18" r="2"></circle>
                            </svg>
                            <div class="text-[12px] md:text-sm font-medium">Fast Shipping</div>
                        </div>
                    </div>

                    <div class="text-center mb-4 text-gray-700">Call or WhatsApp to order directly</div>

                    <div class="grid grid-cols-2 gap-3">

                        <a href="tel:{{ $setup->phone }}"
                            class="bg-[#EE4D2D] hover:bg-red-600 text-white h-11 rounded-xl flex items-center justify-center gap-3 font-bold transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-phone h-5 w-5 mr-2">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                </path>
                            </svg>
                            Call Now
                        </a>

                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $setup->phone) }}?text=Assalamu Alaikum, I want to order: {{ $product->title }}"
                            target="_blank"
                            class="bg-[#25D366] hover:bg-green-600 text-white h-11 rounded-xl flex items-center justify-center gap-3 font-bold transition-colors">
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
            <div class="flex items-center justify-center border-b border-gray-100 bg-[#F9FAFB]" id="tabs-nav">
                <button onclick="switchTab('description')" id="tab-btn-description"
                    class="tab-btn px-8 py-4 text-sm transition-all border-b-2 border-[#FF6A00] text-gray-900 bg-white font-bold">Description</button>

                <button onclick="switchTab('specification')" id="tab-btn-specification"
                    class="tab-btn px-8 py-4 text-sm transition-all border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-bold">Specification</button>

                <button onclick="switchTab('review')" id="tab-btn-review"
                    class="tab-btn px-8 py-4 text-sm transition-all border-b-2 border-transparent text-gray-600 hover:text-gray-900 font-bold">Reviews
                    (42)</button>
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
                <div id="tab-content-review" class="tab-content hidden">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Customer Reviews</h3>

                    <!-- Review Summary Card -->
                    <div class="bg-gray-50/50 rounded-xl p-6 mb-10 border border-gray-100">
                        <div class="text-3xl font-bold text-orange-500 mb-1">4.8</div>
                        <div class="flex text-yellow-400 text-sm mb-1 gap-0.5">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                class="fas fa-star"></i><i class="fas fa-star-half-alt text-gray-300"></i>
                        </div>
                        <div class="text-sm text-gray-800 font-medium">42 Reviews</div>
                    </div>

                    <!-- Individual Reviews List -->
                    <div class="space-y-0">

                        <!-- Review 1 -->
                        <div class="py-8 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div
                                    class="w-10 h-10 bg-orange-500 text-white rounded-full flex items-center justify-center text-md font-bold shrink-0">
                                    RH</div>

                                <div class="flex-1">
                                    <!-- Name, Stars and Date on Same Line -->
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <h4 class="text-md text-gray-800">Rahim Uddin</h4>
                                        <div class="flex text-yellow-400 text-[10px] gap-0.5">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="fas fa-star"></i>
                                        </div>
                                        <span class="text-sm text-gray-500">2 days ago</span>
                                    </div>

                                    <!-- Comment -->
                                    <p class="text-md text-gray-600 leading-relaxed mb-3">Great product! The sound quality is excellent and the light is very nice. Excellent value for money. I would recommend it to everyone.
                                    </p>

                                    <!-- Variant Tag -->
                                    <span
                                        class="inline-block bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-sm font-medium">Blue
                                        - Medium</span>
                                </div>
                            </div>
                        </div>

                        <!-- Review 2 -->
                        <div class="py-8 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div
                                    class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center text-md font-bold shrink-0">
                                    F</div>

                                <div class="flex-1">
                                    <!-- Name, Stars and Date -->
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <h4 class="text-md text-gray-900">Fatema Khatun</h4>
                                        <div class="flex text-yellow-400 text-[10px] gap-0.5">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="far fa-star text-gray-300"></i>
                                        </div>
                                        <span class="text-sm text-gray-500">1 week ago</span>
                                    </div>

                                    <!-- Comment -->
                                    <p class="text-md text-gray-600 leading-relaxed mb-3">Good product. The battery life could have been a bit longer. Still satisfied.</p>

                                    <!-- Variant Tag -->
                                    <span
                                        class="inline-block bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-sm font-medium">Red
                                        - Small</span>
                                </div>
                            </div>
                        </div>
                        <!-- Review 2 -->
                        <div class="py-8 border-b border-gray-100 last:border-0">
                            <div class="flex items-start gap-4">
                                <!-- Avatar -->
                                <div
                                    class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center text-md font-bold shrink-0">
                                    F</div>

                                <div class="flex-1">
                                    <!-- Name, Stars and Date -->
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <h4 class="text-md text-gray-900">Fatema Khatun</h4>
                                        <div class="flex text-yellow-400 text-[10px] gap-0.5">
                                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="fas fa-star"></i><i class="fas fa-star"></i><i
                                                class="far fa-star text-gray-300"></i>
                                        </div>
                                        <span class="text-sm text-gray-500">1 week ago</span>
                                    </div>

                                    <!-- Comment -->
                                    <p class="text-md text-gray-600 leading-relaxed mb-3">Good product. The battery life could have been a bit longer. Still satisfied.</p>

                                    <!-- Variant Tag -->
                                    <span
                                        class="inline-block bg-gray-100 text-gray-500 text-xs px-2.5 py-1 rounded-sm font-medium">Red
                                        - Small</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </section>
@endsection

@push('scripts')
    <script>
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
        //for Wishlist
        function toggleWishlist(productId) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch("{{ route('wishlist.toggle') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        product_id: productId
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'unauthorized') {
                        toastr.info(data.message);
                    } else if (data.status === 'added') {
                        toastr.success(data.message);
                    } else if (data.status === 'removed') {
                        toastr.info(data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    toastr.error("Server error occurred or your session has expired.");
                });
        }
    </script>
@endpush
