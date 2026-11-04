<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('components.meta-info.ecommerce-meta.landing-meta', ['landing' => $landing, 'setup' => $setup])
    <link rel="icon" type="image/x-icon" href="{{ $setup->favicon_url }}">
    @include('components.fontawesome')
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('components.meta-info.pixel', ['landing' => $landing, 'setup' => $setup])

    <style>
        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            overflow-x: hidden;
            font-family:
                {!! $setup->lang === 'bn' ? "'Hind Siliguri', sans-serif" : "'Manrope', sans-serif" !!}
            ;
        }
        .custom-landing-wrapper {
            width: 100%;
        }
        .custom-landing-wrapper > p,
        .custom-landing-wrapper > div:not([id="order"]):not([id="landing-order-section"]) {
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        /* Isolate order form summary from custom global styles */
        #landing-order-section, #landing-order-section * {
            box-sizing: border-box;
        }
        #landing-order-section .sticky {
            position: static !important;
            transform: none !important;
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        #landing-order-section .lg\:col-span-5 {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            width: auto !important;
        }
        #landing-order-section .price {
            display: block !important;
            margin-top: 0 !important;
            font-size: inherit !important;
            font-weight: inherit !important;
            gap: 0 !important;
        }
        #landing-order-section .total {
            margin-top: 0 !important;
            font-size: inherit !important;
            font-weight: inherit !important;
        }
        #landing-order-section .btn {
            box-shadow: none !important;
            transform: none !important;
        }
        #landing-order-section h1,
        #landing-order-section h2,
        #landing-order-section h3,
        #landing-order-section p {
            letter-spacing: normal !important;
        }
    </style>
</head>

