<div class="group relative flex flex-col p-4 bg-white border border-gray-200 rounded-lg hover:shadow-xl transition-all duration-300">

    <!-- Image Section -->
    <div class="relative w-[200px] aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3">
        <img src="{{ $product->thumbnail_url }}"
            class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
            alt="{{ $product->title }}">

        {{-- Discount Badge (Original Logic) --}}
        @if ($product->discount > 0)
            <div
                class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">
                -{{ $product->discount_type == 'percent' ? (int) $product->discount . '%' : '৳' . (int) $product->discount }}
            </div>
        @endif

        <!-- HOVER ICONS (Update: Dynamic Add to Cart) -->
        <div
            class="absolute inset-0 bg-black/10 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[1px]">
            <a href="{{ route('product.details', $product->slug ?? $product->id) }}"
                class="w-9 h-9 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-eye h-4 w-4">
                    <path
                        d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0">
                    </path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </a>

            {{-- Hover Cart Icon Logic --}}
            <button
                onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
                class="w-9 h-9 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="lucide lucide-shopping-cart h-4 w-4">
                    <circle cx="8" cy="21" r="1"></circle>
                    <circle cx="19" cy="21" r="1"></circle>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Product Info -->
    <div class="flex flex-col flex-1">
        <h3
            class="text-md font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[36px] group-hover:text-[#FF6A00] transition-colors">
            {{ $product->title }}
        </h3>

        <div class="flex items-center gap-1 mb-2">
            <div class="flex text-yellow-400 text-[11px]">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                    class="fas fa-star"></i><i class="fas fa-star text-gray-200"></i>
            </div>
            <span class="text-[11px] text-gray-400 font-bold">(25)</span>
        </div>

        <!-- Price Row (Update: Same as Home Logic) -->
        <div class="mt-auto flex items-center justify-between gap-2">
            <div class="flex items-baseline gap-2">
                @if ($product->display_price_data->sale_price > 0)
                    <span class="text-md font-bold text-[#FF6A00]">
                        ৳{{ number_format($product->display_price_data->sale_price) }}{{ $product->display_price_data->is_variation ? '+' : '' }}
                    </span>

                    @if ($product->display_price_data->regular_price > $product->display_price_data->sale_price)
                        <span class="text-sm text-gray-400 line-through">
                            ৳{{ number_format($product->display_price_data->regular_price) }}
                        </span>
                    @endif
                @endif
            </div>

            {{-- Main Add to Cart Button Logic --}}
            <button
                onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
                class="bg-[#1D2128] text-white px-2 py-2 rounded-lg text-sm font-semibold hover:bg-[#FF6A00] transition-colors shrink-0">
                Add to Cart
            </button>
        </div>
    </div>
</div>
