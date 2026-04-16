@php

    $company = getCurrentCompany();
    $template = $company->product_card_template ?? 1;
@endphp
@if ($template == 1)
    @include('components.template1.product-1', ['product' => $product])
@elseif($template == 2)
    @include('components.template1.product-2', ['product' => $product])
@endif
@once
    @push('scripts')
        <script>
            const token = document.querySelector('meta[name="csrf-token"]').content;

            // product variation modal related scripts
            function updateModalTotal() {
                const selectedVariant = document.querySelector('input[name="selected_variant"]:checked');
                const qtyInput = document.getElementById('modal-qty');
                const totalDisplay = document.getElementById('modal-total-price-display');
                const unitPriceDisplay = document.getElementById('modal-unit-price');

                if (selectedVariant && qtyInput && totalDisplay) {
                    const unitPrice = parseFloat(selectedVariant.getAttribute('data-price'));
                    const qty = parseInt(qtyInput.value);
                    const currency = "{{ $setup->currency }}";

                    // calculate total
                    const total = unitPrice * qty;

                    // update display
                    unitPriceDisplay.innerText = currency + " " + unitPrice.toLocaleString();
                    totalDisplay.innerText = currency + " " + total.toLocaleString();
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

            // modal open function with loading state
            function openVariationModal(id) {
                const modal = document.getElementById('variation-modal');
                const contentArea = document.getElementById('modal-content-area');
                if (!modal) return;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                contentArea.innerHTML =
                    '<div class="py-10 text-center"><i class="fas fa-spinner fa-spin text-2xl text-[#FF6A00]"></i></div>';

                fetch("/product-variation/" + id)
                    .then(res => res.text())
                    .then(html => {
                        contentArea.innerHTML = html;

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
                const selectedVariant = document.querySelector('input[name="selected_variant"]:checked');
                const qtyInput = document.getElementById('modal-qty');
                if (!selectedVariant) {
                    toastr.warning("Please select an option.");
                    return;
                }
                fetch("{{ route('cart.add') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            variation_id: selectedVariant.value,
                            qty: qtyInput ? qtyInput.value : 1
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                            closeModal();
                            toastr.success(data.message);
                        }
                    });
            }

            function addSingleToCart(id) {
                fetch("{{ route('cart.add') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({
                            id: id,
                            qty: 1
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            document.querySelectorAll('.cart-count-nav').forEach(el => el.innerText = data.cart_count);
                            toastr.success(data.message);
                        }
                    });
            }

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
                            // heart icon update in all product cards
                            const icons = document.querySelectorAll(`[id="wish-icon-${productId}"]`);
                            icons.forEach(icon => {
                                const btn = icon.closest('button');
                                if (data.status === 'added') {
                                    icon.setAttribute('fill', '#ef4444');
                                    icon.setAttribute('stroke', '#ef4444');
                                    if (btn) btn.classList.add('opacity-100');
                                } else {
                                    icon.setAttribute('fill', 'none');
                                    icon.setAttribute('stroke', 'currentColor');
                                    if (btn) btn.classList.remove('opacity-100');
                                }
                            });

                            // remove from wishlist page with animation if removed
                            if (data.status === 'removed') {
                                // card find and remove animation
                                const wishlistSection = document.getElementById('wishlist-section');
                                if (wishlistSection) {
                                    const iconInWishlist = wishlistSection.querySelector(`[id="wish-icon-${productId}"]`);
                                    if (iconInWishlist) {
                                        const productCard = iconInWishlist.closest(
                                        '.group.relative'); // main card element
                                        if (productCard) {
                                            // remove animation
                                            productCard.style.transition = 'all 0.5s ease';
                                            productCard.style.opacity = '0';
                                            productCard.style.transform = 'scale(0.9)';
                                            setTimeout(() => {
                                                productCard.remove();

                                                // message remove
                                                const remainingCards = wishlistSection.querySelectorAll(
                                                    '.group.relative');
                                                if (remainingCards.length === 0) {
                                                    location
                                                .reload();
                                                }
                                            }, 500);
                                        }
                                    }
                                }
                                toastr.info(data.message);
                            } else {
                                toastr.success(data.message);
                            }

                            const wishCountElements = document.querySelectorAll(
                            '.wishlist-count-val');
                            wishCountElements.forEach(el => el.innerText = data.wish_count);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        </script>
    @endpush
@endonce
