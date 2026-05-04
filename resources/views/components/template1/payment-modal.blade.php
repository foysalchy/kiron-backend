{{-- resources/views/components/template1/payment-modal.blade.php --}}
@props(['methods', 'currency' => '৳'])

<div id="payment-modal" class="fixed inset-0 z-[2000] hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white w-full max-w-xl rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
            <div class="flex items-center gap-3">
                <button id="back-to-methods" onclick="goBackToMethods()" class="hidden text-gray-500 hover:text-black">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h3 class="text-base font-bold text-gray-800">Select Payment Method</h3>
            </div>
            <button onclick="closePaymentModal()"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

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

            {{-- Forms Area --}}
            {{-- Forms Area --}}
            <div id="payment-forms-area">
                @foreach ($methods as $method)
                    @php
                        $slug = strtolower(trim($method->name));
                        $isBank = str_contains($slug, 'bank');
                        $isCOD = str_contains($slug, 'cash') || str_contains($slug, 'delivery') || $slug == 'cod';
                        $isMFS =
                            str_contains($slug, 'bkash') ||
                            str_contains($slug, 'nagad') ||
                            str_contains($slug, 'rocket') ||
                            str_contains($slug, 'upay');
                    @endphp

                    @if ($isBank)
                        <x-template1.payment-forms.bank :method="$method" mode="modal" :slug="$slug" />
                    @elseif($isMFS)
                        <x-template1.payment-forms.mfs :method="$method" mode="modal" :slug="$slug" />
                    @elseif($isCOD)
                        <div class="payment-form hidden" id="form-{{ $slug }}">
                            <form onsubmit="handlePaymentSubmit(event, 'cod')" class="space-y-4">
                                @csrf
                                <input type="hidden" name="order_id">
                                <input type="hidden" name="payment_method" value="{{ $method->name }}">
                                <input type="hidden" name="amount">

                                <div class="p-6 bg-green-50 border border-green-200 rounded-xl text-center">
                                    <i class="fas fa-truck text-3xl text-green-600 mb-3"></i>
                                    <p class="text-green-800 font-bold">Cash on Delivery</p>
                                    <p class="text-sm text-green-600 mt-1">Pay when you receive the product.</p>
                                </div>

                                <button type="submit"
                                    class="w-full py-3 bg-[#1D2128] text-white rounded-xl font-bold hover:bg-black transition-all">
                                    Confirm Order (COD)
                                </button>
                            </form>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function previewImages(input, slug) {
            const grid = document.getElementById(slug + '-preview');
            const hidden = document.getElementById(slug + '-hidden-files');
            grid.innerHTML = '';
            hidden.innerHTML = '';

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    grid.innerHTML = `<div class="relative w-16 h-16 border rounded overflow-hidden">
                <img src="${e.target.result}" class="w-full h-full object-cover">
            </div>`;
                };
                reader.readAsDataURL(input.files[0]);

                const dt = new DataTransfer();
                dt.items.add(input.files[0]);
                const newInp = document.createElement('input');
                newInp.type = 'file';
                newInp.name = 'screenshots[]';
                newInp.files = dt.files;
                newInp.classList.add('hidden');
                hidden.appendChild(newInp);
            }
        }
        let _activeOrderId = null;
        let _payableAmount = 0;

        function openPaymentModal(orderId, amount) {
            _activeOrderId = orderId;
            _payableAmount = amount;
            document.getElementById('modal-amount').innerText = parseFloat(amount).toLocaleString();

            resetPaymentForms();
            document.getElementById('payment-modal').classList.replace('hidden', 'flex');
            document.body.style.overflow = 'hidden';
        }

        function resetPaymentForms() {
            document.querySelectorAll('.payment-form').forEach(f => {
                f.classList.add('hidden');
                f.querySelectorAll('input, select, textarea').forEach(i => i.disabled = true);
            });
            document.querySelectorAll('.method-icon-btn').forEach(b => {
                b.classList.remove('border-[#FF6A00]', 'bg-orange-50');
                b.classList.add('border-gray-100');
            });
            document.getElementById('payment-method-list').classList.remove('hidden');
            document.getElementById('back-to-methods').classList.add('hidden');
        }

        function selectPaymentMethod(slug, name) {
            resetPaymentForms();

            const btn = document.getElementById('method-btn-' + slug);
            if (btn) btn.classList.add('border-[#FF6A00]', 'bg-orange-50');

            document.getElementById('payment-method-list').classList.add('hidden');
            document.getElementById('back-to-methods').classList.remove('hidden');

            const form = document.getElementById('form-' + slug);
            if (form) {
                form.classList.remove('hidden');

                form.querySelectorAll('input, select, textarea').forEach(i => {
                    i.disabled = false;
                    if (i.name === 'order_id') i.value = _activeOrderId;

                    if (i.name === 'amount') i.value = _payableAmount;

                    if (i.name === 'payment_method') i.value = name;
                });
            }
        }

        function goBackToMethods() {
            resetPaymentForms();
        }

        function closePaymentModal() {
            document.getElementById('payment-modal').classList.replace('flex', 'hidden');
            document.body.style.overflow = '';
            goBackToMethods();
        }

        function handlePaymentSubmit(event, type) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);
            const btn = form.querySelector('button[type="submit"]');
            const originalBtnText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';

            fetch("{{ route('order.payment.submit') }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async response => {
                    const data = await response.json();

                    if (response.ok && data.success) {
                        toastr.success(data.message);
                        closePaymentModal();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        if (data.errors) {
                            Object.values(data.errors).forEach(err => toastr.error(err[0]));
                        } else {
                            toastr.error(data.message || 'Something went wrong!');
                        }
                        btn.disabled = false;
                        btn.innerHTML = originalBtnText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Connection failed. Please try again.');
                    btn.disabled = false;
                    btn.innerHTML = originalBtnText;
                });
        }
    </script>
@endpush