<body class="antialiased bg-white text-gray-900">
    @php
        $rawContent = $landing->description ?? '';
        $isPreviewMode = !empty($isPreview) || request()->is('*landing/preview*') || !empty($landing->is_preview);
        $multiProducts = isset($products) && $products->isNotEmpty() ? $products : ($landing->products ?? collect([$landing->product]));

        // Render all product cards grid html
        $productCardsHtml = '';
        if ($multiProducts->isNotEmpty()) {
            $productCardsHtml = '<div id="landing-products-section" class="my-8 max-w-6xl mx-auto px-4"><div class="grid grid-cols-1 ' . ($multiProducts->count() === 1 ? 'max-w-md mx-auto' : 'sm:grid-cols-2 md:grid-cols-3') . ' gap-6">';
            foreach($multiProducts as $p) {
                $productCardsHtml .= view('components.landing.landing-product-card-1', [
                    'product' => $p,
                    'setup' => $setup,
                    'landing' => $landing
                ])->render();
            }
            $productCardsHtml .= '</div></div>';
        }

        // Render the central order-form component HTML inside a dedicated wrapper with top and bottom spacing
        $orderFormHtml = '<section id="landing-order-section" class="py-12 md:py-16 my-8 px-4 scroll-mt-6 bg-[#f8faff] [&_#landing-order-form_h2]:!text-xl sm:[&_#landing-order-form_h2]:!text-2xl md:[&_#landing-order-form_h2]:!text-4xl [&_#landing-order-form_h2]:!font-bold [&_#landing-order-form_h2]:!leading-normal md:[&_#landing-order-form_h2]:!leading-relaxed">' . view('components.landing.order-form', [
            'landing' => $landing,
            'products' => $products,
            'isPreview' => $isPreviewMode
        ])->render() . '</section>';

        // Process [product_cards] and [product_card id="XX"] shortcodes
        $processedContent = $rawContent;
        if (str_contains($processedContent, '[product_cards]') || str_contains($processedContent, '{{product_cards}}')) {
            $processedContent = str_replace(['[product_cards]', '{{product_cards}}'], $productCardsHtml, $processedContent);
        }

        // Single product card shortcode regex replacement: [product_card id="123"] or [product_card id=123]
        $processedContent = preg_replace_callback('/\[product_card\s+id=[\'"]?(\d+)[\'"]?\]/i', function($m) use ($multiProducts, $setup, $landing) {
            $targetId = (int)$m[1];
            $matchedProduct = $multiProducts->firstWhere('id', $targetId) ?? \App\Models\Product::find($targetId);
            if ($matchedProduct) {
                return '<div class="my-6 max-w-sm mx-auto px-4">' . view('components.landing.landing-product-card-1', [
                    'product' => $matchedProduct,
                    'setup' => $setup,
                    'landing' => $landing
                ])->render() . '</div>';
            }
            return '';
        }, $processedContent);

        // Sanitize raw user code: remove any premature closing </body> or </html> tags that would break DOM structure
        $processedContent = preg_replace('/<\/(body|html)>/i', '', $processedContent);

        // Check if shortcode exists, otherwise append the form at the bottom
        if (str_contains($processedContent, '[order_form]') || str_contains($processedContent, '{{order_form}}')) {
            $finalHtml = str_replace(['[order_form]', '{{order_form}}'], $orderFormHtml, $processedContent);
        } else {
            $finalHtml = $processedContent . $orderFormHtml;
        }
    @endphp

        <div class="custom-landing-wrapper min-h-screen">
        {!! $finalHtml !!}
    </div>

    <script>
        // Variation change handler for shortcode product cards (matches Landing 4)
        function onCardVariantChange(selectEl) {
            const card = selectEl.closest('.product-card');
            if (!card) return;

            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            if (!selectedOpt) return;

            const imgEl = card.querySelector('.card-product-img');
            const salePriceEl = card.querySelector('.card-sale-price');
            const regPriceEl = card.querySelector('.card-reg-price');
            const addBtn = card.querySelector('.card-add-btn');

            if (imgEl && selectedOpt.dataset.img) {
                imgEl.src = selectedOpt.dataset.img;
            }
            if (salePriceEl && selectedOpt.dataset.price) {
                const priceFormatted = parseFloat(selectedOpt.dataset.price).toLocaleString(undefined, { minimumFractionDigits: 2 });
                salePriceEl.innerText = priceFormatted + "{{ $setup->currency }}";
            }
            if (regPriceEl && selectedOpt.dataset.regular) {
                const regFormatted = parseFloat(selectedOpt.dataset.regular).toLocaleString(undefined, { minimumFractionDigits: 2 });
                regPriceEl.innerText = regFormatted + "{{ $setup->currency }}";
            }

            // Update discount badge on variation change
            const discountBadgeEl = card.querySelector('.card-discount-badge');
            if (discountBadgeEl) {
                const dLabel = selectedOpt.dataset.discountLabel || '';
                if (dLabel) {
                    discountBadgeEl.innerText = dLabel;
                    discountBadgeEl.classList.remove('hidden');
                } else {
                    discountBadgeEl.classList.add('hidden');
                }
            }

            // Check out of stock status of selected variant
            const isOutOfStock = selectedOpt.dataset.outOfStock === '1';
            const orderBtn = card.querySelector('.card-order-btn');

            if (isOutOfStock) {
                if (addBtn) {
                    addBtn.disabled = true;
                    addBtn.className = "card-add-btn w-full inline-flex items-center justify-center gap-1 py-2.5 px-2 bg-gray-300 cursor-not-allowed opacity-75 text-gray-600 font-bold text-xs rounded-lg shadow-sm";
                    addBtn.innerHTML = '<i class="fa-solid fa-ban text-xs"></i> <span>স্টক আউট</span>';
                }
                if (orderBtn) {
                    orderBtn.disabled = true;
                    orderBtn.className = "card-order-btn w-full inline-flex items-center justify-center gap-1 py-2.5 px-2 bg-gray-400 cursor-not-allowed opacity-75 text-white font-bold text-xs rounded-lg shadow-sm";
                    orderBtn.innerHTML = '<span>স্টক শেষ</span>';
                }
            } else {
                if (addBtn) {
                    addBtn.disabled = false;
                    addBtn.className = "card-add-btn w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-2 bg-green-50 hover:bg-green-100 text-[#0D2601] border-2 border-[#35B11E]/40 hover:border-[#35B11E] font-bold text-xs rounded-lg transition-all active:scale-[0.98] cursor-pointer shadow-sm";
                    addBtn.innerHTML = '<i class="fa-solid fa-cart-plus text-xs text-[#35B11E]"></i> <span class="btn-label">কার্টে যোগ করুন</span>';
                }
                if (orderBtn) {
                    orderBtn.disabled = false;
                    orderBtn.className = "card-order-btn w-full inline-flex items-center justify-center gap-1.5 py-2.5 px-2 bg-[#0D2601] hover:bg-[#1a4a03] text-white font-bold text-xs rounded-lg shadow-md hover:shadow-lg transition-all active:scale-[0.98] cursor-pointer";
                    orderBtn.innerHTML = '<span class="btn-label">অর্ডার করুন</span> <i class="fa-solid fa-arrow-down text-xs"></i>';
                }
            }
        }

        // Add Product To Landing Order Function (supports scroll toggle, matches Landing 4)
        function addProductToLandingOrder(productId, shouldScroll = false) {
            const card = document.querySelector(`.product-card[data-product-id="${productId}"]`);
            let targetCheckbox = null;
            let targetVarId = null;

            if (card) {
                const selectEl = card.querySelector('.card-variant-select');
                if (selectEl) {
                    const selectedOpt = selectEl.options[selectEl.selectedIndex];
                    if (selectedOpt && selectedOpt.dataset.outOfStock === '1') {
                        alert('দুঃখিত! এই অপশনটি বর্তমানে স্টকে নেই।');
                        return;
                    }
                    targetVarId = selectEl.value;
                    targetCheckbox = document.querySelector(`.variant-checkbox[value="${targetVarId}"]`);
                } else {
                    targetCheckbox = document.querySelector(`input[name="product_ids[]"][value="${productId}"]`);
                    if (targetCheckbox && targetCheckbox.dataset.outOfStock === '1') {
                        alert('দুঃখিত! এই পণ্যটি বর্তমানে স্টকে নেই।');
                        return;
                    }
                }

                // Button click feedback on Add to Cart button if staying
                if (!shouldScroll) {
                    const btn = card.querySelector('.card-add-btn');
                    if (btn && !btn.disabled) {
                        const origContent = btn.innerHTML;
                        btn.classList.remove('bg-green-50', 'text-[#0D2601]');
                        btn.classList.add('bg-green-600', 'text-white');
                        btn.innerHTML = '<i class="fa-solid fa-check text-xs"></i> <span>যোগ হয়েছে</span>';
                        setTimeout(() => {
                            btn.classList.remove('bg-green-600', 'text-white');
                            btn.classList.add('bg-green-50', 'text-[#0D2601]');
                            btn.innerHTML = origContent;
                        }, 1500);
                    }
                }
            }

            if (targetCheckbox) {
                if (targetCheckbox.dataset.outOfStock === '1') {
                    alert('দুঃখিত! নির্বাচিত পণ্যটি বর্তমানে স্টকে নেই।');
                    return;
                }

                // Show product wrapper in order form if hidden
                const productWrapper = targetCheckbox.closest('[data-order-product-id]');
                if (productWrapper) {
                    productWrapper.classList.remove('hidden');
                }

                targetCheckbox.checked = true;
                targetCheckbox.dispatchEvent(new Event('change'));
            }

            // Smooth scroll down to order form if requested
            if (shouldScroll) {
                const orderSection = document.getElementById('landing-order-section') || document.getElementById('landing-order-form') || document.getElementById('order');
                if (orderSection) {
                    orderSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Global click handler for custom buttons/links targeting products or order form
            document.body.addEventListener('click', function (e) {
                // If click is inside a ready-made .product-card component (which has its own dedicated handler), let it handle itself
                if (e.target.closest('.product-card')) {
                    return;
                }

                const target = e.target.closest('[data-product-id], a[href="#order"], a[href="#landing-order-section"], .order-btn, .add-to-cart-btn, [data-add-to-cart]');
                if (!target) return;

                const productId = target.getAttribute('data-product-id');
                const orderForm = document.getElementById('landing-order-form') || document.getElementById('landing-order-section') || document.getElementById('order');

                // Check if target is explicitly Add to Cart (stay on same place) or Direct Order (scroll)
                const isAddToCartOnly = target.classList.contains('add-to-cart-btn') ||
                                        target.hasAttribute('data-add-to-cart') ||
                                        (target.dataset.action === 'cart') ||
                                        (target.dataset.scroll === 'false');

                // If specific product ID is linked in custom button
                if (productId) {
                    // 1. Check if product exists in multi-product order form wrapper
                    const productWrapper = document.querySelector(`[data-order-product-id="${productId}"]`);
                    if (productWrapper) {
                        productWrapper.classList.remove('hidden');

                        // If product has variations, select the first available variation
                        const varCheckboxes = productWrapper.querySelectorAll('input[name="variation_ids[]"]');
                        if (varCheckboxes.length > 0) {
                            let targetVar = Array.from(varCheckboxes).find(cb => cb.dataset.outOfStock !== '1') || varCheckboxes[0];
                            if (targetVar && !targetVar.checked) {
                                targetVar.checked = true;
                                targetVar.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        } else {
                            // Single variation or regular product checkbox inside wrapper
                            const prodCheckbox = productWrapper.querySelector('input[name="product_ids[]"], input[name="variation_ids[]"]');
                            if (prodCheckbox && !prodCheckbox.checked) {
                                prodCheckbox.checked = true;
                                prodCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    } else {
                        // Multi-product standalone checkbox mode
                        const prodCheckbox = document.querySelector(`input[name="product_ids[]"][value="${productId}"]`);
                        if (prodCheckbox) {
                            const pWrapper = prodCheckbox.closest('[data-order-product-id]');
                            if (pWrapper) pWrapper.classList.remove('hidden');
                            if (!prodCheckbox.checked) {
                                prodCheckbox.checked = true;
                                prodCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        } else {
                            // Single product mode: select radio or hidden input
                            const singleRadio = document.querySelector(`input[name="product_id"][value="${productId}"]`);
                            if (singleRadio) {
                                singleRadio.checked = true;
                                singleRadio.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    }

                    // Visual feedback for Add to Cart
                    if (isAddToCartOnly) {
                        e.preventDefault();
                        const origHtml = target.innerHTML;
                        const origBg = target.style.backgroundColor;
                        target.style.transition = 'all 0.2s';
                        target.classList.add('opacity-90');
                        
                        // Temporarily show checkmark if it contains text
                        if (!target.querySelector('.cart-added-indicator')) {
                            const indicator = document.createElement('span');
                            indicator.className = 'cart-added-indicator text-xs font-bold text-green-600 block';
                            indicator.innerText = ' ✓ কার্টে যোগ হয়েছে';
                            target.appendChild(indicator);
                            setTimeout(() => {
                                indicator.remove();
                                target.classList.remove('opacity-90');
                            }, 1800);
                        }
                        return; // GUARANTEED: DO NOT SCROLL on Add to Cart!
                    }
                }

                // If button is explicitly Add to Cart without product-id, never scroll
                if (isAddToCartOnly) {
                    e.preventDefault();
                    return;
                }

                // Direct Order buttons: smooth scroll to order form
                if (orderForm) {
                    e.preventDefault();
                    orderForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>

</html>
