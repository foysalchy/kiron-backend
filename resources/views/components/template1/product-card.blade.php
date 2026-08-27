@php

    $company = getCurrentCompany();
    $card = $company->product_card_template ?? 2;
@endphp
@if ($card == 1)
    @include('components.template1.product-1', ['product' => $product, 'company' => $company])
@elseif($card == 2)
    @include('components.template1.product-2', ['product' => $product, 'company' => $company])
@elseif($card == 3)
    @include('components.template1.product-3', ['product' => $product, 'company' => $company])
@elseif($card == 4)
    @include('components.template1.product-4', ['product' => $product, 'company' => $company])

@elseif($card == 5)
    @include('components.template1.product-5', ['product' => $product, 'company' => $company])
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
                            return;
                        }

                        const allIcons = document.querySelectorAll(
                            `[id="wish-icon-${productId}"], .wish-icon-${productId}, button[onclick="toggleWishlist(${productId})"] i`
                            );

                        allIcons.forEach(icon => {
                            if (data.status === 'added') {
                                // SVG সাপোর্ট (Template 1)
                                icon.setAttribute('fill', '#ef4444');
                                icon.setAttribute('stroke', '#ef4444');
                                icon.classList.replace('fa-regular', 'fa-solid');
                                icon.classList.add('text-white');

                                const btn = icon.closest('button');
                                if (btn) btn.classList.replace('bg-[var(--primary-color)]', 'bg-red-500');
                            } else {
                                icon.setAttribute('fill', 'none');
                                icon.setAttribute('stroke', 'currentColor');
                                icon.classList.replace('fa-solid', 'fa-regular');

                                const btn = icon.closest('button');
                                if (btn) btn.classList.replace('bg-red-500', 'bg-[var(--primary-color)]');
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
    @endpush
@endonce
