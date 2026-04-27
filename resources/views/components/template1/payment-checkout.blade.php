{{-- resources/views/components/payment-checkout.blade.php --}}
@props(['methods', 'currency' => '৳'])

@php
    $bkash = $methods->first(fn($m) => str_contains(strtolower($m->name), 'bkash'));
    $nagad = $methods->first(fn($m) => str_contains(strtolower($m->name), 'nagad'));
    $rocket = $methods->first(fn($m) => str_contains(strtolower($m->name), 'rocket'));
    $bank = $methods->first(fn($m) => str_contains(strtolower($m->name), 'bank'));
@endphp

<div id="payment-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white w-full max-w-xl rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">

        {{-- Scrollable Body --}}
        <div class="overflow-y-auto flex-1 p-5">
            {{-- Amount Display --}}
            <div class="text-center mb-6">
                <p class="text-xs text-gray-500 uppercase tracking-wider">Amount to Pay</p>
                <p class="text-3xl font-black text-[#FF6A00]">{{ $currency }} <span id="modal-amount">0</span></p>
            </div>

            {{-- Method Grid --}}
            <div id="payment-method-list">
                <p class="text-xs font-semibold text-gray-400 mb-3 uppercase tracking-wide">Choose Method</p>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                    @foreach ($methods as $method)
                        @php $slug = strtolower(trim($method->name)); @endphp
                        <button type="button"
                            onclick="selectPaymentMethod('{{ $slug }}', '{{ $method->name }}')"
                            id="method-btn-{{ $slug }}"
                            class="method-icon-btn flex flex-col items-center justify-center gap-2 p-3 border-2 border-gray-100 rounded-xl hover:border-[#FF6A00] transition-all bg-white group">

                            <div
                                class="w-12 h-12 flex items-center justify-center grayscale group-hover:grayscale-0 transition-all">
                                @if ($method->icon)
                                    <img src="{{ $method->icon_url }}" class="h-8 object-contain"
                                        onerror="this.onerror=null; this.src='{{ asset('images/payment/default.png') }}';">
                                @else
                                    <i class="fas fa-university text-2xl text-gray-400 group-hover:text-[#FF6A00]"></i>
                                @endif
                            </div>
                            <span
                                class="text-[10px] font-bold text-gray-600 uppercase tracking-tighter">{{ $method->name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div id="payment-forms-area">
                <x-template1.payment-forms.card />
                <x-template1.payment-forms.cod />
                <x-template1.payment-forms.bkash :method="$bkash" />
                <x-template1.payment-forms.nagad :method="$nagad" />
                <x-template1.payment-forms.rocket :method="$rocket" />
                <x-template1.payment-forms.bank :method="$bank" />
            </div>
        </div>
    </div>
</div>

<script>
    function handlePaymentSelection(slug, name) {
        // ১. সব কন্টেইনার হাইড করুন এবং ফর্মগুলো আবার রিপোজিটরিতে পাঠিয়ে দিন
        document.querySelectorAll('[id^="checkout-anchor-"]').forEach(anchor => {
            const form = anchor.querySelector('.payment-form');
            if (form) {
                document.getElementById('payment-forms-area').appendChild(form);
                form.classList.add('hidden');
            }
            anchor.classList.add('hidden');
        });

        // ২. সিলেক্ট করা স্লাগ অনুযায়ী ফর্ম আইডি বের করুন
        let s = slug.toLowerCase();
        let formId = '';
        if (s.includes('bkash')) formId = 'form-bkash';
        else if (s.includes('nagad')) formId = 'form-nagad';
        else if (s.includes('rocket')) formId = 'form-rocket';
        else if (s.includes('card') || s.includes('visa')) formId = 'form-card';
        else if (s.includes('bank')) formId = 'form-bank';
        else if (s.includes('cash') || s.includes('cod')) formId = 'form-cod';

        const form = document.getElementById(formId);
        const anchor = document.getElementById('checkout-anchor-' + slug);

        if (form && anchor) {
            // ৩. ফর্মটি এনভকর পয়েন্টে মুভ করুন
            anchor.querySelector('.form-injection-point').appendChild(form);
            anchor.classList.remove('hidden');
            form.classList.remove('hidden');

            // ৪. ইনপুট ফিল্ডে ভ্যালু সেট করুন
            const orderInput = form.querySelector('[name="order_id"]');
            const amountInput = form.querySelector('[name="amount"]');
            if (orderInput) orderInput.value = _activeDraftOrderId;
            if (amountInput) amountInput.value = _checkoutTotal;
        }
    }
</script>
