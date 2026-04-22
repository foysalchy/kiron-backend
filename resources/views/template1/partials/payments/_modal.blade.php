{{-- resources/views/template1/partials/payment/_modal.blade.php --}}
{{-- Include at bottom of dashboard: @include('template1.partials.payment._modal') --}}

<div id="payment-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
            <h3 class="text-base font-bold text-gray-800">Select Payment Method</h3>
            <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>

        {{-- Scrollable Body --}}
        <div class="overflow-y-auto flex-1 p-5">

            {{-- Amount --}}
            <div class="text-center mb-4">
                <p class="text-xs text-gray-500">Amount to Pay</p>
                <p class="text-2xl font-black text-[#FF6A00]">{{ $setup->currency }} <span id="modal-amount">0</span></p>
            </div>

            {{-- Method Grid --}}
            <div id="payment-method-list">
                <p class="text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wide">Choose Method</p>
                <div class="grid grid-cols-3 gap-3">
                    @foreach ($paymentMethods as $method)
                        @php
                            $slug = strtolower(trim($method->name));
                        @endphp
                        <button type="button"
                            onclick="selectMethod('{{ $slug }}', '{{ $method->name }}')"
                            id="method-btn-{{ $slug }}"
                            class="method-icon-btn flex flex-col items-center justify-center gap-2 p-3 border-2 border-gray-200 rounded-lg hover:border-[#FF6A00] transition-all bg-white">

                            @if(str_contains($slug, 'bkash'))
                                <img src="{{ asset('images/payment/bkash.png') }}"
                                    onerror="this.outerHTML='<div class=\'w-10 h-10 rounded-full flex items-center justify-center text-white text-xs font-bold\' style=\'background:#e2136e\'>B</div>'"
                                    class="h-8 w-auto object-contain">
                            @elseif(str_contains($slug, 'nagad'))
                                <img src="{{ asset('images/payment/nagad.png') }}"
                                    onerror="this.outerHTML='<div class=\'w-10 h-10 rounded-full flex items-center justify-center text-white text-xs font-bold\' style=\'background:#f5821f\'>N</div>'"
                                    class="h-8 w-auto object-contain">
                            @elseif(str_contains($slug, 'rocket'))
                                <img src="{{ asset('images/payment/rocket.png') }}"
                                    onerror="this.outerHTML='<div class=\'w-10 h-10 rounded-full flex items-center justify-center text-white text-xs font-bold\' style=\'background:#8B1FA8\'>R</div>'"
                                    class="h-8 w-auto object-contain">
                            @elseif(str_contains($slug, 'bank'))
                                <div class="w-10 h-10 rounded-full bg-yellow-400 flex items-center justify-center">
                                    <i class="fas fa-university text-white"></i>
                                </div>
                            @elseif(str_contains($slug, 'cash') || str_contains($slug, 'cod'))
                                <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center">
                                    <i class="fas fa-truck text-white"></i>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <i class="{{ $method->icon ?? 'fas fa-wallet' }} text-gray-600"></i>
                                </div>
                            @endif

                            <span class="text-[11px] font-semibold text-gray-700 text-center leading-tight">{{ $method->name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Forms Area --}}
            <div id="payment-forms-area">
                @include('template1.partials.payments.bkash')
                @include('template1.partials.payments.nagad')
                @include('template1.partials.payments.rocket')
                @include('template1.partials.payments.bank')
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    let _orderId = null;
    let _amount  = 0;

    function openPaymentModal(orderId, amount) {
        _orderId = orderId;
        _amount  = amount;
        document.getElementById('modal-amount').innerText = parseFloat(amount).toLocaleString();
        resetForms();
        document.getElementById('payment-modal').classList.remove('hidden');
        document.getElementById('payment-modal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
        document.getElementById('payment-modal').classList.remove('flex');
        document.body.style.overflow = '';
    }

    function resetForms() {
        document.querySelectorAll('.payment-form').forEach(f => f.classList.add('hidden'));
        document.querySelectorAll('.method-icon-btn').forEach(b => {
            b.classList.remove('border-[#FF6A00]', 'bg-orange-50');
            b.classList.add('border-gray-200');
        });
    }

    function selectMethod(slug, name) {
        resetForms();

        // Highlight active button
        const btn = document.getElementById('method-btn-' + slug);
        if (btn) {
            btn.classList.remove('border-gray-200');
            btn.classList.add('border-[#FF6A00]', 'bg-orange-50');
        }

        // Cash on delivery — no form
        if (slug.includes('cash') || slug.includes('cod')) {
            if (confirm('Confirm Cash on Delivery?')) {
                window.location.href = '/order/pay/' + _orderId + '/cod';
            }
            return;
        }

        // Map slug → form id
        let formId;
        if (slug.includes('bkash'))       formId = 'form-bkash';
        else if (slug.includes('nagad'))  formId = 'form-nagad';
        else if (slug.includes('rocket')) formId = 'form-rocket';
        else if (slug.includes('bank'))   formId = 'form-bank';
        else return;

        const form = document.getElementById(formId);
        if (!form) return;

        // Inject order id
        const orderInput = form.querySelector('[name="order_id"]');
        if (orderInput) orderInput.value = _orderId;

        // Auto-fill amount
        const amountInput = form.querySelector('[name="amount"]');
        if (amountInput) amountInput.value = _amount;

        form.classList.remove('hidden');
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Screenshot preview helper (shared across all forms)
    function previewScreenshot(input, previewId, placeholderId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                const placeholder = document.getElementById(placeholderId);
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Close on backdrop click
    document.getElementById('payment-modal').addEventListener('click', function(e) {
        if (e.target === this) closePaymentModal();
    });
</script>
@endpush
