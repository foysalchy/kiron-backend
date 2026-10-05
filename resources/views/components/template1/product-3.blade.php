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

    $secondaryColor = str_replace('##', '#', trim(data_get($company->theme_template, 'secondary_color', '#FFA500')));
    $hexColor = ltrim($secondaryColor, '#');
    if (preg_match('/^[a-f0-9]{3}$|^[a-f0-9]{6}$/i', $hexColor)) {
        if (strlen($hexColor) === 3) {
            $hexColor = implode('', array_map(fn ($digit) => $digit . $digit, str_split($hexColor)));
        }

        $rgb = array_map('hexdec', str_split($hexColor, 2));
        $rgb = array_map(function ($channel) {
            $channel /= 255;
            return $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
        }, $rgb);
        $luminance = 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
        $badgeTextColor = $luminance > 0.179 ? '#000000' : '#ffffff';
    } else {
        $badgeTextColor = '#000000';
    }

    $isOutOfStock = $product->manage_stock ? ($product->available_stock <= 0) : false;
@endphp

<div
    class="max-w-[348px] relative group cursor-pointer bg-white border border-[#ddd] rounded-2xl flex flex-col h-full overflow-hidden transition-all duration-300 hover:shadow-md">

    <div class="relative">
        <a href="{{ url($product->slug ?? $product->id) }}"
            class="hover:border-2 hover:border-[var(--primary-color)]   relative block  border-b border-gray-100 overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.03)] bg-[#f9f9f9]">
            <div class="w-full h-full  flex items-center justify-center">
                <img src="{{ $product->thumbnail_url ?? asset('./images/template1/frontend/cover.webp') }}"
                    alt="{{ $product- loading="lazy">title }}" loading="lazy"
                    class="w-full aspect-square object-cover transition-transform duration-700 group-hover:scale-110" />
            </div>
        </a>

        <div class="absolute -top-1 -right-1 z-20">
            <div class="bg-white p-1 rounded-full">
                <button type="button" onclick="toggleWishlist({{ $product->id }})"
                    class="w-10 h-10 md:w-11 md:h-11 rounded-full flex items-center justify-center shadow-md active:scale-90 transition-all cursor-pointer {{ $isWishlisted ? 'bg-red-500 text-white' : 'bg-[var(--primary-color)] text-white' }}"
                    aria-label="Add to Wishlist">
                    <i class="{{ $isWishlisted ? 'fa-solid' : 'fa-regular' }} fa-heart text-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="p-4 flex flex-col flex-grow ">
        <a href="{{ url($product->slug ?? $product->id) }}" class="block flex-grow">
            <p
                class="  md:text-[17px] text-[14px]  text-gray-800 line-clamp-2 mb-2 min-h-[40px] group-hover/title:text-[#BD4F00] transition-colors">
                {{ $product->title }}
            </p>
        </a>

        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 ">
            <div class="flex items-center gap-2">
                <span class="text-[#005c7a] text-lg md:text-xl font-black font-semibold">
                    @if(($setup->currency_position ?? 'left') == 'left')
                        {{ $setup->currency ?? '৳' }}{{ number_format($salePrice) }}{{ $isVar ? '+' : '' }}
                    @else
                        {{ number_format($salePrice) }}{{ $isVar ? '+' : '' }}{{ $setup->currency ?? '৳' }}
                    @endif
                </span>

                @if ($regularPrice > $salePrice)
                    <span class="text-[#52525b] text-xs md:text-sm font-semibold line-through">
                        @if(($setup->currency_position ?? 'left') == 'left')
                            {{ $setup->currency ?? '৳' }}{{ number_format($regularPrice) }}
                        @else
                            {{ number_format($regularPrice) }}{{ $setup->currency ?? '৳' }}
                        @endif
                    </span>
                @endif
            </div>

            <div class="basis-full h-0 sm:hidden"></div>

           @if ($discountLabel)
            <span
                class="secondary-bg text-[10px] md:text-xs font-semibold font-black px-2 py-0.5 rounded-full uppercase
                    max-md:absolute max-md:top-1 max-md:left-1" style="color: {{ $badgeTextColor }} !important;">
                -{{ $discountLabel }} OFF
            </span>
        @endif
        </div>

        <button {{ $isOutOfStock ? 'disabled' : '' }}
            onclick="{{ $product->type === 'single' ? "addSingleToCart($product->id)" : "openVariationModal($product->id)" }}"
            class="w-full text-white text-center text-sm md:text-base rounded-full border-0 mt-4 py-2 transition-all cursor-pointer px-4 font-bold shadow-sm flex items-center justify-center gap-2
            {{ $isOutOfStock ? 'bg-gray-400 cursor-not-allowed' : 'bg-[var(--primary-color)] hover:primary-bg active:scale-95' }}">

            @if ($isOutOfStock)
                <i class="fas fa-exclamation-circle"></i> Stock Out
            @else
                <i class="fas fa-shopping-cart text-xs"></i> Cart
            @endif
        </button>
    </div>
</div>



@once
    @push('scripts')
        <script>
            const token = document.querySelector('meta[name="csrf-token"]').content;

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
                        'Accept': 'application/json',
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
