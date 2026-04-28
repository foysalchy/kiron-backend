{{-- resources/views/components/template1/payment-checkout.blade.php --}}
@props(['methods', 'currency' => '৳'])

<div id="payment-forms-area">
    @foreach ($methods as $method)
        @php
            $slug = strtolower(trim($method->name));
            $isBank = str_contains($slug, 'bank');
            $isCOD = str_contains($slug, 'cash') || str_contains($slug, 'delivery');
        @endphp

        @if (!$isCOD)
            @if ($isBank)
                <x-template1.payment-forms.bank :method="$method" mode="checkout" :slug="$slug" />
            @else
                <x-template1.payment-forms.mfs :method="$method" mode="checkout" :slug="$slug" />
            @endif
        @endif
    @endforeach
</div>

<script>
    function handlePaymentSelection(slug, name) {
        const repo = document.getElementById('payment-forms-area');

        document.querySelectorAll('.payment-form').forEach(form => {
            form.classList.add('hidden');
            form.querySelectorAll('input, select, textarea').forEach(input => {
                input.disabled = true;
            });
            repo.appendChild(form);
        });

        document.querySelectorAll('[id^="checkout-anchor-"]').forEach(anchor => {
            anchor.classList.add('hidden');
            const injectionPoint = anchor.querySelector('.form-injection-point');
            if (injectionPoint) injectionPoint.innerHTML = '';
        });

        const targetAnchor = document.getElementById('checkout-anchor-' + slug);
        const targetForm = document.getElementById('form-' + slug);

        if (targetForm && targetAnchor) {
            targetAnchor.querySelector('.form-injection-point').appendChild(targetForm);
            targetAnchor.classList.remove('hidden');
            targetForm.classList.remove('hidden');

            targetForm.querySelectorAll('input, select, textarea').forEach(input => {
                input.disabled = false;
            });

            const amountInput = targetForm.querySelector('[name="amount"]');
            if (amountInput && typeof _checkoutTotal !== 'undefined') {
                amountInput.value = _checkoutTotal;
            }
        }

    }
</script>
