@props(['product', 'company'])

@php
    $isWishlisted = false;
    if (auth('customer')->check()) {
        $isWishlisted = \App\Models\Wishlist::where('customer_id', auth('customer')->id())
            ->where('product_id', $product->id)
            ->exists();
    }

    $priceData = $product->display_price_data;
    $salePrice = $priceData->sale_price;
    $regularPrice = $priceData->regular_price;
    $isVar = $priceData->is_variation;

    $discountLabel = '';
    if ($regularPrice > $salePrice && $regularPrice > 0) {
        $diff = $regularPrice - $salePrice;
        $percentage = round(($diff / $regularPrice) * 100);
        if ($percentage > 0) {
            $discountLabel = $percentage . '%';
        }
    }
    $isOutOfStock = $product->manage_stock ? ($product->available_stock <= 0) : false;
@endphp

<div
    class="group bg-white border border-gray-100 rounded-xl flex flex-col h-full overflow-hidden transition-all duration-300 hover:shadow-xl">

    <!-- image section -->
    <div class="relative overflow-hidden aspect-square bg-[#f9f9f9]">
        <!-- Discount Badge -->
        <!-- Badges (Top Left) -->
        <div class="absolute top-3 left-3 z-20 flex flex-col items-start gap-1">
            <!-- Discount Badge -->
            @if ($discountLabel)
                <span class="bg-[#be123c] text-white text-xs md:text-xs font-bold px-2 py-1 rounded-md shadow-sm">
                    -{{ $discountLabel }}
                </span>
            @endif
            <!-- Free Shipping Badge -->
            @if ($product->is_free_delivery)
                <span class="bg-green-500 text-white text-xs md:text-xs font-bold px-2 py-1 rounded-md shadow-sm">
                    Free Shipping
                </span>
            @endif
        </div>

        <!-- Wishlist -->
        <button type="button" onclick="toggleWishlist({{ $product->id }})" aria-label="wish button"
            class="absolute top-3 right-3 z-30 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md transition-transform active:scale-90 cursor-pointer">
            <i id="wish-icon-{{ $product->id }}"
                class="wish-icon-{{ $product->id }} {{ $isWishlisted ? 'fa-solid fa-heart text-red-500' : 'fa-regular fa-heart text-gray-400' }} text-sm"></i>
        </button>

        <a href="{{ url($product->slug ?? $product->id) }}" class="block w-full h-full "
            aria-label="product details">
            <img src="{{ $product->thumbnail_310_url ?? $product->thumbnail_url ?? asset('./images/template1/frontend/cover.webp') }}"
                alt="{{ $product->title }}" width="300" height="300" loading="lazy"
                class="w-full h-full object-contain transition-transform duration-700 group-hover:scale-110">
        </a>
    </div>

    <!-- info area -->
    <div class="p-4 flex flex-col flex-grow bg-white">
        {{-- <!-- categories/sku -->
        <div class="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-1 truncate">
            {{ $product->mega_categories?->pluck('name')->implode(', ') ?: 'General' }}
        </div> --}}

        <a href="{{ url($product->slug ?? $product->id) }}" class="block flex-grow"
            aria-label="product details">
            <p
                class="  md:text-[17px] text-[15px]  text-gray-800 line-clamp-2  min-h-[30px] group-hover/title:text-[#BD4F00] transition-colors">
                {{ $product->title }}
            </p>
        </a>

        <!-- Price section -->
        <div class="flex items-center gap-2">
            {{-- রেগুলার প্রাইস (কাটা দাম) --}}
            @if ($regularPrice > $salePrice)
                <span class="text-gray-600 text-xs md:text-sm line-through font-semibold">
                    @if(($setup->currency_position ?? 'left') == 'left')
                        {{ $setup->currency }}{{ number_format($regularPrice) }}
                    @else
                        {{ number_format($regularPrice) }}{{ $setup->currency }}
                    @endif
                </span>
            @endif

            {{-- বর্তমান সেল প্রাইস --}}
            <span class="text-[#be123c] text-base md:text-lg font-black font-semibold">
                @if(($setup->currency_position ?? 'left') == 'left')
                    {{ $setup->currency }}{{ number_format($salePrice) }}
                @else
                    {{ number_format($salePrice) }}{{ $setup->currency }}
                @endif
            </span>
        </div>

        <!-- size variations snippet (If it's a variation product) -->
        <div class="mt-2 mb-2 flex flex-wrap gap-2">
            @if ($product->type === 'variation')
                @php
                    $previewValues = $product->variations->flatMap->attributes
                        ->pluck('attributeValue.name')
                        ->unique()
                        ->take(5);
                @endphp
                @foreach ($previewValues as $val)
                    <span
                        class="min-w-[30px] h-7 px-2 flex items-center justify-center border border-gray-200 rounded text-xs font-bold text-gray-600 hover:border-[var(--primary-color)] transition-colors">
                        {{ $val }}
                    </span>
                @endforeach
            @endif
        </div>

        <!-- action button -->
        <button {{ $isOutOfStock ? 'disabled' : '' }}
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
            aria-label="cart button" class="w-full text-white text-center text-xs md:text-sm font-bold rounded-lg mt-2 py-3 transition-all cursor-pointer shadow-sm flex items-center justify-center gap-2
            {{ $isOutOfStock ? 'bg-gray-300' : 'bg-[#be123c] hover:bg-red-700 active:scale-95' }}">

            @if ($isOutOfStock)
                STOCK OUT
            @else
                {{ $product->type === 'single' ? (isset($setup->lang) && $setup->lang == 'bn' ? 'কার্টে যোগ করুন' : 'ADD TO CART') : 'SELECT OPTIONS' }}
            @endif
        </button>
    </div>
</div>


@once
    @push('scripts')
        <script>
            const token = document.querySelector('meta[name="csrf-token"]').content;

            // product variation modal related scripts
            let modalSelectedIds = [];

            // ১. ভ্যারিয়েশন সিলেকশন লজিক (সংশোধিত)
            function handleModalSelection(btn, varId, price, type) {
                const activeClasses = ['border-[var(--primary-color)]', 'bg-orange-50', 'ring-1', 'ring-[var(--primary-color)]'];

                // টাইপটি ছোট হাতের অক্ষরে চেক করা হচ্ছে যাতে ভুল না হয়
                const isMultiple = (type.toLowerCase() === 'multiple');

                if (isMultiple) {
                    // মাল্টিপল টাইপ: টগল (Add/Remove) লজিক
                    if (modalSelectedIds.includes(varId)) {
                        modalSelectedIds = modalSelectedIds.filter(id => id !== varId);
                        btn.classList.remove(...activeClasses);
                    } else {
                        modalSelectedIds.push(varId);
                        btn.classList.add(...activeClasses);
                    }
                } else {
                    // সিঙ্গেল টাইপ: রেডিও লজিক
                    modalSelectedIds = [varId];
                    document.querySelectorAll('.modal-var-btn').forEach(el => el.classList.remove(...activeClasses));
                    btn.classList.add(...activeClasses);
                }

                // আইডিগুলো হিডেন ইনপুটে রাখা
                document.getElementById('modal-selected-ids').value = modalSelectedIds.join(',');
                updateModalTotal();
            }

            function updateModalTotal() {
                const qty = parseInt(document.getElementById('modal-qty').value) || 1;
                const totalDisplay = document.getElementById('modal-total-price-display');
                const currency = "{{ $setup->currency }}";
                const pos = "{{ $setup->currency_position ?? 'left' }}";

                let totalSum = 0;
                document.querySelectorAll('.modal-var-btn.bg-orange-50').forEach(el => {
                    totalSum += parseFloat(el.getAttribute('data-price') || 0);
                });

                const finalTotal = totalSum * qty;

                if (totalDisplay) {
                    if (totalSum > 0) {
                        totalDisplay.innerText = (pos === 'left') ? currency + " " + finalTotal.toLocaleString() : finalTotal.toLocaleString() + " " + currency;
                    } else {
                        totalDisplay.innerText = (pos === 'left') ? currency + " 0" : "0 " + currency;
                    }
                }
            }


            // quantity change function for variation modal
            function changeQty(val) {
                let qtyInput = document.getElementById('modal-qty');
                if (qtyInput) {
                    let newVal = parseInt(qtyInput.value) + val;
                    if (newVal >= 1) {
                        qtyInput.value = newVal;
                        updateModalTotal();
                    }
                }
            }

            function openVariationModal(id) {
                modalSelectedIds = []; // শুরুতে ক্লিয়ার
                const modal = document.getElementById('variation-modal');
                const contentArea = document.getElementById('modal-content-area');
                if (!modal) return;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                contentArea.innerHTML = '<div class="py-10 text-center"><i class="fas fa-spinner fa-spin text-2xl text-[var(--primary-color)]"></i></div>';

                fetch("/product-variation/" + id)
                    .then(res => res.text())
                    .then(html => {
                        contentArea.innerHTML = html;

                        const firstBtn = contentArea.querySelector('.modal-var-btn');
                        if (firstBtn) {
                            const firstId = parseInt(firstBtn.getAttribute('data-var-id'));
                            if (!modalSelectedIds.includes(firstId)) {
                                modalSelectedIds.push(firstId);
                            }
                            document.getElementById('modal-selected-ids').value = modalSelectedIds.join(',');
                        }
                        updateModalTotal();
                    });
            }

            function closeModal() {
                const modal = document.getElementById('variation-modal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }

            function processAddVariation() {
                if (modalSelectedIds.length === 0) {
                    toastr.warning("Please select at least one option."); return;
                }

                const qty = document.getElementById('modal-qty').value;
                const token = document.querySelector('meta[name="csrf-token"]').content;
                const items = modalSelectedIds.map(id => ({ variation_id: id, qty: qty }));

                fetch("{{ route('cart.add') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ items: items })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // আপনার বিদ্যমান পিক্সেল ট্র্যাকিং লজিক
                            if (typeof fbq === 'function') {
                                fbq('track', 'AddToCart', { content_ids: ['{{ $product->id }}'], content_type: 'product', value: {{ $product->sale_price ?? 0 }} * qty, currency: '{{ $setup->currency ?? "BDT" }}' });
                            }
                            if (typeof ttq === 'function') {
                                ttq.track('AddToCart', { content_id: '{{ $product->id }}', content_type: 'product', value: {{ $product->sale_price ?? 0 }} * qty, currency: '{{ $setup->currency ?? "BDT" }}' });
                            }

                            document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                            closeModal();
                            modalSelectedIds = []; // রিসেট

                            if (typeof isOrderNowGlobal !== 'undefined' && isOrderNowGlobal) {
                                window.location.href = "{{ route('checkout.index') }}";
                            } else {
                                toastr.success(data.message);
                            }
                        }
                    });
            }

            function addSingleToCart(id, isOrderNow = false) {
                const token = document.querySelector('meta[name="csrf-token"]').content;

                const postData = {
                    items: [{
                        id: id,
                        qty: 1
                    }]
                };

                fetch("{{ route('cart.add') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json', // এটি যোগ করা জরুরি
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(postData)
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            if (typeof fbq === 'function') {
                                fbq('track', 'AddToCart', {
                                    content_ids: ['{{ $product->id }}'],
                                    content_type: 'product',
                                    value: {{ $product->sale_price ?? 0 }},
                                    currency: '{{ $setup->currency ?? "BDT" }}'
                                });
                            }
                            if (typeof ttq === 'function') {
                                ttq.track('AddToCart', {
                                    content_id: '{{ $product->id }}',
                                    content_type: 'product',
                                    value: {{ $product->sale_price ?? 0 }},
                                    currency: '{{ $setup->currency ?? "BDT" }}'
                                });
                            }
                            document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                            if (isOrderNow) {
                                window.location.href = "{{ route('checkout.index') }}";
                            } else {
                                toastr.success(data.message);
                            }
                        } else {
                            toastr.error(data.message);
                        }
                    }).catch(err => toastr.error("Server error."));
            }
        </script>
    @endpush
@endonce
