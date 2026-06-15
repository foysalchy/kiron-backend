@php

    $company = getCurrentCompany();
    $template = $company->product_card_template ?? 2;
@endphp
@if ($template == 1)
    @include('components.template1.product-1', ['product' => $product, 'company' => $company])
@elseif($template == 2)
    @include('components.template1.product-2', ['product' => $product, 'company' => $company])
@endif

@once
    @push('scripts')
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
