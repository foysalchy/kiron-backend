@extends('template1.layouts.front')

@section('content')
    <section class="py-6 container mx-auto">

        <!-- Main Card Container -->
        <div class="archiveTopInfo">

            <!-- Breadcrumb -->
            <nav class="hidden md:flex items-center gap-2 mb-6 text-md font-medium text-gray-500">
                <a href="/" class="hover:text-gray-500 transition-colors">Home</a>

                <!-- Chevron Icon -->
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" class="w-4 h-4 text-gray-500"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                </svg>
                <a href="{{ url('/shop') }}" class="text-gray-500 hover:text-[#f15a24] transition-colors">Category</a>

                <!-- Chevron Icon -->
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" class="w-4 h-4 text-gray-500"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"></path>
                </svg>

                <span class="text-[#f15a24]">{{ $category->name ?? 'Shop' }}</span>
            </nav>

            <!-- Page Title -->
            <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">
                {{ $category->name ?? 'All Products' }}
            </h2>


        </div>

    </section>
    <!-- SHOP PAGE SECTION -->
    <section class="py-6 container mx-auto">

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- ══════════════════════════ SIDEBAR ═════════════════════════════ -->
            <aside class="w-full lg:w-[220px] shrink-0">
                <form action="{{ url()->current() }}" method="GET" id="sidebar-filter-form">
                    <!-- সর্টিং ভ্যালু ধরে রাখার জন্য হিডেন ইনপুট -->
                    <input type="hidden" name="sort" value="{{ request('sort', 'default') }}">

                    <div class="bg-white rounded-lg shadow-xs border border-gray-200 overflow-hidden lg:sticky lg:top-[70px] lg:max-h-[calc(100vh-90px)] lg:overflow-y-auto no-scrollbar">

                        <!-- ① Filter By Price -->
                        <div class="p-4 border-b border-gray-50">
                            <h3 class="text-sm font-bold text-gray-800 mb-4">Filter By Price</h3>

                            <div class="relative h-1.5 bg-gray-100 rounded-full mb-6 mx-1">
                                <div class="absolute h-full bg-[#f15a24] rounded-full" style="left:0%;right:0%;"></div>
                                <div class="absolute w-4 h-4 bg-white rounded-full border-2 border-[#f15a24] -top-1.5 left-0 cursor-pointer shadow-sm"></div>
                                <div class="absolute w-4 h-4 bg-white rounded-full border-2 border-[#f15a24] -top-1.5 right-0 cursor-pointer shadow-sm"></div>
                            </div>

                            <div class="flex items-center gap-2 mb-4">
                                <input type="number" name="min_price" value="{{ request('min_price', 0) }}"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-[12px] outline-none focus:border-[#f15a24]">
                                <input type="number" name="max_price" value="{{ request('max_price', 5000) }}"
                                    class="w-full border border-gray-200 rounded px-2 py-1.5 text-[12px] outline-none focus:border-[#f15a24]">
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="submit"
                                    class="bg-[#f15a24] text-white px-4 py-1.5 rounded text-[12px] font-bold hover:bg-orange-600 transition-colors uppercase">
                                    Filter
                                </button>
                                <span class="text-[11px] text-gray-500 font-medium">৳{{ request('min_price', 0) }} — ৳{{ request('max_price', 5000) }}</span>
                            </div>
                        </div>

                        <!-- ② Filter By Brand header — Toggle Button -->
                        <button type="button" onclick="toggleAllFilters()"
                            class="w-full flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50">
                            <span class="text-sm font-bold text-gray-800">More Filters</span>
                            <svg id="all-filters-arrow" class="w-4 h-4 text-gray-400 transition-transform duration-300"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- ③ ALL FILTERS PANEL (Initially Hidden) -->
                        <div id="all-filters-panel" class="{{ request()->has('brand') || request()->has('attributes') ? '' : 'hidden' }}">

                            <!-- Brand List -->
                            <div class="px-4 pt-2 pb-3 border-b border-gray-50">
                                <h3 class="text-xs font-bold text-gray-400 uppercase mb-2">Brands</h3>
                                <div class="flex flex-col gap-0 text-gray-800 font-medium">
                                    @foreach($brands as $brand)
                                    @php $isSelectedBrand = in_array($brand->id, (array)request('brand')); @endphp
                                    <label class="flex items-center justify-between py-1.5 cursor-pointer group">
                                        <span class="text-sm group-hover:text-[#f15a24] {{ $isSelectedBrand ? 'text-[#f15a24]' : '' }}">
                                            {{ $brand->name }}
                                        </span>
                                        <div class="relative flex items-center">
                                            <input type="checkbox" name="brand[]" value="{{ $brand->id }}" onchange="this.form.submit()"
                                                {{ $isSelectedBrand ? 'checked' : '' }}
                                                class="absolute opacity-0 w-4 h-4 cursor-pointer z-10">
                                            <div class="w-4 h-4 border {{ $isSelectedBrand ? 'border-[#f15a24] bg-[#f15a24]/10' : 'border-gray-300' }} rounded-full group-hover:border-[#f15a24] shrink-0 flex items-center justify-center">
                                                @if($isSelectedBrand)
                                                    <div class="w-1.5 h-1.5 bg-[#f15a24] rounded-full"></div>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Dynamic Attributes (Color, Size, Style) -->
                            @foreach($attributeGroups as $group)
                            <div class="px-4 pt-3 pb-3 border-b border-gray-50">
                                <h3 class="text-sm font-bold text-gray-800 mb-3">Filter By {{ $group->name }}</h3>
                                <div class="flex flex-col gap-2 text-gray-800 font-medium">
                                    @foreach($group->values as $value)
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox" name="attributes[{{ $group->id }}][]" value="{{ $value->id }}"
                                            onchange="this.form.submit()"
                                            {{ (isset(request('attributes')[$group->id]) && in_array($value->id, request('attributes')[$group->id])) ? 'checked' : '' }}
                                            class="w-3.5 h-3.5 accent-[#f15a24]">
                                        <span class="text-sm group-hover:text-[#f15a24]">{{ $value->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach

                            <!-- Clear Button -->
                            <div class="p-4">
                                <a href="{{ url()->current() }}" class="text-[11px] text-red-500 font-bold hover:underline uppercase">Clear All Filters</a>
                            </div>

                        </div><!-- /#all-filters-panel -->
                    </div>
                </form>
            </aside>

            <!-- ══════════════════════════════════════
                        MAIN CONTENT
                    ══════════════════════════════════════ -->
            <main class="flex-1 bg-white rounded-lg shadow-xs border border-gray-200 overflow-hidden">

                <!-- Shop Header -->
                <div class="px-5 py-3.5 flex items-center justify-between">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">
                        {{ $category ? $category->name : 'আমাদের সব পণ্য' }}
                    </h2>
                    <div class="relative">
                        <form action="" method="GET" id="sortForm">
                            <select name="sort" onchange="document.getElementById('sortForm').submit()"
                                class="appearance-none bg-white border border-gray-200 text-gray-600 text-md rounded-md pr-8 pl-3 py-1.5 outline-none focus:ring-1 focus:ring-[#f15a24] cursor-pointer">
                                <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Default Sorting</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            </select>
                        </form>
                        <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-gray-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Product Grid  -->
                <div class="p-4 grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-4">

                     @forelse($products as $product)
                        <div class="group relative flex flex-col p-4 bg-white border border-gray-200 rounded-lg hover:shadow-xl transition-all duration-300">

                            <!-- Image Section -->
                            <div class="relative w-full aspect-square overflow-hidden bg-gray-50 rounded-lg mb-3">
                                <img src="{{ $product->thumbnail_url }}"
                                    class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500"
                                    alt="{{ $product->title }}">

                                {{-- Discount Badge (Original Logic) --}}
                                @if($product->discount > 0)
                                    <div class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-sm">
                                        -{{ $product->discount_type == 'percent' ? (int)$product->discount . '%' : '৳' . (int)$product->discount }}
                                    </div>
                                @endif

                                <!-- HOVER ICONS (Update: Dynamic Add to Cart) -->
                                <div class="absolute inset-0 bg-black/10 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[1px]">
                                    <a href="{{ route('product.details', $product->slug ?? $product->id) }}"
                                        class="w-9 h-9 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye h-4 w-4"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </a>

                                    {{-- Hover Cart Icon Logic --}}
                                    <button onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
                                        class="w-9 h-9 bg-white text-gray-800 rounded-full flex items-center justify-center hover:bg-[#FF6A00] hover:text-white transition-all shadow-md">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart h-4 w-4"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="flex flex-col flex-1">
                                <h3 class="text-md font-medium leading-[1.4] text-gray-800 line-clamp-2 mb-2 min-h-[36px] group-hover:text-[#FF6A00] transition-colors">
                                    {{ $product->title }}
                                </h3>

                                <div class="flex items-center gap-1 mb-2">
                                    <div class="flex text-yellow-400 text-[11px]">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star text-gray-200"></i>
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

                                            @if($product->display_price_data->regular_price > $product->display_price_data->sale_price)
                                                <span class="text-sm text-gray-400 line-through">
                                                    ৳{{ number_format($product->display_price_data->regular_price) }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- Main Add to Cart Button Logic --}}
                                    <button onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
                                        class="bg-[#1D2128] text-white px-3 py-2 rounded-lg text-sm font-bold hover:bg-[#FF6A00] transition-colors shrink-0">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 text-center">
                            <i class="fas fa-box-open text-5xl text-gray-200 mb-4"></i>
                            <p class="text-gray-500 font-medium">No products found in this category.</p>
                        </div>
                    @endforelse


                </div>
                <div class="mt-12 flex flex-col items-center gap-4 border-t border-gray-100 pt-8 pb-8">
                    <p class="text-sm text-gray-500 font-medium">
                        Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
                    </p>

                    <div class="flex justify-center">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                </div>
            </main>

        </div>

    </section>
    <div id="variation-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 relative">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 text-2xl">&times;</button>
            <div id="modal-content-area"></div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        function addToCart(price) {
            alert("পণ্যটি কার্টে যোগ করা হয়েছে! দাম: ৳" + price);

            window.location.href = "./cart.html";
        }

        function toggleAllFilters() {
            const panel = document.getElementById('all-filters-panel');
            const arrow = document.getElementById('all-filters-arrow');
            const isHidden = panel.classList.contains('hidden');

            if (isHidden) {
                panel.classList.remove('hidden');
                arrow.style.transform = 'rotate(180deg)';
            } else {
                panel.classList.add('hidden');
                arrow.style.transform = 'rotate(0deg)';
            }
        }
    </script>
    <script>
        function addSingleToCart(id) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            fetch("{{ route('cart.add') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ id: id, qty: 1 })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                    toastr.success(data.message);
                }
            });
        }

        function openVariationModal(id) {
            const modal = document.getElementById('variation-modal');
            const contentArea = document.getElementById('modal-content-area');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            contentArea.innerHTML = '<div class="py-10 text-center"><i class="fas fa-spinner fa-spin text-2xl text-[#FF6A00]"></i></div>';
            fetch("/product-variation/" + id)
                .then(res => res.text())
                .then(html => { contentArea.innerHTML = html; });
        }

        function closeModal() {
            const modal = document.getElementById('variation-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function processAddVariation() {
            const selectedVariant = document.querySelector('input[name="selected_variant"]:checked');
            const qtyInput = document.getElementById('modal-qty');
            const token = document.querySelector('meta[name="csrf-token"]').content;
            if(!selectedVariant) { toastr.warning("দয়া করে অপশন সিলেক্ট করুন।"); return; }

            fetch("{{ route('cart.add') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ variation_id: selectedVariant.value, qty: qtyInput ? qtyInput.value : 1 })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                    closeModal();
                    toastr.success(data.message);
                }
            });
        }

        function changeQty(val) {
            let qtyInput = document.getElementById('modal-qty');
            if(qtyInput) {
                let newVal = parseInt(qtyInput.value) + val;
                if(newVal >= 1) qtyInput.value = newVal;
            }
        }

        function toggleAllFilters() {
            const panel = document.getElementById('all-filters-panel');
            const arrow = document.getElementById('all-filters-arrow');
            panel.classList.toggle('hidden');
            arrow.style.transform = panel.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    </script>
@endpush
